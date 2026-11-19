<?php

namespace App\Filament\Pages\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait InteractsWithDashboardDetailFilters
{
    /** @var array<string, mixed> */
    public array $dashboardFilters = [];

    protected function selectedTrainingId(): ?int
    {
        $trainingId = $this->dashboardFilters['training_id'] ?? null;

        return filter_var($trainingId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    protected function selectedTrainingSessionId(): ?int
    {
        $sessionId = $this->dashboardFilters['training_session_id'] ?? null;

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
}
