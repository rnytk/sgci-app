<?php

namespace App\Filament\Resources\ExamAttempts\Pages;

use App\Filament\Resources\ExamAttempts\ExamAttemptResource;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExamAttempt extends EditRecord
{
    protected static string $resource = ExamAttemptResource::class;

    protected function afterSave(): void
    {
        $this->record->load('answers.question');

        foreach ($this->record->answers as $answer) {
            if ($answer->question->type === 'open' && ! is_null($answer->awarded_points)) {
                app(ExamAttemptService::class)->gradeOpenAnswer(
                    $answer,
                    (float) $answer->awarded_points,
                    auth()->user(),
                );
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('finalize')
                ->label('Finalizar calificación')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === ExamAttempt::STATUS_PENDING_REVIEW)
                ->action(function (): void {
                    try {
                        $attempt = app(ExamAttemptService::class)->finalize($this->record, auth()->user());

                        Notification::make()
                            ->success()
                            ->title($attempt->passed ? 'Intento aprobado' : 'Intento reprobado')
                            ->body('El resultado final fue calculado y registrado.')
                            ->send();

                        $this->redirect(ExamAttemptResource::getUrl('index'));
                    } catch (DomainException $exception) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede finalizar')
                            ->body($exception->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
