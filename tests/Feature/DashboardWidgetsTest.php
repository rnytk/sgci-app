<?php

use App\Filament\Pages\DashboardIndicatorDetails;
use App\Filament\Widgets\AttendanceByAgencyChartWidget;
use App\Filament\Widgets\EvaluationStatsWidget;
use App\Filament\Widgets\PendingExamReviewsWidget;
use App\Filament\Widgets\TrainingManagementStatsWidget;
use App\Filament\Widgets\UpcomingTrainingSessionsWidget;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
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

function dashboardWidgetsContext(): array
{
    $administrator = User::factory()->create();
    $administrator->assignRole('administrador_capacitaciones');
    $otherAdministrator = User::factory()->create();
    $otherAdministrator->assignRole('administrador_capacitaciones');
    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluador');

    $ownTraining = Training::create([
        'title' => 'Capacitación propia',
        'description' => 'Contenido',
        'slug' => 'capacitacion-propia-dashboard',
        'user_id' => $administrator->id,
    ]);
    $ownSession = TrainingSession::create([
        'training_id' => $ownTraining->id,
        'trainer_id' => $administrator->id,
        'scheduled_at' => now()->addDay(),
        'exam_expires_at' => now()->addDays(2),
    ]);
    $ownEnrollment = Enrollment::create([
        'training_session_id' => $ownSession->id,
        'user_id' => User::factory()->create(['name' => 'Colaborador propio'])->id,
    ]);
    ExamAttempt::create([
        'enrollment_id' => $ownEnrollment->id,
        'status' => ExamAttempt::STATUS_PENDING_REVIEW,
        'submitted_at' => now(),
    ]);

    $otherTraining = Training::create([
        'title' => 'Capacitación ajena',
        'description' => 'Contenido',
        'slug' => 'capacitacion-ajena-dashboard',
        'user_id' => $otherAdministrator->id,
    ]);
    $otherSession = TrainingSession::create([
        'training_id' => $otherTraining->id,
        'trainer_id' => $otherAdministrator->id,
        'scheduled_at' => now()->addDays(2),
        'exam_expires_at' => now()->addDays(3),
    ]);
    $otherEnrollment = Enrollment::create([
        'training_session_id' => $otherSession->id,
        'user_id' => User::factory()->create(['name' => 'Colaborador ajeno'])->id,
    ]);
    ExamAttempt::create([
        'enrollment_id' => $otherEnrollment->id,
        'status' => ExamAttempt::STATUS_PENDING_REVIEW,
        'submitted_at' => now(),
    ]);

    return compact('administrator', 'evaluator', 'ownTraining', 'ownSession');
}

test('training administrators see only their own dashboard records', function () {
    ['administrator' => $administrator] = dashboardWidgetsContext();

    Livewire::actingAs($administrator)
        ->test(UpcomingTrainingSessionsWidget::class)
        ->assertSee('Capacitación propia')
        ->assertDontSee('Capacitación ajena');

    Livewire::actingAs($administrator)
        ->test(PendingExamReviewsWidget::class)
        ->assertSee('Colaborador propio')
        ->assertDontSee('Colaborador ajeno');
});

test('evaluators see only evaluation widgets and can review all pending attempts', function () {
    ['evaluator' => $evaluator] = dashboardWidgetsContext();

    $this->actingAs($evaluator);
    expect(TrainingManagementStatsWidget::canView())->toBeFalse()
        ->and(AttendanceByAgencyChartWidget::canView())->toBeFalse()
        ->and(EvaluationStatsWidget::canView())->toBeTrue()
        ->and(PendingExamReviewsWidget::canView())->toBeTrue();

    Livewire::actingAs($evaluator)
        ->test(PendingExamReviewsWidget::class)
        ->assertSee('Colaborador propio')
        ->assertSee('Colaborador ajeno');
});

test('dashboard widgets can be filtered by training and programming', function () {
    ['ownTraining' => $ownTraining, 'ownSession' => $ownSession] = dashboardWidgetsContext();
    $superAdministrator = User::factory()->create();
    $superAdministrator->assignRole('super_administrador');

    Livewire::actingAs($superAdministrator)
        ->test(UpcomingTrainingSessionsWidget::class, [
            'pageFilters' => ['training_id' => $ownTraining->id],
        ])
        ->assertSee('Capacitación propia')
        ->assertDontSee('Capacitación ajena');

    Livewire::actingAs($superAdministrator)
        ->test(UpcomingTrainingSessionsWidget::class, [
            'pageFilters' => ['training_session_id' => $ownSession->id],
        ])
        ->assertSee('Capacitación propia')
        ->assertDontSee('Capacitación ajena');
});

test('dashboard indicators open a scoped detail table for the selected data', function () {
    ['administrator' => $administrator, 'ownSession' => $ownSession] = dashboardWidgetsContext();

    Livewire::actingAs($administrator)
        ->test(DashboardIndicatorDetails::class, ['indicator' => 'enrollments'])
        ->assertSee('Colaborador propio')
        ->assertDontSee('Colaborador ajeno')
        ->set('dashboardFilters', ['training_session_id' => $ownSession->id])
        ->assertSee('Colaborador propio')
        ->assertDontSee('Colaborador ajeno');
});

test('evaluators cannot access training-management indicator details', function () {
    ['evaluator' => $evaluator] = dashboardWidgetsContext();

    $this->actingAs($evaluator)
        ->get(DashboardIndicatorDetails::getUrl(['indicator' => 'enrollments'], isAbsolute: false))
        ->assertForbidden();
});

test('the attendance chart exposes a native link to its detail without rendering JavaScript as text', function () {
    ['administrator' => $administrator] = dashboardWidgetsContext();

    Livewire::actingAs($administrator)
        ->test(AttendanceByAgencyChartWidget::class)
        ->assertSee('Ver detalle de asistencia')
        ->assertDontSee('event.native.target.style.cursor');
});
