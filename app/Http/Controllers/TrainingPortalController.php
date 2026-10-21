<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingPortalController extends Controller
{
    public function index(): View
    {
        $enrollments = auth()->user()
            ->enrollments()
            ->with([
                'trainingSession.training.mediaResources',
                'trainingSession.trainer',
                'examAttempts',
            ])
            ->latest('assigned_at')
            ->get();

        return view('training-portal.index', compact('enrollments'));
    }

    public function show(Enrollment $enrollment): View
    {
        $enrollment = $this->ownEnrollment($enrollment);
        $enrollment->load([
            'trainingSession.training.mediaResources',
            'trainingSession.trainer',
            'examAttempts' => fn ($query) => $query->latest('attempt_number'),
        ]);

        return view('training-portal.show', compact('enrollment'));
    }

    public function examLobby(Enrollment $enrollment): View
    {
        $enrollment = $this->ownEnrollment($enrollment);
        $enrollment->load('trainingSession.training.exam');

        $session = $enrollment->trainingSession;
        $exam = $session->training?->exam;
        $attemptsUsed = $enrollment->examAttempts()->count();
        $activeAttempt = $enrollment->examAttempts()->where('status', 'in_progress')->latest('id')->first();

        return view('training-portal.exam-lobby', compact('enrollment', 'session', 'exam', 'attemptsUsed', 'activeAttempt'));
    }

    public function startExam(Enrollment $enrollment, ExamAttemptService $attempts): RedirectResponse
    {
        $enrollment = $this->ownEnrollment($enrollment);
        $attempt = $attempts->start($enrollment);

        return to_route('training-portal.exam.attempt', [
            'enrollment' => $enrollment,
            'attempt' => $attempt,
        ]);
    }

    public function attempt(Enrollment $enrollment, ExamAttempt $attempt): View
    {
        $enrollment = $this->ownEnrollment($enrollment);
        abort_unless($attempt->enrollment_id === $enrollment->getKey(), 404);

        $attempt->load('exam.questions.questionOptions', 'answers');

        return view('training-portal.exam-attempt', compact('enrollment', 'attempt'));
    }

    public function saveAnswers(Request $request, Enrollment $enrollment, ExamAttempt $attempt, ExamAttemptService $attempts): JsonResponse
    {
        $enrollment = $this->ownEnrollment($enrollment);
        abort_unless($attempt->enrollment_id === $enrollment->getKey(), 404);

        $data = $request->validate(['answers' => ['nullable', 'array']]);
        $attempts->saveAnswers($attempt, $data['answers'] ?? []);

        return response()->json(['saved' => true]);
    }

    public function submitExam(Request $request, Enrollment $enrollment, ExamAttempt $attempt, ExamAttemptService $attempts): RedirectResponse
    {
        $enrollment = $this->ownEnrollment($enrollment);
        abort_unless($attempt->enrollment_id === $enrollment->getKey(), 404);

        $data = $request->validate(['answers' => ['nullable', 'array']]);
        $attempt = $attempts->submit($attempt, $data['answers'] ?? []);

        $message = $attempt->status === 'pending_review'
            ? 'Tu examen fue enviado y queda pendiente de revisión en las preguntas abiertas.'
            : ($attempt->passed ? 'Examen aprobado.' : 'Examen enviado. Revisa el resultado y los intentos disponibles.');

        return to_route('training-portal.show', $enrollment)->with('status', $message);
    }

    private function ownEnrollment(Enrollment $enrollment): Enrollment
    {
        abort_unless($enrollment->user_id === auth()->id(), 403);

        return $enrollment;
    }
}
