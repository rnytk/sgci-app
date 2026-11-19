<?php

namespace App\Filament\Resources\ExamAttempts;

use App\Filament\Resources\ExamAttempts\Pages\EditExamAttempt;
use App\Filament\Resources\ExamAttempts\Pages\ListExamAttempts;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static ?string $modelLabel = 'Intento de evaluación';

    protected static ?string $pluralModelLabel = 'Intentos de evaluación';

    protected static string|UnitEnum|null $navigationGroup = 'Control de evaluaciones';

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
            ->filters([
                Filter::make('training')
                    ->label('Capacitación')
                    ->schema([
                        Select::make('training_id')
                            ->label('Capacitación')
                            ->options(fn (): array => static::visibleTrainingOptions())
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['training_id'] ?? null,
                        fn (Builder $query, int $trainingId): Builder => $query->whereHas(
                            'enrollment.trainingSession',
                            fn (Builder $sessionQuery) => $sessionQuery->where('training_id', $trainingId),
                        ),
                    )),
                Filter::make('training_session')
                    ->label('Programación')
                    ->schema([
                        Select::make('training_session_id')
                            ->label('Programación')
                            ->options(fn (): array => static::visibleSessionOptions())
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['training_session_id'] ?? null,
                        fn (Builder $query, int $sessionId): Builder => $query->whereHas(
                            'enrollment',
                            fn (Builder $enrollmentQuery) => $enrollmentQuery->where('training_session_id', $sessionId),
                        ),
                    )),
                Filter::make('collaborator')
                    ->label('Colaborador')
                    ->schema([
                        Select::make('user_id')
                            ->label('Colaborador')
                            ->options(fn (): array => static::visibleCollaboratorOptions())
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['user_id'] ?? null,
                        fn (Builder $query, int $userId): Builder => $query->whereHas(
                            'enrollment',
                            fn (Builder $enrollmentQuery) => $enrollmentQuery->where('user_id', $userId),
                        ),
                    )),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        ExamAttempt::STATUS_SUBMITTED => 'Enviado',
                        ExamAttempt::STATUS_PENDING_REVIEW => 'Pendiente de calificación',
                        ExamAttempt::STATUS_PASSED => 'Aprobado',
                        ExamAttempt::STATUS_FAILED => 'Reprobado',
                    ]),
                Filter::make('submitted_at')
                    ->label('Fecha de envío')
                    ->schema([
                        DatePicker::make('submitted_from')->label('Desde'),
                        DatePicker::make('submitted_until')->label('Hasta'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['submitted_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('submitted_at', '>=', $date))
                        ->when($data['submitted_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('submitted_at', '<=', $date))),
            ])
            ->recordActions([
                EditAction::make()->label('Calificar'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    /** @return array<int, string> */
    private static function visibleTrainingOptions(): array
    {
        $user = auth()->user();

        return Training::query()
            ->whereHas('trainingSessions.enrollments.examAttempts', fn (Builder $query) => $query->visibleTo($user))
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    /** @return array<int, string> */
    private static function visibleSessionOptions(): array
    {
        $user = auth()->user();

        return TrainingSession::query()
            ->whereHas('enrollments.examAttempts', fn (Builder $query) => $query->visibleTo($user))
            ->with('training')
            ->orderByDesc('scheduled_at')
            ->get()
            ->mapWithKeys(fn (TrainingSession $session): array => [
                $session->id => sprintf('%s — %s', $session->training->title, $session->scheduled_at->format('d/m/Y H:i')),
            ])
            ->all();
    }

    /** @return array<int, string> */
    private static function visibleCollaboratorOptions(): array
    {
        $user = auth()->user();

        return User::query()
            ->whereHas('enrollments.examAttempts', fn (Builder $query) => $query->visibleTo($user))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $collaborator): array => [$collaborator->id => "{$collaborator->name} ({$collaborator->email})"])
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamAttempts::route('/'),
            'edit' => EditExamAttempt::route('/{record}/edit'),
        ];
    }
}
