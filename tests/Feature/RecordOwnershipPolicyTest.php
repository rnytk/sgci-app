<?php

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Resources\ExamAttempts\ExamAttemptResource;
use App\Filament\Resources\TrainingSessions\Pages\EditTrainingSession;
use App\Filament\Resources\TrainingSessions\RelationManagers\EnrollmentsRelationManager;
use Database\Seeders\SgciRolesSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->seed(SgciRolesSeeder::class);
});

function policyOwnershipContext(): array
{
    $owner = User::factory()->create();
    $owner->assignRole('administrador_capacitaciones');
    $otherAdministrator = User::factory()->create();
    $otherAdministrator->assignRole('administrador_capacitaciones');

    $training = Training::create([
        'title' => 'Capacitación privada',
        'description' => 'Contenido',
        'slug' => 'capacitacion-privada',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now(),
        'exam_expires_at' => now()->addDay(),
    ]);
    $exam = Exam::create(['training_id' => $training->id, 'passing_score' => 70]);
    $student = User::factory()->create();
    $enrollment = Enrollment::create(['training_session_id' => $session->id, 'user_id' => $student->id]);
    $attempt = ExamAttempt::create(['enrollment_id' => $enrollment->id, 'status' => ExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

    $otherTraining = Training::create([
        'title' => 'Capacitación de otro administrador',
        'description' => 'Contenido',
        'slug' => 'capacitacion-de-otro-administrador',
        'user_id' => $otherAdministrator->id,
    ]);
    $otherSession = TrainingSession::create([
        'training_id' => $otherTraining->id,
        'trainer_id' => $otherAdministrator->id,
        'scheduled_at' => now(),
        'exam_expires_at' => now()->addDay(),
    ]);
    $otherStudent = User::factory()->create();
    $otherEnrollment = Enrollment::create(['training_session_id' => $otherSession->id, 'user_id' => $otherStudent->id]);
    $otherAttempt = ExamAttempt::create(['enrollment_id' => $otherEnrollment->id, 'status' => ExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

    return compact('owner', 'otherAdministrator', 'training', 'session', 'exam', 'attempt', 'otherAttempt');
}

test('ownership policies deny direct access to another administrator records', function () {
    ['owner' => $owner, 'otherAdministrator' => $otherAdministrator, 'training' => $training, 'session' => $session, 'exam' => $exam] = policyOwnershipContext();

    expect($owner->can('view', $training))->toBeTrue()
        ->and($owner->can('update', $session))->toBeTrue()
        ->and($owner->can('delete', $exam))->toBeTrue()
        ->and($otherAdministrator->can('view', $training))->toBeFalse()
        ->and($otherAdministrator->can('update', $session))->toBeFalse()
        ->and($otherAdministrator->can('delete', $exam))->toBeFalse();
});

test('super administrator bypasses ownership and evaluator can review all attempts', function () {
    ['owner' => $owner, 'training' => $training, 'session' => $session, 'exam' => $exam, 'attempt' => $attempt, 'otherAttempt' => $otherAttempt] = policyOwnershipContext();

    $superAdministrator = User::factory()->create();
    $superAdministrator->assignRole('super_administrador');
    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluador');

    expect($superAdministrator->can('view', $training))->toBeTrue()
        ->and($superAdministrator->can('update', $session))->toBeTrue()
        ->and($superAdministrator->can('delete', $exam))->toBeTrue()
        ->and($superAdministrator->can('view', $attempt))->toBeTrue()
        ->and($evaluator->can('view', $attempt))->toBeTrue()
        ->and($evaluator->can('update', $attempt))->toBeTrue()
        ->and($owner->can('view', $attempt))->toBeTrue()
        ->and($owner->can('update', $attempt))->toBeTrue()
        ->and($owner->can('view', $otherAttempt))->toBeFalse()
        ->and($owner->can('update', $otherAttempt))->toBeFalse();
});

test('a direct Filament URL cannot expose another administrator training', function () {
    ['otherAdministrator' => $otherAdministrator, 'training' => $training] = policyOwnershipContext();

    Filament::setCurrentPanel('admin');

    $this->actingAs($otherAdministrator)
        ->get(TrainingResource::getUrl('edit', ['record' => $training], panel: 'admin'))
        ->assertNotFound();
});

test('a training administrator cannot open another creator exam attempt by URL', function () {
    ['owner' => $owner, 'otherAttempt' => $otherAttempt] = policyOwnershipContext();

    Filament::setCurrentPanel('admin');

    $this->actingAs($owner)
        ->get(ExamAttemptResource::getUrl('edit', ['record' => $otherAttempt], panel: 'admin'))
        ->assertNotFound();
});

test('enrollment relation manager is unavailable for another administrator session', function () {
    ['owner' => $owner, 'otherAdministrator' => $otherAdministrator, 'session' => $session] = policyOwnershipContext();

    $this->actingAs($owner);
    expect(EnrollmentsRelationManager::canViewForRecord($session, EditTrainingSession::class))->toBeTrue();

    $this->actingAs($otherAdministrator);
    expect(EnrollmentsRelationManager::canViewForRecord($session, EditTrainingSession::class))->toBeFalse();
});
