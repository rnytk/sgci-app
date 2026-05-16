<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                /*TextInput::make('password')
                    ->password()
                    ->required(),*/
                Select::make('agency_id')
                    ->relationship('agency', 'name')
                    ->label('Agencia')
                    ->nullable(),
                Select::make('job_position_id')
                    ->relationship('jobPosition', 'name')
                    ->label('Puesto de Trabajo')
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Usuario Activo')
                    ->default(true),
            ]);
    }
}
