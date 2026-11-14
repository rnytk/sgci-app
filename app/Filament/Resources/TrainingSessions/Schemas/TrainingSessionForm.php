<?php

namespace App\Filament\Resources\TrainingSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TrainingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Logística de la Sesión')
                ->schema([
                    // Se asigna automáticamente al usuario logueado
                    Hidden::make('trainer_id')
                        ->dehydrated(false),

                    Select::make('training_id')
                        ->label('Capacitación Maestro')
                        ->relationship(
                            name: 'training',
                            titleAttribute: 'title',
                            // Seguridad: Solo ves lo que tú creaste en el catálogo
                            modifyQueryUsing: fn (Builder $query) => $query->visibleTo(Auth::user())
                        )
                        ->required()
                        ->searchable(),

                    DateTimePicker::make('scheduled_at')
                        ->label('Fecha y Hora Programada')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),

                    DateTimePicker::make('exam_expires_at')
                        ->label('Hora de Expiración del Examen')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->after('scheduled_at')
                        ->validationMessages([
                            'after' => 'La hora de expiración debe ser posterior a la fecha programada.',
                        ]),

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
                        ->default(false),

                    Toggle::make('issue_certificate')
                        ->label('¿Generar Diploma?')
                        ->default(false)
                        ->live(),

                    Toggle::make('exam_enabled')
                        ->label('Habilitar Examen (Control Manual)')
                        ->onColor('success')
                        ->helperText('Activa esto solo cuando desees que los alumnos empiecen el examen.'),

                    FileUpload::make('diploma_bg')
                        ->label('Plantilla del diploma')
                        ->disk('public')
                        ->directory('training-sessions/diplomas')
                        ->required(fn ($get) => $get('issue_certificate') === true)
                        ->visible(fn ($get) => $get('issue_certificate') === true)
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png'])
                        ->helperText('Sube la plantilla SGCI aprobada. El sistema colocará únicamente nombre, capacitación y fecha en sus espacios definidos.')
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }
}
