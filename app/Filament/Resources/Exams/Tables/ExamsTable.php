<?php

namespace App\Filament\Resources\Exams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training.title')
                    ->label('Capacitación')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('passing_score')
                    ->label('Nota Mínima')
                    ->badge()
                    ->color('info'),

                TextColumn::make('questions_count')
                    ->label('Preguntas')
                    ->counts('questions')
                    ->alignCenter(),
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
