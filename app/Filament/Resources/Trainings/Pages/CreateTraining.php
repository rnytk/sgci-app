<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Resources\Pages\CreateRecord;

class CreateTraining extends CreateRecord
{
    use RedirectToIndex;    

    protected static string $resource = TrainingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
