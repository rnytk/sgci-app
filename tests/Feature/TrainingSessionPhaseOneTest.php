<?php

use App\Enums\EnrollmentStatus;
use App\Enums\TrainingModality;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

it('snapshots session rules and normalizes enrollment defaults', function () {
    $trainer = User::factory()->create();
    $learner = User::factory()->create();
    $training = Training::create([
        'title' => 'Inducción institucional',
        'description' => 'Contenido inicial',
        'slug' => 'induccion-institucional',
        'user_id' => $trainer->id,
    ]);

    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $trainer->id,
        'modality' => TrainingModality::Virtual,
        'scheduled_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'requires_attendance' => true,
        'require_exam' => true,
        'passing_score' => 85,
        'max_attempts' => 1,
    ]);

    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $learner->id,
    ]);

    expect($session->fresh()->modality)->toBe(TrainingModality::Virtual)
        ->and((float) $session->fresh()->passing_score)->toBe(85.0)
        ->and($session->fresh()->max_attempts)->toBe(1)
        ->and($enrollment->fresh()->status)->toBe(EnrollmentStatus::Assigned)
        ->and($enrollment->fresh()->assigned_at)->not->toBeNull();
});

it('prevents duplicate assignments for the same session and collaborator', function () {
    $trainer = User::factory()->create();
    $learner = User::factory()->create();
    $training = Training::create([
        'title' => 'Curso de prueba',
        'description' => 'Contenido',
        'slug' => 'curso-de-prueba',
        'user_id' => $trainer->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $trainer->id,
        'scheduled_at' => now()->addDay(),
    ]);

    Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $learner->id,
    ]);

    expect(fn () => Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $learner->id,
    ]))->toThrow(QueryException::class);
});

it('records virtual attendance once and writes audit entries', function () {
    $trainer = User::factory()->create();
    $learner = User::factory()->create();
    $training = Training::create([
        'title' => 'Sesión virtual',
        'description' => 'Contenido',
        'slug' => 'sesion-virtual',
        'user_id' => $trainer->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $trainer->id,
        'modality' => TrainingModality::Virtual,
        'scheduled_at' => now()->subMinutes(5),
        'ends_at' => now()->addHour(),
        'requires_attendance' => true,
        'require_exam' => true,
    ]);
    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $learner->id,
    ]);

    $service = app(AttendanceService::class);
    $service->recordVirtualAccess($session, $learner);
    $service->recordVirtualAccess($session, $learner);

    expect($enrollment->fresh()->attendance_source)->toBe('virtual')
        ->and($enrollment->fresh()->status)->toBe(EnrollmentStatus::InProgress)
        ->and(AuditLog::where('event', 'attendance.recorded')->count())->toBe(1);
});

it('validates expiring QR attendance and rejects non-enrolled users', function () {
    $trainer = User::factory()->create();
    $learner = User::factory()->create();
    $outsider = User::factory()->create();
    $training = Training::create([
        'title' => 'Sesión presencial',
        'description' => 'Contenido',
        'slug' => 'sesion-presencial',
        'user_id' => $trainer->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $trainer->id,
        'modality' => TrainingModality::InPerson,
        'scheduled_at' => now()->subMinutes(5),
        'ends_at' => now()->addHour(),
        'requires_attendance' => true,
        'require_exam' => false,
    ]);
    Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $learner->id,
    ]);

    $token = app(AttendanceService::class)->issueQrToken($session, $trainer);

    expect(fn () => app(AttendanceService::class)->recordQrCheckIn($token, $outsider))
        ->toThrow(ValidationException::class);

    $enrollment = app(AttendanceService::class)->recordQrCheckIn($token, $learner);

    expect($enrollment->status)->toBe(EnrollmentStatus::Completed)
        ->and($enrollment->attendance_source)->toBe('qr');
});
