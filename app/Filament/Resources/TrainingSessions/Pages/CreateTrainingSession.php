<?php

namespace App\Filament\Resources\TrainingSessions\Pages;

use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingSession extends CreateRecord
{
    use RedirectToIndex;

    protected static string $resource = TrainingSessionResource::class;
}
