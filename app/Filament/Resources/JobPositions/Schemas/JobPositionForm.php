<?php

namespace App\Filament\Resources\JobPositions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JobPositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del puesto')
                    ->required()
                    ->unique(),
                TextInput::make('description')
                    ->label('Descripción')
                    ->required(),
            ]);
    }
}
