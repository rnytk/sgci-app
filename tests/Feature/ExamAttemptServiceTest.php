<?php

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\ExamAttemptService;
use Illuminate\Validation\ValidationException;

function examContext(): array
{
    $owner = User::factory()->create();
    $student = User::factory()->create();
    $training = Training::create([
        'title' => 'Seguridad institucional',
        'description' => 'Contenido de prueba',
        'slug' => 'seguridad-institucional',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->subHour(),
        'require_exam' => true,
        'exam_enabled' => true,
        'issue_certificate' => true,
        'exam_expires_at' => now()->addDay(),
    ]);

    return [
        'exam' => Exam::create([
            'training_id' => $training->id,
            'passing_score' => 70,
        ]),
        'enrollment' => Enrollment::create([
            'training_session_id' => $session->id,
            'user_id' => $student->id,
            'attendance_status' => Enrollment::ATTENDANCE_PRESENT,
            'attended_at' => now(),
        ]),
        'evaluator' => User::factory()->create(),
    ];
}

test('an exam with open questions remains pending even when automatic points pass', function () {
    ['exam' => $exam, 'enrollment' => $enrollment, 'evaluator' => $evaluator] = examContext();

    $multipleChoice = Question::create([
        'exam_id' => $exam->id,
        'question_text' => 'Pregunta automática',
        'type' => 'multiple',
        'points' => 90,
    ]);
    $correctOption = $multipleChoice->questionOptions()->create([
        'option_text' => 'Correcta',
        'is_correct' => true,
    ]);
    $openQuestion = Question::create([
        'exam_id' => $exam->id,
        'question_text' => 'Explica el procedimiento',
        'type' => 'open',
        'points' => 10,
    ]);

    $service = app(ExamAttemptService::class);
    $attempt = $service->submit($enrollment, $exam, [
        $multipleChoice->id => $correctOption->id,
        $openQuestion->id => 'Respuesta del colaborador',
    ]);

    expect($attempt->status)->toBe('pendiente_calificacion')
        ->and($attempt->score)->toBeNull()
        ->and($attempt->passed)->toBeNull()
        ->and($enrollment->fresh()->status)->toBe('pendiente_calificacion');

    expect(fn () => $service->finalize($attempt, $evaluator))
        ->toThrow(DomainException::class);

    $openAnswer = $attempt->answers()->where('question_id', $openQuestion->id)->firstOrFail();
    $service->gradeOpenAnswer($openAnswer, 0, $evaluator);
    $attempt = $service->finalize($attempt, $evaluator);

    expect($attempt->status)->toBe('aprobado')
        ->and($attempt->score)->toBe('90.00')
        ->and($attempt->passed)->toBeTrue()
        ->and($enrollment->fresh()->status)->toBe('completado');
});

test('a multiple choice-only exam is finalized automatically and accepts one attempt only', function () {
    ['exam' => $exam, 'enrollment' => $enrollment] = examContext();

    $question = Question::create([
        'exam_id' => $exam->id,
        'question_text' => 'Pregunta automática',
        'type' => 'multiple',
        'points' => 100,
    ]);
    $option = $question->questionOptions()->create([
        'option_text' => 'Correcta',
        'is_correct' => true,
    ]);

    $service = app(ExamAttemptService::class);
    $attempt = $service->submit($enrollment, $exam, [$question->id => $option->id]);

    expect($attempt->status)->toBe('aprobado')
        ->and($attempt->score)->toBe('100.00')
        ->and($attempt->passed)->toBeTrue();

    expect(fn () => $service->submit($enrollment, $exam, [$question->id => $option->id]))
        ->toThrow(ValidationException::class);
});
