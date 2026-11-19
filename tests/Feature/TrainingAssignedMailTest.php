<?php

use App\Mail\TrainingAssignedMail;
use App\Models\Enrollment;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('assignment email links only to the collaborator training module', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $collaborator = User::factory()->create();
    $training = Training::create([
        'title' => 'Cultura cooperativa',
        'description' => 'Contenido',
        'slug' => 'cultura-cooperativa-correo',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->addDay(),
        'exam_expires_at' => now()->addDays(2),
        'meeting_link' => 'https://meet.example.test/reunion-privada',
    ]);
    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $collaborator->id,
    ]);

    $mail = null;

    Mail::assertQueued(TrainingAssignedMail::class, function (TrainingAssignedMail $queuedMail) use (&$mail, $enrollment): bool {
        $mail = $queuedMail;

        return $queuedMail->enrollment->is($enrollment);
    });

    expect($mail)->not->toBeNull()
        ->and($mail->trainingUrl)->toBe(route('user.training.detail', ['enrollment' => $enrollment]));

    $html = $mail->render();

    expect($html)
        ->toContain('Ver mi capacitación')
        ->toContain($mail->trainingUrl)
        ->not->toContain($session->meeting_link)
        ->not->toContain('Enlace de la Reunión');
});
