<?php

namespace App\Filament\Resources\TrainingSessions\Pages;

use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use App\Filament\Traits\RedirectToIndex;
use App\Models\Training;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateTrainingSession extends CreateRecord
{
    use RedirectToIndex;

    protected static string $resource = TrainingSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->ensureTrainingIsVisibleToCurrentUser($data['training_id']);
        $data['trainer_id'] = auth()->id();

        return $data;
    }

    private function ensureTrainingIsVisibleToCurrentUser(int|string $trainingId): void
    {
        if (! Training::visibleTo(auth()->user())->whereKey($trainingId)->exists()) {
            throw ValidationException::withMessages([
                'training_id' => 'No puedes usar una capacitación que no te pertenece.',
            ]);
        }
    }
}
