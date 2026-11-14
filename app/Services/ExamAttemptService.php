<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamAttemptService
{
    /**
     * @param  array<int, int|string>  $answers
     */
    public function submit(Enrollment $enrollment, Exam $exam, array $answers): ExamAttempt
    {
        return DB::transaction(function () use ($enrollment, $exam, $answers): ExamAttempt {
            $enrollment = Enrollment::query()
                ->with('trainingSession')
                ->lockForUpdate()
                ->findOrFail($enrollment->id);

            if ($exam->training_id !== $enrollment->trainingSession->training_id) {
                throw new DomainException('La evaluación no pertenece a la capacitación de esta inscripción.');
            }

            if (! $enrollment->hasPresentAttendance()) {
                throw new DomainException('Debes tener asistencia registrada como presente para realizar la evaluación.');
            }

            if (
                ! $enrollment->trainingSession->require_exam
                || ! $enrollment->trainingSession->exam_enabled
                || now()->greaterThan($enrollment->trainingSession->exam_expires_at)
            ) {
                throw new DomainException('La evaluación no está disponible en este momento.');
            }

            if (ExamAttempt::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('status', '!=', ExamAttempt::STATUS_REPLACED)
                ->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'Ya existe un intento registrado para esta capacitación.',
                ]);
            }

            $questions = $exam->questions()->with('questionOptions')->get();

            if ($questions->isEmpty()) {
                throw ValidationException::withMessages([
                    'exam' => 'La evaluación no tiene preguntas configuradas.',
                ]);
            }

            $attempt = ExamAttempt::create([
                'enrollment_id' => $enrollment->id,
                'status' => ExamAttempt::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            $requiresManualReview = false;

            foreach ($questions as $question) {
                $answer = $answers[$question->id] ?? null;

                if ($question->type === 'open') {
                    if (! is_string($answer) || blank(trim($answer))) {
                        throw ValidationException::withMessages([
                            "answers.{$question->id}" => 'Debes responder esta pregunta.',
                        ]);
                    }

                    $attempt->answers()->create([
                        'question_id' => $question->id,
                        'answer_text' => trim($answer),
                    ]);
                    $requiresManualReview = true;

                    continue;
                }

                $option = $question->questionOptions->firstWhere('id', (int) $answer);

                if (! $option) {
                    throw ValidationException::withMessages([
                        "answers.{$question->id}" => 'La opción seleccionada no pertenece a esta pregunta.',
                    ]);
                }

                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'selected_option_id' => $option->id,
                    'awarded_points' => $option->is_correct ? $question->points : 0,
                ]);
            }

            if ($requiresManualReview) {
                $attempt->update([
                    'status' => ExamAttempt::STATUS_PENDING_REVIEW,
                    'score' => null,
                    'passed' => null,
                ]);
                $enrollment->update(['status' => ExamAttempt::STATUS_PENDING_REVIEW]);

                return $attempt->fresh('answers.question');
            }

            return $this->finalizeAttempt($attempt, null);
        });
    }

    public function gradeOpenAnswer(ExamAttemptAnswer $answer, float $points, User $evaluator): ExamAttemptAnswer
    {
        return DB::transaction(function () use ($answer, $points, $evaluator): ExamAttemptAnswer {
            $answer = ExamAttemptAnswer::query()
                ->with(['attempt', 'question'])
                ->lockForUpdate()
                ->findOrFail($answer->id);

            if ($answer->attempt->status !== ExamAttempt::STATUS_PENDING_REVIEW) {
                throw new DomainException('El intento ya no está pendiente de calificación.');
            }

            if ($answer->question->type !== 'open') {
                throw new DomainException('Solo las preguntas abiertas requieren calificación manual.');
            }

            if ($points < 0 || $points > $answer->question->points) {
                throw ValidationException::withMessages([
                    "answers.{$answer->id}.awarded_points" => "Los puntos deben estar entre 0 y {$answer->question->points}.",
                ]);
            }

            $answer->update([
                'awarded_points' => round($points, 2),
                'graded_by' => $evaluator->id,
                'graded_at' => now(),
            ]);

            return $answer->fresh(['question', 'grader']);
        });
    }

    public function finalize(ExamAttempt $attempt, User $evaluator): ExamAttempt
    {
        return DB::transaction(function () use ($attempt, $evaluator): ExamAttempt {
            $attempt = ExamAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($attempt->status !== ExamAttempt::STATUS_PENDING_REVIEW) {
                throw new DomainException('Solo los intentos pendientes pueden finalizarse manualmente.');
            }

            return $this->finalizeAttempt($attempt, $evaluator);
        });
    }

    private function finalizeAttempt(ExamAttempt $attempt, ?User $evaluator): ExamAttempt
    {
        $attempt->loadMissing(['answers.question', 'enrollment.trainingSession.training.exams']);

        if ($attempt->answers->contains(fn (ExamAttemptAnswer $answer): bool => is_null($answer->awarded_points))) {
            throw new DomainException('Todas las preguntas abiertas deben calificarse antes de finalizar el intento.');
        }

        $exam = $attempt->enrollment->trainingSession->training->exams;

        if (! $exam) {
            throw new DomainException('No se encontró la evaluación asociada al intento.');
        }

        $score = round((float) $attempt->answers->sum('awarded_points'), 2);
        $passed = $score >= $exam->passing_score;

        $attempt->update([
            'status' => $passed ? ExamAttempt::STATUS_PASSED : ExamAttempt::STATUS_FAILED,
            'score' => $score,
            'passed' => $passed,
            'finalized_at' => now(),
            'finalized_by' => $evaluator?->id,
        ]);

        $attempt->enrollment->update([
            'status' => $passed ? 'completado' : ExamAttempt::STATUS_FAILED,
        ]);

        return $attempt->fresh(['answers.question', 'enrollment.trainingSession.training.exams']);
    }
}
