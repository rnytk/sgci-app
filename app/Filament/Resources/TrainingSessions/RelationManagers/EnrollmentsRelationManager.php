<?php

namespace App\Filament\Resources\TrainingSessions\RelationManagers;

use App\Models\Agency;
use App\Models\CollaboratorGroup;
use App\Models\Enrollment;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\CertificateArchiveService;
use App\Services\EnrollmentAssignmentResult;
use App\Services\EnrollmentAssignmentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        return $user !== null
            && ($user->isSuperAdministrator() || $ownerRecord->isOwnedBy($user))
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Seleccionar Colaborador')
                    ->relationship('user', 'name') // Esto hace que veas Nombres, no IDs
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('Colaborador')
                    ->searchable(),
                TextColumn::make('user.agency.name')
                    ->label('Agencia'),
                TextColumn::make('status')
                    ->label('Progreso')
                    ->badge()
                    ->color(fn (string $state): string => match (mb_strtolower($state)) {
                        'completado', 'aprobado' => 'success',
                        'en progreso', 'pendiente_calificacion' => 'warning',
                        'reprobado' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('attendance_status')
                    ->label('Asistencia')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Enrollment::ATTENDANCE_PRESENT => 'Presente',
                        Enrollment::ATTENDANCE_ABSENT => 'Ausente',
                        Enrollment::ATTENDANCE_WITHDRAWN => 'Se retiró',
                        default => 'Pendiente',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Enrollment::ATTENDANCE_PRESENT => 'success',
                        Enrollment::ATTENDANCE_ABSENT => 'danger',
                        Enrollment::ATTENDANCE_WITHDRAWN => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('attendance_justification')
                    ->label('Descripción de ausencia')
                    ->limit(50),
                TextColumn::make('attendance_justification_documents')
                    ->label('Documentos')
                    ->state(fn (Enrollment $record): string => count($record->attendance_justification_documents ?? []).' archivo(s)'),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Progreso')
                    ->options([
                        'asignado' => 'Asignado',
                        'en progreso' => 'En progreso',
                        'pendiente_calificacion' => 'Pendiente de calificación',
                        'completado' => 'Completado',
                        'aprobado' => 'Aprobado',
                        'reprobado' => 'Reprobado',
                    ]),
                SelectFilter::make('attendance_status')
                    ->label('Asistencia')
                    ->options([
                        Enrollment::ATTENDANCE_PENDING => 'Pendiente',
                        Enrollment::ATTENDANCE_PRESENT => 'Presente',
                        Enrollment::ATTENDANCE_ABSENT => 'Ausente',
                        Enrollment::ATTENDANCE_WITHDRAWN => 'Se retiró',
                    ]),
                Filter::make('agency')
                    ->label('Agencia')
                    ->schema([
                        Select::make('agency_id')
                            ->label('Agencia')
                            ->options($this->enrolledAgencyOptions())
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['agency_id'] ?? null,
                        fn (Builder $query, int $agencyId): Builder => $query->whereHas(
                            'user',
                            fn (Builder $userQuery) => $userQuery->where('agency_id', $agencyId),
                        ),
                    )),
                Filter::make('job_position')
                    ->label('Puesto')
                    ->schema([
                        Select::make('job_position_id')
                            ->label('Puesto')
                            ->options($this->enrolledJobPositionOptions())
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['job_position_id'] ?? null,
                        fn (Builder $query, int $jobPositionId): Builder => $query->whereHas(
                            'user',
                            fn (Builder $userQuery) => $userQuery->where('job_position_id', $jobPositionId),
                        ),
                    )),
            ])
            ->headerActions([
                Action::make('download_certificates')
                    ->label('Descargar diplomas aprobados')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (): string => route('training-sessions.certificates.download', $this->getOwnerRecord()))
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => $this->getOwnerRecord()->issue_certificate && filled($this->getOwnerRecord()->diploma_bg))
                    ->disabled(fn (): bool => app(CertificateArchiveService::class)
                        ->eligibleEnrollments($this->getOwnerRecord())
                        ->isEmpty())
                    ->tooltip('Solo incluye colaboradores con examen aprobado y resultado final.'),
                Action::make('assign_one')
                    ->label('Asignar uno')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        $this->singleUserSelect(),
                    ])
                    ->action(function (array $data): void {
                        $result = app(EnrollmentAssignmentService::class)->assign($this->getOwnerRecord(), [
                            'assignment_type' => EnrollmentAssignmentService::TYPE_MANUAL,
                            'user_ids' => [$data['user_id']],
                        ]);

                        $this->sendAssignmentNotification($result);
                    }),
                Action::make('assign_collaborators')
                    ->label('Asignar colaboradores')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading('Asignar colaboradores')
                    ->modalDescription('Elige un criterio y el sistema evitará duplicar inscripciones existentes.')
                    ->modalSubmitActionLabel('Asignar')
                    ->schema([
                        Select::make('assignment_type')
                            ->label('Asignar por')
                            ->options([
                                EnrollmentAssignmentService::TYPE_AGENCY => 'Agencia',
                                EnrollmentAssignmentService::TYPE_JOB_POSITION => 'Puesto',
                                EnrollmentAssignmentService::TYPE_GROUP => 'Grupo de colaboradores',
                                EnrollmentAssignmentService::TYPE_MANUAL => 'Selección manual',
                            ])
                            ->default(EnrollmentAssignmentService::TYPE_AGENCY)
                            ->required()
                            ->live(),
                        Select::make('agency_id')
                            ->label('Agencia')
                            ->options(fn (): array => Agency::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_AGENCY)
                            ->required(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_AGENCY),
                        Select::make('job_position_id')
                            ->label('Puesto')
                            ->options(fn (): array => JobPosition::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_JOB_POSITION)
                            ->required(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_JOB_POSITION),
                        Select::make('collaborator_group_id')
                            ->label('Grupo de colaboradores')
                            ->options(fn (): array => CollaboratorGroup::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_GROUP)
                            ->required(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_GROUP),
                        $this->manualUserSelect()
                            ->visible(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_MANUAL)
                            ->required(fn (Get $get): bool => $get('assignment_type') === EnrollmentAssignmentService::TYPE_MANUAL),
                    ])
                    ->action(function (array $data): void {
                        $result = app(EnrollmentAssignmentService::class)->assign($this->getOwnerRecord(), $data);

                        $this->sendAssignmentNotification($result);
                    }),

            ])
            ->recordActions([
                Action::make('record_attendance')
                    ->label('Registrar asistencia')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->successNotificationTitle('Asistencia registrada.')
                    ->fillForm(fn (Enrollment $record): array => [
                        'attendance_status' => $record->attendance_status,
                        'attendance_justification' => $record->attendance_justification,
                        'attendance_justification_documents' => $record->attendance_justification_documents,
                    ])
                    ->schema([
                        Select::make('attendance_status')
                            ->label('Asistencia')
                            ->options([
                                Enrollment::ATTENDANCE_PRESENT => 'Presente',
                                Enrollment::ATTENDANCE_ABSENT => 'Ausente',
                                Enrollment::ATTENDANCE_WITHDRAWN => 'Se retiró',
                            ])
                            ->required()
                            ->live(),
                        Textarea::make('attendance_justification')
                            ->label('Descripción de la ausencia')
                            ->required(fn (Get $get): bool => Enrollment::attendanceRequiresDescription($get('attendance_status')))
                            ->visible(fn (Get $get): bool => Enrollment::attendanceRequiresDescription($get('attendance_status')))
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        FileUpload::make('attendance_justification_documents')
                            ->label('Documentos de respaldo')
                            ->disk('public')
                            ->directory('attendance-justifications')
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->downloadable()
                            ->openable()
                            ->helperText('Opcional. Máximo 5 archivos de 10 MB: PDF, imagen o Word.')
                            ->visible(fn (Get $get): bool => Enrollment::attendanceRequiresDescription($get('attendance_status')))
                            ->columnSpanFull(),
                    ])
                    ->action(function (Enrollment $record, array $data): void {
                        /** @var User $user */
                        $user = auth()->user();

                        app(AttendanceService::class)->recordManually(
                            $record,
                            $data['attendance_status'],
                            $data['attendance_justification'] ?? null,
                            $user,
                            $data['attendance_justification_documents'] ?? [],
                        );
                    }),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function singleUserSelect(): Select
    {
        return Select::make('user_id')
            ->label('Colaborador')
            ->options($this->userOptions())
            ->searchable()
            ->required();
    }

    private function manualUserSelect(): Select
    {
        return Select::make('user_ids')
            ->label('Colaboradores')
            ->options($this->userOptions())
            ->multiple()
            ->searchable();
    }

    /**
     * @return array<int, string>
     */
    private function userOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => "{$user->name} ({$user->email})"])
            ->all();
    }

    /** @return array<int, string> */
    private function enrolledAgencyOptions(): array
    {
        return $this->getOwnerRecord()
            ->enrollments()
            ->with('user.agency')
            ->get()
            ->map(fn (Enrollment $enrollment) => $enrollment->user?->agency)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /** @return array<int, string> */
    private function enrolledJobPositionOptions(): array
    {
        return $this->getOwnerRecord()
            ->enrollments()
            ->with('user.jobPosition')
            ->get()
            ->map(fn (Enrollment $enrollment) => $enrollment->user?->jobPosition)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private function sendAssignmentNotification(EnrollmentAssignmentResult $result): void
    {
        Notification::make()
            ->title($result->created > 0 ? 'Asignación completada' : 'No se crearon inscripciones')
            ->body($result->summary())
            ->color($result->created > 0 ? 'success' : 'warning')
            ->send();
    }
}
