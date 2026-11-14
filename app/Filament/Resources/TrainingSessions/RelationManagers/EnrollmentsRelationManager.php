<?php

namespace App\Filament\Resources\TrainingSessions\RelationManagers;

use App\Models\Agency;
use App\Models\CollaboratorGroup;
use App\Models\Enrollment;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\EnrollmentAssignmentResult;
use App\Services\EnrollmentAssignmentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                        default => 'Pendiente',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Enrollment::ATTENDANCE_PRESENT => 'success',
                        Enrollment::ATTENDANCE_ABSENT => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('attendance_justification')
                    ->label('Justificación')
                    ->limit(50),
            ])

            ->filters([
                //
            ])
            ->headerActions([
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
                    ])
                    ->schema([
                        Select::make('attendance_status')
                            ->label('Asistencia')
                            ->options([
                                Enrollment::ATTENDANCE_PRESENT => 'Presente',
                                Enrollment::ATTENDANCE_ABSENT => 'Ausente',
                            ])
                            ->required()
                            ->live(),
                        Textarea::make('attendance_justification')
                            ->label('Justificación')
                            ->required(fn (Get $get): bool => $get('attendance_status') === Enrollment::ATTENDANCE_ABSENT)
                            ->visible(fn (Get $get): bool => $get('attendance_status') === Enrollment::ATTENDANCE_ABSENT)
                            ->maxLength(2000)
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

    private function sendAssignmentNotification(EnrollmentAssignmentResult $result): void
    {
        Notification::make()
            ->title($result->created > 0 ? 'Asignación completada' : 'No se crearon inscripciones')
            ->body($result->summary())
            ->color($result->created > 0 ? 'success' : 'warning')
            ->send();
    }
}
