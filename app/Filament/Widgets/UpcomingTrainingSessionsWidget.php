<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use App\Filament\Widgets\Concerns\InteractsWithTrainingDashboardFilters;
use App\Models\TrainingSession;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingTrainingSessionsWidget extends TableWidget
{
    use InteractsWithTrainingDashboardFilters;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdministrator() || $user?->hasRole('administrador_capacitaciones');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Próximas programaciones')
            ->description('Sesiones programadas desde hoy.')
            ->query(
                $this->applyTrainingSessionFilters(TrainingSession::query()
                    ->visibleTo(auth()->user()))
                    ->with('training')
                    ->withCount('enrollments')
                    ->where('scheduled_at', '>=', now()->startOfDay())
                    ->orderBy('scheduled_at')
                    ->limit(5),
            )
            ->columns([
                TextColumn::make('training.title')
                    ->label('Capacitación')
                    ->searchable(),
                TextColumn::make('scheduled_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('enrollments_count')
                    ->label('Asignados')
                    ->alignCenter()
                    ->badge(),
            ])
            ->headerActions([
                Action::make('view_all')
                    ->label('Ver todas')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url($this->dashboardDetailUrl('upcoming_sessions')),
            ])
            ->recordUrl(fn (TrainingSession $record): string => TrainingSessionResource::getUrl('edit', ['record' => $record]))
            ->paginated(false);
    }
}
