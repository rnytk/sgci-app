<?php

namespace App\Filament\Resources\TrainingSessions\RelationManagers;

use App\Models\Enrollment;
use App\Models\User;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                CreateAction::make()->label('Asignar uno'),
                /* AssociateAction::make() */

                Action::make('bulk_assign')
                    ->label('Asignación Masiva')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->form([
                        Select::make('agency_id')
                            ->label('Filtrar por Agencia')
                            ->relationship('user.agency', 'name')
                            ->placeholder('Todas las agencias'),
                    ])
                    ->action(function (array $data) {
                        $sessionId = $this->getOwnerRecord()->id;
                        $query = User::query();

                        if ($data['agency_id']) {
                            $query->where('agency_id', $data['agency_id']);
                        }

                        $users = $query->get();
                        foreach ($users as $user) {
                            Enrollment::firstOrCreate([
                                'training_session_id' => $sessionId,
                                'user_id' => $user->id,
                            ]);
                        }
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
}
