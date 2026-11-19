<?php

use App\Filament\Resources\Agencies\Pages\ListAgencies;
use App\Filament\Resources\CollaboratorGroups\Pages\ListCollaboratorGroups;
use App\Filament\Resources\ExamAttempts\Pages\ListExamAttempts;
use App\Filament\Resources\Exams\Pages\ListExams;
use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Filament\Resources\TrainingSessions\Pages\ListTrainingSessions;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Agency;
use App\Models\CollaboratorGroup;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\JobPosition;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use Database\Seeders\SgciRolesSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(SgciRolesSeeder::class);
    Filament::setCurrentPanel('admin');
});

function tableFiltersContext(): array
{
    $superAdministrator = User::factory()->create();
    $superAdministrator->assignRole('super_administrador');
    $creator = User::factory()->create();
    $agency = Agency::create(['name' => 'Agencia Central', 'is_active' => true]);
    $jobPosition = JobPosition::create(['name' => 'Analista']);
    $firstCollaborator = User::factory()->create([
        'name' => 'Colaborador Uno',
        'agency_id' => $agency->id,
        'job_position_id' => $jobPosition->id,
    ]);
    $secondCollaborator = User::factory()->create(['name' => 'Colaborador Dos']);

    $firstTraining = Training::create([
        'title' => 'Capacitación Uno',
        'description' => 'Contenido',
        'slug' => 'capacitacion-uno-filtros',
        'user_id' => $creator->id,
    ]);
    $secondTraining = Training::create([
        'title' => 'Capacitación Dos',
        'description' => 'Contenido',
        'slug' => 'capacitacion-dos-filtros',
        'user_id' => $creator->id,
    ]);
    $firstSession = TrainingSession::create([
        'training_id' => $firstTraining->id,
        'trainer_id' => $creator->id,
        'scheduled_at' => now()->startOfDay()->addHours(9),
        'exam_expires_at' => now()->addDay(),
        'require_exam' => true,
    ]);
    $secondSession = TrainingSession::create([
        'training_id' => $secondTraining->id,
        'trainer_id' => $creator->id,
        'scheduled_at' => now()->addDay()->startOfDay()->addHours(9),
        'exam_expires_at' => now()->addDays(2),
    ]);
    $firstEnrollment = Enrollment::create([
        'training_session_id' => $firstSession->id,
        'user_id' => $firstCollaborator->id,
    ]);
    $secondEnrollment = Enrollment::create([
        'training_session_id' => $secondSession->id,
        'user_id' => $secondCollaborator->id,
    ]);
    $firstAttempt = ExamAttempt::create([
        'enrollment_id' => $firstEnrollment->id,
        'status' => ExamAttempt::STATUS_PENDING_REVIEW,
        'submitted_at' => now()->startOfDay()->addHours(10),
    ]);
    $secondAttempt = ExamAttempt::create([
        'enrollment_id' => $secondEnrollment->id,
        'status' => ExamAttempt::STATUS_PASSED,
        'submitted_at' => now()->addDay()->startOfDay()->addHours(10),
        'finalized_at' => now()->addDay(),
        'passed' => true,
        'score' => 100,
    ]);
    Exam::create(['training_id' => $firstTraining->id, 'passing_score' => 70]);
    CollaboratorGroup::create(['name' => 'Grupo de prueba', 'created_by' => $creator->id]);

    return compact(
        'superAdministrator',
        'firstTraining',
        'firstSession',
        'secondSession',
        'firstAttempt',
        'secondAttempt',
        'firstCollaborator',
    );
}

test('programming filters narrow records by training and date', function () {
    [
        'superAdministrator' => $superAdministrator,
        'firstTraining' => $firstTraining,
        'firstSession' => $firstSession,
        'secondSession' => $secondSession,
    ] = tableFiltersContext();

    Livewire::actingAs($superAdministrator)
        ->test(ListTrainingSessions::class)
        ->assertTableFilterExists('training_id')
        ->assertTableFilterExists('trainer_id')
        ->assertTableFilterExists('require_exam')
        ->assertTableFilterExists('issue_certificate')
        ->assertTableFilterExists('exam_enabled')
        ->assertTableFilterExists('scheduled_at')
        ->filterTable('training_id', $firstTraining->id)
        ->assertCanSeeTableRecords([$firstSession])
        ->assertCanNotSeeTableRecords([$secondSession])
        ->resetTableFilters()
        ->filterTable('scheduled_at', [
            'scheduled_from' => now()->toDateString(),
            'scheduled_until' => now()->toDateString(),
        ])
        ->assertCanSeeTableRecords([$firstSession])
        ->assertCanNotSeeTableRecords([$secondSession]);
});

test('exam attempt filters narrow records by related data and status', function () {
    [
        'superAdministrator' => $superAdministrator,
        'firstTraining' => $firstTraining,
        'firstSession' => $firstSession,
        'firstAttempt' => $firstAttempt,
        'secondAttempt' => $secondAttempt,
        'firstCollaborator' => $firstCollaborator,
    ] = tableFiltersContext();

    Livewire::actingAs($superAdministrator)
        ->test(ListExamAttempts::class)
        ->assertTableFilterExists('training')
        ->assertTableFilterExists('training_session')
        ->assertTableFilterExists('collaborator')
        ->assertTableFilterExists('status')
        ->assertTableFilterExists('submitted_at')
        ->filterTable('training', ['training_id' => $firstTraining->id])
        ->assertCanSeeTableRecords([$firstAttempt])
        ->assertCanNotSeeTableRecords([$secondAttempt])
        ->resetTableFilters()
        ->filterTable('training_session', ['training_session_id' => $firstSession->id])
        ->assertCanSeeTableRecords([$firstAttempt])
        ->assertCanNotSeeTableRecords([$secondAttempt])
        ->resetTableFilters()
        ->filterTable('collaborator', ['user_id' => $firstCollaborator->id])
        ->assertCanSeeTableRecords([$firstAttempt])
        ->assertCanNotSeeTableRecords([$secondAttempt])
        ->resetTableFilters()
        ->filterTable('status', ExamAttempt::STATUS_PENDING_REVIEW)
        ->assertCanSeeTableRecords([$firstAttempt])
        ->assertCanNotSeeTableRecords([$secondAttempt]);
});

test('catalog tables register their operational filters', function () {
    ['superAdministrator' => $superAdministrator] = tableFiltersContext();

    Livewire::actingAs($superAdministrator)
        ->test(ListTrainings::class)
        ->assertTableFilterExists('user_id')
        ->assertTableFilterExists('created_at');

    Livewire::actingAs($superAdministrator)
        ->test(ListExams::class)
        ->assertTableFilterExists('training');

    Livewire::actingAs($superAdministrator)
        ->test(ListUsers::class)
        ->assertTableFilterExists('agency_id')
        ->assertTableFilterExists('job_position_id')
        ->assertTableFilterExists('roles')
        ->assertTableFilterExists('is_active')
        ->assertTableFilterExists('created_at');

    Livewire::actingAs($superAdministrator)
        ->test(ListCollaboratorGroups::class)
        ->assertTableFilterExists('created_by')
        ->assertTableFilterExists('created_at');

    Livewire::actingAs($superAdministrator)
        ->test(ListAgencies::class)
        ->assertTableFilterExists('is_active');
});
