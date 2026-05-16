<?php

namespace App\Filament\Resources\Exams\Pages;

use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Traits\RedirectToIndex;
use Filament\Resources\Pages\CreateRecord;

class CreateExam extends CreateRecord
{

    use RedirectToIndex;

    protected static string $resource = ExamResource::class;
}
