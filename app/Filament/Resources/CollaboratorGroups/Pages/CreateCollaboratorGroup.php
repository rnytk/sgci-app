<?php

namespace App\Filament\Resources\CollaboratorGroups\Pages;

use App\Filament\Resources\CollaboratorGroups\CollaboratorGroupResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Resources\Pages\CreateRecord;

class CreateCollaboratorGroup extends CreateRecord
{
    use RedirectToIndex;

    protected static string $resource = CollaboratorGroupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
