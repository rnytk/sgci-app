<?php

namespace App\Filament\Resources\TrainingSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TrainingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training.title')
                    ->label('Capacitación')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i')
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
                //
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
