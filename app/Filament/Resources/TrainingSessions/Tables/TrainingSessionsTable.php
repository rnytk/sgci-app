<?php

namespace App\Filament\Resources\TrainingSessions\Tables;

use App\Models\Training;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('training.title')
                    ->label('Capacitación')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('enrollments_count')
                    ->label('Personas asignadas')
                    ->counts('enrollments')
                    ->alignCenter()
                    ->sortable()
                    ->badge(),

                TextColumn::make('trainer.name')
                    ->label('Creador')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('require_exam')
                    ->label('Examen')
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('issue_certificate')
                    ->label('Diploma')
                    ->boolean()
                    ->alignCenter(),

                // Permite activar el examen en vivo desde la lista
                ToggleColumn::make('exam_enabled')
                    ->label('Examen Activo')
                    ->onColor('success'),
            ])
            ->filters([
                SelectFilter::make('training_id')
                    ->label('Capacitación')
                    ->options(fn (): array => Training::query()
                        ->visibleTo(auth()->user())
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable(),
                SelectFilter::make('trainer_id')
                    ->label('Creador')
                    ->options(fn (): array => User::query()
                        ->when(
                            ! auth()->user()->isSuperAdministrator(),
                            fn ($query) => $query->whereKey(auth()->id()),
                        )
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                TernaryFilter::make('require_exam')
                    ->label('Requiere examen')
                    ->placeholder('Todos')
                    ->trueLabel('Sí requiere')
                    ->falseLabel('No requiere'),
                TernaryFilter::make('issue_certificate')
                    ->label('Genera diploma')
                    ->placeholder('Todos')
                    ->trueLabel('Sí genera')
                    ->falseLabel('No genera'),
                TernaryFilter::make('exam_enabled')
                    ->label('Examen habilitado')
                    ->placeholder('Todos')
                    ->trueLabel('Habilitado')
                    ->falseLabel('No habilitado'),
                Filter::make('scheduled_at')
                    ->label('Fecha programada')
                    ->schema([
                        DatePicker::make('scheduled_from')
                            ->label('Desde'),
                        DatePicker::make('scheduled_until')
                            ->label('Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['scheduled_from'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                        )
                        ->when(
                            $data['scheduled_until'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                        )),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
