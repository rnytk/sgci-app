<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTrainingDashboardFilters;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrainingManagementStatsWidget extends StatsOverviewWidget
{
    use InteractsWithTrainingDashboardFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Resumen de capacitaciones';

    protected ?string $description = 'Indicadores de las capacitaciones que puedes administrar.';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdministrator() || $user?->hasRole('administrador_capacitaciones');
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $enrollments = $this->applyEnrollmentFilters(Enrollment::query()->visibleTo($user));
        $totalEnrollments = (clone $enrollments)->count();
        $presentEnrollments = (clone $enrollments)
            ->where('attendance_status', Enrollment::ATTENDANCE_PRESENT)
            ->count();

        $availableDiplomas = (clone $enrollments)
            ->whereHas('trainingSession', fn ($query) => $query
                ->where('issue_certificate', true)
                ->whereNotNull('diploma_bg'))
            ->whereHas('examAttempts', fn ($query) => $query
                ->where('status', ExamAttempt::STATUS_PASSED)
                ->whereNotNull('finalized_at'))
            ->count();

        return [
            Stat::make('Capacitaciones', $this->applyTrainingFilters(Training::query()->visibleTo($user))->count())
                ->description('Disponibles para tu gestión')
                ->icon('heroicon-o-academic-cap')
                ->color('primary')
                ->url($this->dashboardDetailUrl('trainings')),
            Stat::make('Próximas sesiones', $this->applyTrainingSessionFilters(TrainingSession::query()
                ->visibleTo($user))
                ->where('scheduled_at', '>=', now()->startOfDay())
                ->count())
                ->description('Programadas desde hoy')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->url($this->dashboardDetailUrl('upcoming_sessions')),
            Stat::make('Colaboradores asignados', $totalEnrollments)
                ->description('En tus programaciones')
                ->icon('heroicon-o-users')
                ->color('gray')
                ->url($this->dashboardDetailUrl('enrollments')),
            Stat::make('Asistencia registrada', "{$presentEnrollments} de {$totalEnrollments}")
                ->description('Marcados como presentes')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->url($this->dashboardDetailUrl('attendance')),
            Stat::make('Diplomas disponibles', $availableDiplomas)
                ->description('Resultados aprobados y finalizados')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url($this->dashboardDetailUrl('diplomas')),
        ];
    }
}
