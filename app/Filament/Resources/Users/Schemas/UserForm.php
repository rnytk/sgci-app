<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

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
                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->default(fn (): array => Role::query()
                        ->where('name', 'colaborador')
                        ->where('guard_name', 'web')
                        ->pluck('id')
                        ->all())
                    ->visible(fn (): bool => Auth::user()?->hasRole('super_administrador') ?? false),
            ]);
    }
}
