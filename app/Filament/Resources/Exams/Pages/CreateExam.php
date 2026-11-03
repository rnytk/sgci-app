<?php

namespace App\Filament\Resources\Exams\Pages;

use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Traits\RedirectToIndex;
use App\Models\Training;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateExam extends CreateRecord
{

    use RedirectToIndex;

    protected static string $resource = ExamResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! Training::visibleTo(auth()->user())->whereKey($data['training_id'])->exists()) {
            throw ValidationException::withMessages([
                'training_id' => 'No puedes usar una capacitación que no te pertenece.',
            ]);
        }

        return $data;
    }
}
