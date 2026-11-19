<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithTrainingDashboardFilters;
use App\Models\ExamAttempt;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EvaluationStatsWidget extends StatsOverviewWidget
{
    use InteractsWithTrainingDashboardFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Estado de evaluaciones';

    protected ?string $description = 'Resultados e intentos que están dentro de tu alcance.';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdministrator()
            || $user?->hasRole('administrador_capacitaciones')
            || $user?->hasRole('evaluador');
    }

    protected function getStats(): array
    {
        $attempts = $this->applyExamAttemptFilters(ExamAttempt::query()->visibleTo(auth()->user()));

        return [
            Stat::make('Pendientes de calificar', (clone $attempts)
                ->where('status', ExamAttempt::STATUS_PENDING_REVIEW)
                ->count())
                ->description('Requieren revisión manual')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url($this->dashboardDetailUrl('pending_reviews')),
            Stat::make('Aprobados', (clone $attempts)
                ->where('status', ExamAttempt::STATUS_PASSED)
                ->whereNotNull('finalized_at')
                ->count())
                ->description('Con resultado final')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->url($this->dashboardDetailUrl('passed_attempts')),
            Stat::make('Reprobados', (clone $attempts)
                ->where('status', ExamAttempt::STATUS_FAILED)
                ->whereNotNull('finalized_at')
                ->count())
                ->description('Con resultado final')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->url($this->dashboardDetailUrl('failed_attempts')),
        ];
    }
}
