<?php

namespace App\Filament\Resources\CollaboratorGroups;

use App\Filament\Resources\CollaboratorGroups\Pages\CreateCollaboratorGroup;
use App\Filament\Resources\CollaboratorGroups\Pages\EditCollaboratorGroup;
use App\Filament\Resources\CollaboratorGroups\Pages\ListCollaboratorGroups;
use App\Filament\Resources\CollaboratorGroups\Schemas\CollaboratorGroupForm;
use App\Filament\Resources\CollaboratorGroups\Tables\CollaboratorGroupsTable;
use App\Models\CollaboratorGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CollaboratorGroupResource extends Resource
{
    protected static ?string $model = CollaboratorGroup::class;

    protected static ?string $modelLabel = 'Grupo de colaboradores';

    protected static ?string $pluralModelLabel = 'Grupos de colaboradores';

    protected static ?string $navigationLabel = 'Grupos de colaboradores';

    protected static string|UnitEnum|null $navigationGroup = 'Capacitaciones';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return CollaboratorGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollaboratorGroupsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('members');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollaboratorGroups::route('/'),
            'create' => CreateCollaboratorGroup::route('/create'),
            'edit' => EditCollaboratorGroup::route('/{record}/edit'),
        ];
    }
}
