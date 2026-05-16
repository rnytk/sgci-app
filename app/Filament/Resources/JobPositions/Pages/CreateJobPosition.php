<?php

namespace App\Filament\Resources\JobPositions\Pages;

use App\Filament\Resources\JobPositions\JobPositionResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Resources\Pages\CreateRecord;

class CreateJobPosition extends CreateRecord
{
    use RedirectToIndex;    
    
    protected static string $resource = JobPositionResource::class;
}
