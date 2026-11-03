<?php

namespace App\Filament\Resources\ExamAttempts;

use App\Filament\Resources\ExamAttempts\Pages\EditExamAttempt;
use App\Filament\Resources\ExamAttempts\Pages\ListExamAttempts;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static ?string $modelLabel = 'Intento de evaluación';

    protected static ?string $pluralModelLabel = 'Intentos de evaluación';

    protected static string|UnitEnum|null $navigationGroup = 'Capacitaciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Respuestas del colaborador')
                ->schema([
                    Repeater::make('answers')
                        ->relationship()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->schema([
                            Placeholder::make('question_text')
                                ->label('Pregunta')
                                ->content(fn (?ExamAttemptAnswer $record): string => $record?->question?->question_text ?? ''),
                            Placeholder::make('answer')
                                ->label('Respuesta seleccionada')
                                ->content(fn (?ExamAttemptAnswer $record): string => $record?->selectedOption?->option_text ?? ''),
                            Textarea::make('answer_text')
                                ->label('Respuesta abierta')
                                ->disabled()
                                ->visible(fn (?ExamAttemptAnswer $record): bool => $record?->question?->type === 'open'),
                            TextInput::make('awarded_points')
                                ->label('Puntos otorgados')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn (?ExamAttemptAnswer $record): int => $record?->question?->points ?? 0)
                                ->required(fn (?ExamAttemptAnswer $record): bool => $record?->question?->type === 'open')
                                ->visible(fn (?ExamAttemptAnswer $record): bool => $record?->question?->type === 'open'),
                        ])
                        ->columns(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment.user.name')->label('Colaborador')->searchable(),
                TextColumn::make('enrollment.trainingSession.training.title')->label('Capacitación')->searchable(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('submitted_at')->label('Enviado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Calificar'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamAttempts::route('/'),
            'edit' => EditExamAttempt::route('/{record}/edit'),
        ];
    }
}
