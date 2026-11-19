<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamAttempts\ExamAttemptResource;
use App\Filament\Widgets\Concerns\InteractsWithTrainingDashboardFilters;
use App\Models\ExamAttempt;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingExamReviewsWidget extends TableWidget
{
    use InteractsWithTrainingDashboardFilters;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdministrator()
            || $user?->hasRole('administrador_capacitaciones')
            || $user?->hasRole('evaluador');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Evaluaciones pendientes de calificar')
            ->description('Intentos con preguntas abiertas que requieren revisión manual.')
            ->query(
                $this->applyExamAttemptFilters(ExamAttempt::query()
                    ->visibleTo(auth()->user()))
                    ->with(['enrollment.user', 'enrollment.trainingSession.training'])
                    ->where('status', ExamAttempt::STATUS_PENDING_REVIEW)
                    ->orderBy('submitted_at'),
            )
            ->columns([
                TextColumn::make('enrollment.user.name')
                    ->label('Colaborador')
                    ->searchable(),
                TextColumn::make('enrollment.trainingSession.training.title')
                    ->label('Capacitación')
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('view_all')
                    ->label('Ver todas')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url($this->dashboardDetailUrl('pending_reviews')),
            ])
            ->recordUrl(fn (ExamAttempt $record): string => ExamAttemptResource::getUrl('edit', ['record' => $record]))
            ->defaultPaginationPageOption(5);
    }
}
