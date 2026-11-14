<?php

namespace App\Filament\Resources\CollaboratorGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CollaboratorGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del grupo')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(2000)
                    ->columnSpanFull(),
                Select::make('members')
                    ->label('Integrantes')
                    ->relationship('members', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Selecciona las personas que formarán parte de este grupo.')
                    ->columnSpanFull(),
            ]);
    }
}
