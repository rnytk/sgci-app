<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamAttemptService
{
    public function start(Enrollment $enrollment): ExamAttempt
    {
        $enrollment->loadMissing('trainingSession.training.exam.questions.questionOptions');
        $session = $enrollment->trainingSession;
        $exam = $session?->training?->exam;

        if (! $session || ! $exam || ! $session->require_exam) {
            throw ValidationException::withMessages([
                'exam' => 'Esta sesión no tiene un examen habilitado.',
            ]);
        }

        if (in_array($enrollment->status, [EnrollmentStatus::Cancelled, EnrollmentStatus::Absent], true)) {
            throw ValidationException::withMessages([
                'enrollment' => 'Esta inscripción no está disponible para presentar el examen.',
            ]);
        }

        if (! $session->exam_enabled) {
            throw ValidationException::withMessages([
                'exam' => 'El examen todavía no está habilitado por el instructor.',
            ]);
        }

        if ($session->requires_attendance && $enrollment->attended_at === null) {
            throw ValidationException::withMessages([
                'attendance' => 'Debes registrar tu asistencia antes de iniciar el examen.',
            ]);
        }

        $activeAttempt = $enrollment->examAttempts()
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        if ($activeAttempt && $activeAttempt->submitted_at === null && ! $activeAttempt->hasExpired()) {
            return $activeAttempt->load('exam.questions.questionOptions', 'answers');
        }

        if ($activeAttempt && $activeAttempt->isInProgress() && $activeAttempt->hasExpired()) {
            $this->submit($activeAttempt, []);
        }

        $attemptNumber = ((int) $enrollment->examAttempts()->max('attempt_number')) + 1;

        if ($attemptNumber > $session->max_attempts) {
            throw ValidationException::withMessages([
                'exam' => 'Ya utilizaste el máximo de intentos permitido para esta sesión.',
            ]);
        }

        $startedAt = now();
        $attempt = DB::transaction(function () use ($enrollment, $exam, $attemptNumber, $startedAt): ExamAttempt {
            $attempt = $enrollment->examAttempts()->create([
                'exam_id' => $exam->getKey(),
                'attempt_number' => $attemptNumber,
                'started_at' => $startedAt,
                'expires_at' => $startedAt->copy()->addMinutes(max(1, (int) $exam->time_limit)),
                'status' => 'in_progress',
            ]);

            $enrollment->forceFill([
                'status' => EnrollmentStatus::InProgress,
                'started_at' => $enrollment->started_at ?? $startedAt,
            ])->save();

            return $attempt;
        });

        app(AuditLogger::class)->record('exam_attempt.started', $attempt, [
            'enrollment_id' => $enrollment->getKey(),
            'attempt_number' => $attemptNumber,
        ]);

        return $attempt->load('exam.questions.questionOptions', 'answers');
    }

    public function saveAnswers(ExamAttempt $attempt, array $answers): ExamAttempt
    {
        if (! $attempt->isInProgress()) {
            throw ValidationException::withMessages([
                'attempt' => 'Este intento ya fue enviado.',
            ]);
        }

        if ($attempt->hasExpired()) {
            throw ValidationException::withMessages([
                'attempt' => 'El tiempo del examen terminó.',
            ]);
        }

        $this->persistAnswers($attempt, $answers);

        return $attempt->refresh()->load('answers');
    }

    public function submit(ExamAttempt $attempt, array $answers = []): ExamAttempt
    {
        if (! $attempt->isInProgress()) {
            return $attempt->fresh()->load('exam.questions.questionOptions', 'answers', 'enrollment.trainingSession');
        }

        return DB::transaction(function () use ($attempt, $answers): ExamAttempt {
            $attempt = ExamAttempt::query()
                ->with('exam.questions.questionOptions', 'answers', 'enrollment.trainingSession')
                ->lockForUpdate()
                ->findOrFail($attempt->getKey());

            if (! $attempt->isInProgress()) {
                return $attempt;
            }

            if ($answers !== []) {
                $this->persistAnswers($attempt, $answers);
                $attempt->load('exam.questions.questionOptions', 'answers', 'enrollment.trainingSession');
            }

            $answerMap = $attempt->answers->keyBy('question_id');
            $hasPendingReview = false;
            $awardedPoints = 0.0;
            $totalPoints = 0.0;

            foreach ($attempt->exam->questions as $question) {
                $totalPoints += (float) $question->points;
                $answer = $answerMap->get($question->getKey()) ?? ExamAttemptAnswer::create([
                    'exam_attempt_id' => $attempt->getKey(),
                    'question_id' => $question->getKey(),
                ]);

                if ($question->type === 'open') {
                    $hasPendingReview = true;
                    $answer->forceFill([
                        'auto_graded' => false,
                        'is_correct' => null,
                        'points_awarded' => null,
                    ])->save();

                    continue;
                }

                $selected = collect($answer->answer ?? [])->map(fn (mixed $value): int => (int) $value)->sort()->values()->all();
                $correct = $question->questionOptions
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->map(fn (mixed $value): int => (int) $value)
                    ->sort()
                    ->values()
                    ->all();
                $isCorrect = $selected === $correct;
                $points = $isCorrect ? (float) $question->points : 0.0;
                $awardedPoints += $points;

                $answer->forceFill([
                    'auto_graded' => true,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $points,
                ])->save();
            }

            $score = $hasPendingReview || $totalPoints === 0.0
                ? null
                : round(($awardedPoints / $totalPoints) * 100, 2);
            $passed = $score === null ? null : $score >= (float) $attempt->enrollment->trainingSession->passing_score;

            $attempt->forceFill([
                'submitted_at' => now(),
                'score' => $score,
                'passed' => $passed,
                'status' => $hasPendingReview ? 'pending_review' : 'submitted',
            ])->save();

            $this->syncEnrollmentStatus($attempt->fresh()->load('enrollment.trainingSession'));

            app(AuditLogger::class)->record('exam_attempt.submitted', $attempt, [
                'score' => $score,
                'passed' => $passed,
                'pending_review' => $hasPendingReview,
            ]);

            return $attempt->fresh()->load('exam.questions.questionOptions', 'answers', 'enrollment.trainingSession');
        });
    }

    private function persistAnswers(ExamAttempt $attempt, array $answers): void
    {
        $attempt->loadMissing('exam.questions.questionOptions');

        foreach ($attempt->exam->questions as $question) {
            $rawAnswer = $answers[(string) $question->getKey()] ?? $answers[$question->getKey()] ?? null;

            if ($question->type === 'open') {
                $answerText = is_array($rawAnswer) ? implode(' ', $rawAnswer) : (filled($rawAnswer) ? (string) $rawAnswer : null);
                $payload = [
                    'answer' => null,
                    'answer_text' => $answerText,
                ];
            } else {
                $validOptionIds = $question->questionOptions->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
                $selected = collect(Arr::wrap($rawAnswer))
                    ->map(fn (mixed $id): int => (int) $id)
                    ->filter(fn (int $id): bool => in_array($id, $validOptionIds, true))
                    ->unique()
                    ->values()
                    ->all();
                $payload = [
                    'answer' => $selected,
                    'answer_text' => null,
                ];
            }

            ExamAttemptAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->getKey(),
                    'question_id' => $question->getKey(),
                ],
                $payload,
            );
        }
    }

    private function syncEnrollmentStatus(ExamAttempt $attempt): void
    {
        $enrollment = $attempt->enrollment->fresh()->load('trainingSession');

        if ($attempt->status === 'pending_review') {
            $enrollment->forceFill(['status' => EnrollmentStatus::InProgress])->save();

            return;
        }

        $hasPassedAttempt = $enrollment->examAttempts()->where('passed', true)->exists();
        $attendanceSatisfied = ! $enrollment->trainingSession->requires_attendance || $enrollment->attended_at !== null;

        if ($hasPassedAttempt && $attendanceSatisfied) {
            $enrollment->forceFill([
                'status' => EnrollmentStatus::Completed,
                'completed_at' => $enrollment->completed_at ?? now(),
            ])->save();

            return;
        }

        if ($hasPassedAttempt) {
            $enrollment->forceFill(['status' => EnrollmentStatus::InProgress])->save();

            return;
        }

        $enrollment->forceFill(['status' => EnrollmentStatus::Failed])->save();
    }
}
