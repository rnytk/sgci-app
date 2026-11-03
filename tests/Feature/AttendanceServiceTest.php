<?php

use App\Livewire\UserTrainings;
use App\Models\Enrollment;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function attendanceContext(): array
{
    $trainer = User::factory()->create();
    $student = User::factory()->create();
    $administrator = User::factory()->create();
    $training = Training::create([
        'title' => 'Capacitación para control de asistencia',
        'description' => 'Contenido de prueba',
        'slug' => 'capacitacion-control-asistencia',
        'user_id' => $trainer->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $trainer->id,
        'scheduled_at' => now()->addDay(),
        'meeting_link' => 'https://meet.example.test/asistencia',
        'require_exam' => false,
        'exam_enabled' => false,
        'issue_certificate' => false,
        'exam_expires_at' => now()->addWeek(),
    ]);

    return [
        'administrator' => $administrator,
        'student' => $student,
        'enrollment' => Enrollment::create([
            'training_session_id' => $session->id,
            'user_id' => $student->id,
        ]),
    ];
}

test('a manual absence requires a justification and is attributed to its recorder', function () {
    ['administrator' => $administrator, 'enrollment' => $enrollment] = attendanceContext();
    $service = app(AttendanceService::class);

    expect(fn () => $service->recordManually(
        $enrollment,
        Enrollment::ATTENDANCE_ABSENT,
        null,
        $administrator,
    ))->toThrow(ValidationException::class);

    $attendance = $service->recordManually(
        $enrollment,
        Enrollment::ATTENDANCE_ABSENT,
        'Presentó una incapacidad médica.',
        $administrator,
    );

    expect($attendance->attendance_status)->toBe(Enrollment::ATTENDANCE_ABSENT)
        ->and($attendance->attendance_justification)->toBe('Presentó una incapacidad médica.')
        ->and($attendance->attendance_recorded_by)->toBe($administrator->id)
        ->and($attendance->attendance_recorded_at)->not->toBeNull()
        ->and($attendance->attended_at)->toBeNull();
});

test('a manual present mark records attendance and clears a previous justification', function () {
    ['administrator' => $administrator, 'enrollment' => $enrollment] = attendanceContext();
    $service = app(AttendanceService::class);

    $service->recordManually(
        $enrollment,
        Enrollment::ATTENDANCE_ABSENT,
        'Justificación inicial.',
        $administrator,
    );
    $attendance = $service->recordManually(
        $enrollment,
        Enrollment::ATTENDANCE_PRESENT,
        null,
        $administrator,
    );

    expect($attendance->attendance_status)->toBe(Enrollment::ATTENDANCE_PRESENT)
        ->and($attendance->attendance_justification)->toBeNull()
        ->and($attendance->attendance_recorded_by)->toBe($administrator->id)
        ->and($attendance->attended_at)->not->toBeNull()
        ->and($attendance->status)->toBe('en progreso');
});

test('joining through the meeting link records presence and does not overwrite a manual absence', function () {
    ['administrator' => $administrator, 'student' => $student, 'enrollment' => $enrollment] = attendanceContext();
    $service = app(AttendanceService::class);

    Livewire::actingAs($student)
        ->test(UserTrainings::class)
        ->call('markAttendanceAndJoin', $enrollment->id)
        ->assertDispatched('open-meeting', url: 'https://meet.example.test/asistencia');

    $attendance = $enrollment->fresh();

    expect($attendance->attendance_status)->toBe(Enrollment::ATTENDANCE_PRESENT)
        ->and($attendance->attended_at)->not->toBeNull()
        ->and($attendance->status)->toBe('en progreso');

    $service->recordManually(
        $attendance,
        Enrollment::ATTENDANCE_ABSENT,
        'Ausencia confirmada por administración.',
        $administrator,
    );

    $attendance = $service->recordFromMeetingLink($attendance);

    expect($attendance->attendance_status)->toBe(Enrollment::ATTENDANCE_ABSENT)
        ->and($attendance->attendance_justification)->toBe('Ausencia confirmada por administración.')
        ->and($attendance->attended_at)->toBeNull()
        ->and($attendance->attendance_recorded_by)->toBe($administrator->id);
});
