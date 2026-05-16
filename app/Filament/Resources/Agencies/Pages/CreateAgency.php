<?php

namespace App\Filament\Resources\Agencies\Pages;

use App\Filament\Resources\Agencies\AgencyResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Resources\Pages\CreateRecord;

class CreateAgency extends CreateRecord
{
    use RedirectToIndex;

    protected static string $resource = AgencyResource::class;
}
