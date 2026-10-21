<?php

namespace App\Filament\Resources\TrainingSessions\RelationManagers;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Seleccionar Colaborador')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'enrollments',
                        column: 'user_id',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where(
                            'training_session_id',
                            $this->getOwnerRecord()->getKey(),
                        ),
                    ),
                Select::make('status')
                    ->label('Estado')
                    ->options(collect(EnrollmentStatus::cases())->mapWithKeys(
                        fn (EnrollmentStatus $status): array => [$status->value => $status->label()]
                    ))
                    ->default(EnrollmentStatus::Assigned->value)
                    ->required(),
                TextInput::make('absence_reason')
                    ->label('Motivo de ausencia')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Colaborador')
                    ->searchable(),
                TextColumn::make('user.agency.name')
                    ->label('Agencia'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof EnrollmentStatus
                        ? $state->label()
                        : (EnrollmentStatus::tryFrom((string) $state)?->label() ?? (string) $state)),
                TextColumn::make('attended_at')
                    ->label('Asistencia')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendiente'),
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

                        if (filled($data['agency_id'] ?? null)) {
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
