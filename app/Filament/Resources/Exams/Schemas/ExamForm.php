<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Models\Exam;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Configuración del Examen')
                ->schema([
                    Select::make('training_id')
                        ->label('Capacitación')
                        ->relationship(
                            name: 'training',
                            titleAttribute: 'title',
                            modifyQueryUsing: fn (Builder $query) => $query->visibleTo(auth()->user())
                        )
                        ->required()
                        ->unique(Exam::class, 'training_id', ignoreRecord: true)
                        ->validationMessages([
                            'unique' => 'Esta capacitación ya tiene una evaluación configurada.',
                        ])
                        ->searchable(),

                    TextInput::make('passing_score')
                        ->label('Nota de Aprobación')
                        ->numeric()
                        ->default(70)
                        ->suffix('pts'),
                ])->columns(2),

            Section::make('Preguntas y Respuestas')
                ->schema([
                    // 1. EL CONTADOR EN VIVO
                    Placeholder::make('total_points_counter')
                        ->label('Estado del Punteo')
                        ->content(function (Get $get) {
                            $questions = $get('questions') ?? [];
                            $total = collect($questions)->sum('points');

                            $color = $total == 100 ? 'text-success-600' : 'text-danger-600';
                            $icon = $total == 100 ? '✅' : '⚠️';

                            return new HtmlString(
                                "<div class='text-xl font-bold {$color}'>
                                    {$icon} Total acumulado: {$total} / 100 puntos
                                </div>"
                            );
                        }),

                    // 2. EL REPEATER CON VALIDACIÓN
                    Repeater::make('questions')
                        ->relationship('questions')
                        ->schema([
                            TextInput::make('question_text')->label('Pregunta')->required()->columnSpan(3),

                            Select::make('type')
                                ->label('Tipo')
                                ->options(['multiple' => 'Opción Múltiple', 'open' => 'Abierta'])
                                ->required()
                                ->live(),

                            TextInput::make('points')
                                ->label('Puntos')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true), // Hace que el contador se actualice al salir del campo

                            Repeater::make('questionOptions')
                                ->relationship('questionOptions')
                                ->label('Opciones de Respuesta')
                                ->schema([
                                    TextInput::make('option_text')->label('Texto')->required()->columnSpan(3),
                                    Toggle::make('is_correct')->label('¿Es Correcta?')->onColor('success'),
                                ])
                                ->columns(4)
                                ->columnSpanFull()
                                ->hidden(fn (Get $get) => $get('type') === 'open'),
                        ])
                        ->columns(5)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? 'Nueva Pregunta')

                        // 3. REGLA DE VALIDACIÓN PARA LOS 100 PUNTOS
                        ->rules([
                            fn () => function (string $attribute, $value, \Closure $fail) {
                                $total = collect($value)->sum('points');
                                if ($total != 100) {
                                    $fail("La suma de los puntos debe ser exactamente 100. Actualmente tienes {$total} puntos.");
                                }
                            },
                        ]),
                ]),
        ]);
    }
}
