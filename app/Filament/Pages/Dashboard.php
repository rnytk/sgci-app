<?php

namespace App\Filament\Pages;

use App\Models\Training;
use App\Models\TrainingSession;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Escritorio';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_id')
                    ->label('Capacitación')
                    ->options(fn (): array => $this->trainingQuery()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('training_session_id', null)),
                Select::make('training_session_id')
                    ->label('Programación')
                    ->options(fn (Get $get): array => $this->sessionQuery()
                        ->when($get('training_id'), fn (Builder $query, $trainingId): Builder => $query->where('training_id', $trainingId))
                        ->with('training')
                        ->orderByDesc('scheduled_at')
                        ->get()
                        ->mapWithKeys(fn (TrainingSession $session): array => [
                            $session->id => sprintf('%s — %s', $session->training->title, $session->scheduled_at->format('d/m/Y H:i')),
                        ])
                        ->all())
                    ->searchable()
                    ->live(),
            ]);
    }

    private function trainingQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('evaluador') && ! $user->isSuperAdministrator() && ! $user->hasRole('administrador_capacitaciones')) {
            return Training::query()->whereHas(
                'trainingSessions.enrollments.examAttempts',
                fn (Builder $query) => $query->visibleTo($user),
            );
        }

        return Training::query()->visibleTo($user);
    }

    private function sessionQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('evaluador') && ! $user->isSuperAdministrator() && ! $user->hasRole('administrador_capacitaciones')) {
            return TrainingSession::query()->whereHas(
                'enrollments.examAttempts',
                fn (Builder $query) => $query->visibleTo($user),
            );
        }

        return TrainingSession::query()->visibleTo($user);
    }
}
