<?php

namespace App\Filament\Resources\TrainingSessions\Schemas;

use App\Enums\TrainingModality;
use App\Models\Training;
use App\Models\TrainingSession;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        ->live()
                        ->afterStateUpdated(function (?int $state, Set $set): void {
                            $set('passing_score', Training::with('exams')->find($state)?->exams?->passing_score ?? 70);
                        })
                        ->relationship(
                            name: 'training',
                            titleAttribute: 'title',
                            // Seguridad: Solo ves lo que tú creaste en el catálogo
                            modifyQueryUsing: fn (Builder $query) => $query->where('user_id', auth()->id())
                        )
                        ->required()
                        ->searchable(),

                    Select::make('modality')
                        ->label('Modalidad')
                        ->options(collect(TrainingModality::cases())->mapWithKeys(
                            fn (TrainingModality $modality): array => [$modality->value => $modality->label()]
                        ))
                        ->default(TrainingModality::InPerson->value)
                        ->live()
                        ->required(),

                    DateTimePicker::make('scheduled_at')
                        ->label('Fecha y Hora de Inicio')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y H:i'),

                    DateTimePicker::make('ends_at')
                        ->label('Fecha y Hora de Fin')
                        ->required(fn (?TrainingSession $record): bool => $record === null)
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->after('scheduled_at'),

                    TextInput::make('location')
                        ->label('Ubicación')
                        ->visible(fn (Get $get): bool => $get('modality') === TrainingModality::InPerson->value)
                        ->maxLength(255),

                    TextInput::make('meeting_link')
                        ->label('Enlace de Reunión (Virtual)')
                        ->url()
                        ->placeholder('https://teams.microsoft.com/...')
                        ->visible(fn (Get $get): bool => $get('modality') === TrainingModality::Virtual->value),
                ])->columns(2),

            Section::make('Reglas de Cumplimiento')
                ->description('Estas reglas quedan guardadas en la sesión y no cambian si luego se modifica el examen maestro.')
                ->schema([
                    Toggle::make('requires_attendance')
                        ->label('¿Requiere asistencia?')
                        ->default(false)
                        ->helperText('Presencial y virtual pueden exigir asistencia; autogestionada se acredita con el examen.'),

                    Toggle::make('require_exam')
                        ->label('¿Requiere Examen?')
                        ->default(true),

                    TextInput::make('passing_score')
                        ->label('Nota mínima')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(70)
                        ->required()
                        ->suffix('pts'),

                    TextInput::make('max_attempts')
                        ->label('Máximo de intentos')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->default(1)
                        ->required(),

                    Toggle::make('issue_certificate')
                        ->label('¿Emitir Diploma?')
                        ->default(false)
                        ->live(),

                    Toggle::make('exam_enabled')
                        ->label('Habilitar Examen (Control Manual)')
                        ->onColor('success')
                        ->helperText('Activa esto solo cuando desees que los alumnos empiecen el examen.'),

                    FileUpload::make('diploma_bg')
                        ->label('Arte del Diploma')
                        ->directory('training-sessions/diplomas')
                        ->visible(fn (Get $get): bool => $get('issue_certificate') === true)
                        ->image()
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }
}
