<?php

namespace App\Filament\Resources\CollaboratorGroups\Pages;

use App\Filament\Resources\CollaboratorGroups\CollaboratorGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCollaboratorGroups extends ListRecords
{
    protected static string $resource = CollaboratorGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
