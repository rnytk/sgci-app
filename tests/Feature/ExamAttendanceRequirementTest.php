<?php

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\ExamAttemptService;

function examAttendanceContext(string $attendanceStatus = Enrollment::ATTENDANCE_PENDING): array
{
    $owner = User::factory()->create();
    $student = User::factory()->create();
    $training = Training::create([
        'title' => 'Capacitación con asistencia requerida',
        'description' => 'Contenido de prueba',
        'slug' => 'capacitacion-con-asistencia-requerida',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->subHour(),
        'require_exam' => true,
        'exam_enabled' => true,
        'exam_expires_at' => now()->addDay(),
    ]);
    $exam = Exam::create([
        'training_id' => $training->id,
        'passing_score' => 70,
    ]);
    $question = Question::create([
        'exam_id' => $exam->id,
        'question_text' => 'Pregunta de validación',
        'type' => 'multiple',
        'points' => 100,
    ]);
    $option = $question->questionOptions()->create([
        'option_text' => 'Respuesta correcta',
        'is_correct' => true,
    ]);
    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $student->id,
        'attendance_status' => $attendanceStatus,
        'attended_at' => $attendanceStatus === Enrollment::ATTENDANCE_PRESENT ? now() : null,
    ]);

    return compact('student', 'enrollment', 'exam', 'question', 'option');
}

test('a collaborator without present attendance cannot open the exam from its direct URL', function () {
    ['student' => $student, 'enrollment' => $enrollment] = examAttendanceContext();

    $this->actingAs($student)
        ->get(route('user.training.exam', $enrollment))
        ->assertForbidden();

    $enrollment->update([
        'attendance_status' => Enrollment::ATTENDANCE_ABSENT,
        'attendance_justification' => 'Ausencia justificada.',
    ]);

    $this->actingAs($student)
        ->get(route('user.training.exam', $enrollment))
        ->assertForbidden();
});

test('present attendance enables the exam and the service blocks a bypass without attendance', function () {
    ['student' => $student, 'enrollment' => $enrollment, 'exam' => $exam, 'question' => $question, 'option' => $option] = examAttendanceContext();
    $service = app(ExamAttemptService::class);

    expect(fn () => $service->submit($enrollment, $exam, [$question->id => $option->id]))
        ->toThrow(DomainException::class, 'asistencia registrada como presente');

    $enrollment->update([
        'attendance_status' => Enrollment::ATTENDANCE_PRESENT,
        'attended_at' => now(),
    ]);

    $this->actingAs($student)
        ->get(route('user.training.exam', $enrollment))
        ->assertOk();

    $attempt = $service->submit($enrollment, $exam, [$question->id => $option->id]);

    expect($attempt->status)->toBe('aprobado')
        ->and($attempt->passed)->toBeTrue();
});
