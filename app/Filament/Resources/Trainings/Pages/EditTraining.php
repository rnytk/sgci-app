<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTraining extends EditRecord
{
    use RedirectToIndex;

    protected static string $resource = TrainingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = $this->record->user_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
