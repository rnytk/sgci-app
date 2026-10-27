<?php

namespace App\Filament\Resources\TrainingSessions\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use App\Models\User;
use App\Models\Enrollment;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Forms\Components\Select;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

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
            TextColumn::make('user.name')
                ->label('Colaborador')
                ->searchable(),
           TextColumn::make('user.agency.name')
                ->label('Agencia'),
        ])

           
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Asignar uno'),
             /*AssociateAction::make()*/    

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
                })


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
