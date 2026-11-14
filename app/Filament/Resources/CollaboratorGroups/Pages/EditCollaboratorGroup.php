<?php

namespace App\Filament\Resources\CollaboratorGroups\Pages;

use App\Filament\Resources\CollaboratorGroups\CollaboratorGroupResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollaboratorGroup extends EditRecord
{
    use RedirectToIndex;

    protected static string $resource = CollaboratorGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
