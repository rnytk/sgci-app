<?php

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\ExamAttemptService;
use Illuminate\Validation\ValidationException;

function examScenario(bool $withOpenQuestion = false, bool $requiresAttendance = false): array
{
    $trainer = User::factory()->create();
    $learner = User::factory()->create();
    $training = Training::create([
        'title' => 'Evaluación institucional',
        'description' => 'Contenido de prueba',
        'slug' => 'evaluacion-institucional-'.$trainer->id,
        'user_id' => $trainer->id,
    ]);
    $exam = Exam::create([
        'training_id' => $training->id,
        'passing_score' => 70,
        'time_limit' => 30,
    ]);
    $multiple = Question::create([
        'exam_id' => $exam->id,
        'question_text' => '¿Cuál es la respuesta correcta?',
        'type' => 'multiple',
        'points' => $withOpenQuestion ? 50 : 100,
    ]);
    $correctOption = $multiple->questionOptions()->create(['option_text' => 'Correcta', 'is_correct' => true]);
    $multiple->questionOptions()->create(['option_text' => 'Incorrecta', 'is_correct' => false]);

    $open = null;
    if ($withOpenQuestion) {
        $open = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Explica tu respuesta.',
            'type' => 'open',
            'points' => 50,
        ]);
    }

    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $trainer->id,
        'modality' => $requiresAttendance ? 'virtual' : 'self_paced',
        'scheduled_at' => now()->subHour(),
        'ends_at' => now()->addDay(),
        'require_exam' => true,
        'requires_attendance' => $requiresAttendance,
        'passing_score' => 70,
        'max_attempts' => 2,
        'exam_enabled' => true,
    ]);
    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $learner->id,
    ]);

    return compact('trainer', 'learner', 'exam', 'session', 'enrollment', 'multiple', 'correctOption', 'open');
}

it('grades multiple choice answers and completes the enrollment', function () {
    $scenario = examScenario();
    $service = app(ExamAttemptService::class);
    $attempt = $service->start($scenario['enrollment']);

    $attempt = $service->submit($attempt, [
        $scenario['multiple']->id => [$scenario['correctOption']->id],
    ]);

    expect($attempt->score)->toBe('100.00')
        ->and($attempt->passed)->toBeTrue()
        ->and($attempt->status)->toBe('submitted')
        ->and($scenario['enrollment']->fresh()->status)->toBe(EnrollmentStatus::Completed)
        ->and($scenario['enrollment']->fresh()->completed_at)->not->toBeNull();
});

it('keeps open questions pending manual review', function () {
    $scenario = examScenario(withOpenQuestion: true);
    $service = app(ExamAttemptService::class);
    $attempt = $service->start($scenario['enrollment']);

    $attempt = $service->submit($attempt, [
        $scenario['multiple']->id => [$scenario['correctOption']->id],
        $scenario['open']->id => 'Respuesta desarrollada',
    ]);

    expect($attempt->status)->toBe('pending_review')
        ->and($attempt->score)->toBeNull()
        ->and($attempt->passed)->toBeNull()
        ->and($scenario['enrollment']->fresh()->status)->toBe(EnrollmentStatus::InProgress);
});

it('enforces attendance before an exam when the session requires it', function () {
    $scenario = examScenario(requiresAttendance: true);

    expect(fn () => app(ExamAttemptService::class)->start($scenario['enrollment']))
        ->toThrow(ValidationException::class);
});

it('enforces the configured maximum number of attempts', function () {
    $scenario = examScenario();
    $scenario['session']->update(['max_attempts' => 1]);
    $service = app(ExamAttemptService::class);
    $attempt = $service->start($scenario['enrollment']);
    $service->submit($attempt, [$scenario['multiple']->id => []]);

    expect(fn () => $service->start($scenario['enrollment']))
        ->toThrow(ValidationException::class);
});

it('renders the collaborator portal and protects enrollment details', function () {
    $scenario = examScenario();
    $outsider = User::factory()->create();

    $this->actingAs($scenario['learner'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Mis capacitaciones')
        ->assertSee('Evaluación institucional');

    $this->actingAs($outsider)
        ->get(route('training-portal.show', $scenario['enrollment']))
        ->assertForbidden();
});

it('starts an exam through the collaborator portal', function () {
    $scenario = examScenario();

    $response = $this->actingAs($scenario['learner'])
        ->post(route('training-portal.exam.start', $scenario['enrollment']));

    $attempt = $scenario['enrollment']->examAttempts()->first();

    $response->assertRedirect(route('training-portal.exam.attempt', [$scenario['enrollment'], $attempt]));
    $this->actingAs($scenario['learner'])
        ->get(route('training-portal.exam.attempt', [$scenario['enrollment'], $attempt]))
        ->assertOk()
        ->assertSee('¿Cuál es la respuesta correcta?');
});
