<?php

namespace App\Filament\Resources\Exams\Pages;

use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Traits\RedirectToIndex;
use App\Models\Training;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditExam extends EditRecord
{
    use RedirectToIndex;

    protected static string $resource = ExamResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! Training::visibleTo(auth()->user())->whereKey($data['training_id'])->exists()) {
            throw ValidationException::withMessages([
                'training_id' => 'No puedes usar una capacitación que no te pertenece.',
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
