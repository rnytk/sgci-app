<?php

namespace App\Filament\Resources\TrainingSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TrainingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Logística de la Sesión')
                ->schema([
                    // Se asigna automáticamente al usuario logueado
                    Hidden::make('trainer_id')
                        ->default(auth()->id()),

                    Select::make('training_id')
                        ->label('Capacitación Maestro')
                        ->relationship(
                            name: 'training', 
                            titleAttribute: 'title',
                            // Seguridad: Solo ves lo que tú creaste en el catálogo
                            modifyQueryUsing: fn (Builder $query) => $query->where('user_id', auth()->id())
                        )
                        ->required()
                        ->searchable(),

                    DateTimePicker::make('scheduled_at')
                        ->label('Fecha y Hora Programada')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),

                    TextInput::make('meeting_link')
                        ->label('Enlace de Reunión (Virtual)')
                        ->url()
                        ->placeholder('https://teams.microsoft.com/...'),
                ])->columns(2),

            Section::make('Reglas de Evaluación y Diploma')
                ->description('Configure si esta sesión específica requiere examen y si emitirá certificados.')
                ->schema([
                    Toggle::make('require_exam')
                        ->label('¿Requiere Examen?')
                        ->default(true),

                    Toggle::make('issue_certificate')
                        ->label('¿Generar Diploma?')
                        ->default(false)
                        ->live(),

                    Toggle::make('exam_enabled')
                        ->label('Habilitar Examen (Control Manual)')
                        ->onColor('success')
                        ->helperText('Activa esto solo cuando desees que los alumnos empiecen el examen.'),

                    FileUpload::make('diploma_bg')
                        ->label('Arte del Diploma')
                        ->directory('training-sessions/diplomas')
                        ->visible(fn ($get) => $get('issue_certificate') === true)
                        ->image()
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }
}