<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\InteractsWithDashboardDetailFilters;
use App\Filament\Resources\ExamAttempts\ExamAttemptResource;
use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DashboardIndicatorDetails extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithDashboardDetailFilters;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $slug = 'escritorio/detalle/{indicator}';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.dashboard-indicator-details';

    public string $indicator = 'trainings';

    /** @var array<int, string> */
    private const INDICATORS = [
        'trainings',
        'upcoming_sessions',
        'enrollments',
        'attendance',
        'attendance_by_agency',
        'diplomas',
        'pending_reviews',
        'passed_attempts',
        'failed_attempts',
    ];

    public function mount(string $indicator): void
    {
        abort_unless(in_array($indicator, self::INDICATORS, true), 404);
        abort_unless($this->canViewIndicator($indicator), 403);

        $this->indicator = $indicator;
        $this->dashboardFilters = array_filter([
            'training_id' => $this->requestPositiveInteger('training_id'),
            'training_session_id' => $this->requestPositiveInteger('training_session_id'),
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdministrator()
            || $user?->hasRole('administrador_capacitaciones')
            || $user?->hasRole('evaluador');
    }

    public function getTitle(): string
    {
        return $this->definition()['title'];
    }

    public function getHeading(): string
    {
        return $this->definition()['heading'];
    }

    public function getSubheading(): string
    {
        return $this->definition()['description'];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading($this->getHeading())
            ->description($this->getSubheading())
            ->query($this->detailQuery())
            ->columns($this->columns())
            ->recordUrl(fn (Model $record): ?string => $this->recordUrl($record))
            ->defaultSort($this->defaultSortColumn(), $this->defaultSortDirection())
            ->emptyStateHeading('No hay registros para mostrar')
            ->emptyStateDescription('Prueba ajustando los filtros del escritorio o vuelve cuando existan registros.')
            ->emptyStateIcon(Heroicon::OutlinedInformationCircle);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver al escritorio')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(Dashboard::getUrl($this->dashboardFilters, isAbsolute: false)),
        ];
    }

    private function detailQuery(): Builder
    {
        $user = auth()->user();

        return match ($this->indicator) {
            'trainings' => $this->applyTrainingFilters(Training::query()
                ->visibleTo($user)
                ->with('user')
                ->withCount('trainingSessions')),
            'upcoming_sessions' => $this->applyTrainingSessionFilters(TrainingSession::query()
                ->visibleTo($user)
                ->with('training')
                ->withCount('enrollments')
                ->where('scheduled_at', '>=', now()->startOfDay())),
            'enrollments', 'attendance', 'attendance_by_agency' => $this->enrollmentQuery(),
            'diplomas', 'pending_reviews', 'passed_attempts', 'failed_attempts' => $this->examAttemptQuery(),
        };
    }

    private function enrollmentQuery(): Builder
    {
        $query = $this->applyEnrollmentFilters(Enrollment::query()
            ->visibleTo(auth()->user())
            ->with(['user.agency', 'trainingSession.training']));

        if ($this->indicator === 'attendance') {
            $query->where('attendance_status', Enrollment::ATTENDANCE_PRESENT);
        }

        return $query;
    }

    private function examAttemptQuery(): Builder
    {
        $query = $this->applyExamAttemptFilters(ExamAttempt::query()
            ->visibleTo(auth()->user())
            ->with(['enrollment.user', 'enrollment.trainingSession.training']));

        return match ($this->indicator) {
            'diplomas' => $query
                ->where('status', ExamAttempt::STATUS_PASSED)
                ->whereNotNull('finalized_at')
                ->whereHas('enrollment.trainingSession', fn (Builder $sessionQuery) => $sessionQuery
                    ->where('issue_certificate', true)
                    ->whereNotNull('diploma_bg')),
            'pending_reviews' => $query->where('status', ExamAttempt::STATUS_PENDING_REVIEW),
            'passed_attempts' => $query
                ->where('status', ExamAttempt::STATUS_PASSED)
                ->whereNotNull('finalized_at'),
            'failed_attempts' => $query
                ->where('status', ExamAttempt::STATUS_FAILED)
                ->whereNotNull('finalized_at'),
            default => $query,
        };
    }

    /** @return array<int, TextColumn> */
    private function columns(): array
    {
        return match ($this->indicator) {
            'trainings' => [
                TextColumn::make('title')->label('Capacitación')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Creador')->searchable(),
                TextColumn::make('training_sessions_count')->label('Programaciones')->alignCenter()->badge(),
                TextColumn::make('created_at')->label('Creada')->dateTime('d/m/Y H:i')->sortable(),
            ],
            'upcoming_sessions' => [
                TextColumn::make('training.title')->label('Capacitación')->searchable()->sortable(),
                TextColumn::make('scheduled_at')->label('Fecha y hora')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('enrollments_count')->label('Asignados')->alignCenter()->badge(),
            ],
            'enrollments', 'attendance', 'attendance_by_agency' => [
                TextColumn::make('user.name')->label('Colaborador')->searchable()->sortable(),
                TextColumn::make('user.agency.name')->label('Agencia')->placeholder('Sin agencia')->toggleable(),
                TextColumn::make('trainingSession.training.title')->label('Capacitación')->searchable(),
                TextColumn::make('trainingSession.scheduled_at')->label('Programación')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('status')->label('Progreso')->badge(),
                TextColumn::make('attendance_status')->label('Asistencia')->badge(),
            ],
            default => [
                TextColumn::make('enrollment.user.name')->label('Colaborador')->searchable()->sortable(),
                TextColumn::make('enrollment.trainingSession.training.title')->label('Capacitación')->searchable(),
                TextColumn::make('enrollment.trainingSession.scheduled_at')->label('Programación')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('status')->label('Resultado')->badge(),
                TextColumn::make('score')->label('Punteo')->numeric(decimalPlaces: 2)->placeholder('Pendiente'),
                TextColumn::make('finalized_at')->label('Finalizado')->dateTime('d/m/Y H:i')->placeholder('Pendiente')->sortable(),
            ],
        };
    }

    private function recordUrl(Model $record): ?string
    {
        return match (true) {
            $record instanceof Training => TrainingResource::getUrl('edit', ['record' => $record]),
            $record instanceof TrainingSession => TrainingSessionResource::getUrl('edit', ['record' => $record]),
            $record instanceof ExamAttempt => ExamAttemptResource::getUrl('edit', ['record' => $record]),
            default => $record instanceof Enrollment
                ? TrainingSessionResource::getUrl('edit', ['record' => $record->training_session_id])
                : null,
        };
    }

    private function defaultSortColumn(): string
    {
        return match ($this->indicator) {
            'trainings' => 'created_at',
            'upcoming_sessions' => 'scheduled_at',
            'enrollments', 'attendance', 'attendance_by_agency' => 'created_at',
            default => 'submitted_at',
        };
    }

    private function defaultSortDirection(): string
    {
        return $this->indicator === 'upcoming_sessions' ? 'asc' : 'desc';
    }

    /** @return array{title: string, heading: string, description: string} */
    private function definition(): array
    {
        return match ($this->indicator) {
            'trainings' => ['title' => 'Capacitaciones', 'heading' => 'Capacitaciones', 'description' => 'Capacitaciones incluidas en el indicador del escritorio.'],
            'upcoming_sessions' => ['title' => 'Próximas programaciones', 'heading' => 'Próximas programaciones', 'description' => 'Sesiones programadas a partir de hoy.'],
            'enrollments' => ['title' => 'Colaboradores asignados', 'heading' => 'Colaboradores asignados', 'description' => 'Personas inscritas en las programaciones visibles para ti.'],
            'attendance' => ['title' => 'Asistencia registrada', 'heading' => 'Asistencia registrada', 'description' => 'Colaboradores marcados como presentes.'],
            'attendance_by_agency' => ['title' => 'Asistencia por agencia', 'heading' => 'Asistencia por agencia', 'description' => 'Detalle de asistencia utilizado en la gráfica por agencia.'],
            'diplomas' => ['title' => 'Diplomas disponibles', 'heading' => 'Diplomas disponibles', 'description' => 'Resultados aprobados, finalizados y con diploma habilitado.'],
            'pending_reviews' => ['title' => 'Evaluaciones pendientes', 'heading' => 'Evaluaciones pendientes de calificar', 'description' => 'Intentos que requieren revisión manual.'],
            'passed_attempts' => ['title' => 'Evaluaciones aprobadas', 'heading' => 'Evaluaciones aprobadas', 'description' => 'Intentos con resultado final aprobado.'],
            'failed_attempts' => ['title' => 'Evaluaciones reprobadas', 'heading' => 'Evaluaciones reprobadas', 'description' => 'Intentos con resultado final reprobado.'],
        };
    }

    private function canViewIndicator(string $indicator): bool
    {
        $user = auth()->user();

        if ($user?->isSuperAdministrator() || $user?->hasRole('administrador_capacitaciones')) {
            return true;
        }

        return $user?->hasRole('evaluador') && in_array($indicator, [
            'pending_reviews',
            'passed_attempts',
            'failed_attempts',
        ], true);
    }

    private function requestPositiveInteger(string $key): ?int
    {
        $value = request()->query($key);

        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }
}
