<?php

namespace App\Filament\Resources\TrainingSessions\Pages;

use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use App\Filament\Traits\RedirectToIndex;
use App\Models\Training;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTrainingSession extends EditRecord
{
    use RedirectToIndex;    

    protected static string $resource = TrainingSessionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! Training::visibleTo(auth()->user())->whereKey($data['training_id'])->exists()) {
            throw ValidationException::withMessages([
                'training_id' => 'No puedes usar una capacitación que no te pertenece.',
            ]);
        }

        $data['trainer_id'] = $this->record->trainer_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
