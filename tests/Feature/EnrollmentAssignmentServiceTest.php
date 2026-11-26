<?php

use App\Models\Agency;
use App\Models\CollaboratorGroup;
use App\Models\Enrollment;
use App\Models\JobPosition;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\EnrollmentAssignmentService;
use Illuminate\Validation\ValidationException;

function enrollmentAssignmentContext(): array
{
    $owner = User::factory()->create();
    $training = Training::create([
        'title' => 'Capacitación para asignaciones',
        'description' => 'Contenido de prueba',
        'slug' => 'capacitacion-para-asignaciones',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now(),
        'exam_expires_at' => now()->addDay(),
    ]);

    return compact('owner', 'session');
}

test('assignment by agency creates only missing enrollments and reports duplicates', function () {
    ['session' => $session] = enrollmentAssignmentContext();
    $agency = Agency::create(['name' => 'Agencia Central']);
    $firstUser = User::factory()->create(['agency_id' => $agency->id]);
    $secondUser = User::factory()->create(['agency_id' => $agency->id]);
    Enrollment::create(['training_session_id' => $session->id, 'user_id' => $firstUser->id]);

    $result = app(EnrollmentAssignmentService::class)->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_AGENCY,
        'agency_id' => $agency->id,
    ]);

    expect($result->matched)->toBe(2)
        ->and($result->created)->toBe(1)
        ->and($result->alreadyAssigned)->toBe(1)
        ->and(Enrollment::query()->where('training_session_id', $session->id)->count())->toBe(2);
});

test('assignment by job position and group resolves the corresponding collaborators', function () {
    ['owner' => $owner, 'session' => $session] = enrollmentAssignmentContext();
    $position = JobPosition::create(['name' => 'Supervisor']);
    $positionMember = User::factory()->create(['job_position_id' => $position->id]);
    $groupMember = User::factory()->create();
    $group = CollaboratorGroup::create([
        'name' => 'Grupo de prueba para asignación',
        'created_by' => $owner->id,
    ]);
    $group->members()->attach($groupMember->id);
    $service = app(EnrollmentAssignmentService::class);

    $byPosition = $service->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_JOB_POSITION,
        'job_position_id' => $position->id,
    ]);
    $byGroup = $service->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_GROUP,
        'collaborator_group_id' => $group->id,
    ]);

    expect($byPosition->created)->toBe(1)
        ->and($byGroup->created)->toBe(1)
        ->and(Enrollment::query()->where('training_session_id', $session->id)->pluck('user_id')->all())
        ->toEqualCanonicalizing([$positionMember->id, $groupMember->id]);
});

test('manual assignment removes repeated selected users and rejects an empty selection', function () {
    ['session' => $session] = enrollmentAssignmentContext();
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $service = app(EnrollmentAssignmentService::class);

    $result = $service->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_MANUAL,
        'user_ids' => [$firstUser->id, $firstUser->id, $secondUser->id],
    ]);

    expect($result->matched)->toBe(2)
        ->and($result->created)->toBe(2)
        ->and($result->alreadyAssigned)->toBe(0);

    expect(fn () => $service->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_MANUAL,
        'user_ids' => [],
    ]))->toThrow(ValidationException::class);
});

test('assignment blocks users with an active or completed enrollment in the same training', function () {
    ['owner' => $owner, 'session' => $session] = enrollmentAssignmentContext();
    $user = User::factory()->create();
    $previousSession = TrainingSession::create([
        'training_id' => $session->training_id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->addDay(),
        'exam_expires_at' => now()->addDays(2),
    ]);
    Enrollment::create([
        'training_session_id' => $previousSession->id,
        'user_id' => $user->id,
        'status' => 'completado',
    ]);

    expect(fn () => app(EnrollmentAssignmentService::class)->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_MANUAL,
        'user_ids' => [$user->id],
    ]))->toThrow(ValidationException::class, 'inscripción activa o completada');
});

test('assignment allows a user to retake a training only after a failed enrollment', function () {
    ['owner' => $owner, 'session' => $session] = enrollmentAssignmentContext();
    $user = User::factory()->create();
    $previousSession = TrainingSession::create([
        'training_id' => $session->training_id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->addDay(),
        'exam_expires_at' => now()->addDays(2),
    ]);
    Enrollment::create([
        'training_session_id' => $previousSession->id,
        'user_id' => $user->id,
        'status' => 'reprobado',
    ]);

    $result = app(EnrollmentAssignmentService::class)->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_MANUAL,
        'user_ids' => [$user->id],
    ]);

    expect($result->created)->toBe(1)
        ->and(Enrollment::query()->where('training_session_id', $session->id)->where('user_id', $user->id)->exists())
        ->toBeTrue();
});

test('assignment blocks users who already have another active session at the same date and time', function () {
    ['owner' => $owner, 'session' => $session] = enrollmentAssignmentContext();
    $scheduledAt = now()->addWeek()->startOfMinute();
    $session->update([
        'scheduled_at' => $scheduledAt,
        'exam_expires_at' => $scheduledAt->copy()->addHour(),
    ]);
    $user = User::factory()->create();
    $otherTraining = Training::create([
        'title' => 'Otra capacitación simultánea',
        'description' => 'Contenido de prueba',
        'slug' => 'otra-capacitacion-simultanea',
        'user_id' => $owner->id,
    ]);
    $otherSession = TrainingSession::create([
        'training_id' => $otherTraining->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => $scheduledAt,
        'exam_expires_at' => $scheduledAt->copy()->addHour(),
    ]);
    Enrollment::create([
        'training_session_id' => $otherSession->id,
        'user_id' => $user->id,
        'status' => 'asignado',
    ]);

    expect(fn () => app(EnrollmentAssignmentService::class)->assign($session, [
        'assignment_type' => EnrollmentAssignmentService::TYPE_MANUAL,
        'user_ids' => [$user->id],
    ]))->toThrow(ValidationException::class, 'misma fecha y hora');
});
