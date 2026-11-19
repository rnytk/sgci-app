<?php

namespace App\Filament\Widgets\Concerns;

use App\Filament\Pages\DashboardIndicatorDetails;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithTrainingDashboardFilters
{
    use InteractsWithPageFilters;

    protected function selectedTrainingId(): ?int
    {
        $trainingId = ($this->pageFilters ?? [])['training_id'] ?? null;

        return filter_var($trainingId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    protected function selectedTrainingSessionId(): ?int
    {
        $sessionId = ($this->pageFilters ?? [])['training_session_id'] ?? null;

        return filter_var($sessionId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    protected function applyTrainingFilters(Builder $query): Builder
    {
        return $query
            ->when($this->selectedTrainingId(), fn (Builder $query, int $trainingId): Builder => $query->whereKey($trainingId))
            ->when($this->selectedTrainingSessionId(), fn (Builder $query, int $sessionId): Builder => $query->whereHas(
                'trainingSessions',
                fn (Builder $sessionQuery) => $sessionQuery->whereKey($sessionId),
            ));
    }

    protected function applyTrainingSessionFilters(Builder $query): Builder
    {
        return $query
            ->when($this->selectedTrainingId(), fn (Builder $query, int $trainingId): Builder => $query->where('training_id', $trainingId))
            ->when($this->selectedTrainingSessionId(), fn (Builder $query, int $sessionId): Builder => $query->whereKey($sessionId));
    }

    protected function applyEnrollmentFilters(Builder $query): Builder
    {
        return $query
            ->when($this->selectedTrainingId(), fn (Builder $query, int $trainingId): Builder => $query->whereHas(
                'trainingSession',
                fn (Builder $sessionQuery) => $sessionQuery->where('training_id', $trainingId),
            ))
            ->when($this->selectedTrainingSessionId(), fn (Builder $query, int $sessionId): Builder => $query->where('training_session_id', $sessionId));
    }

    protected function applyExamAttemptFilters(Builder $query): Builder
    {
        return $query
            ->when($this->selectedTrainingId(), fn (Builder $query, int $trainingId): Builder => $query->whereHas(
                'enrollment.trainingSession',
                fn (Builder $sessionQuery) => $sessionQuery->where('training_id', $trainingId),
            ))
            ->when($this->selectedTrainingSessionId(), fn (Builder $query, int $sessionId): Builder => $query->whereHas(
                'enrollment',
                fn (Builder $enrollmentQuery) => $enrollmentQuery->where('training_session_id', $sessionId),
            ));
    }

    protected function dashboardDetailUrl(string $indicator): string
    {
        $parameters = ['indicator' => $indicator];

        if ($trainingId = $this->selectedTrainingId()) {
            $parameters['training_id'] = $trainingId;
        }

        if ($sessionId = $this->selectedTrainingSessionId()) {
            $parameters['training_session_id'] = $sessionId;
        }

        return DashboardIndicatorDetails::getUrl($parameters, isAbsolute: false);
    }
}
