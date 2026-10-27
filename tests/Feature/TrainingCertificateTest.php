<?php

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function certificateEnrollment(string $attemptStatus = ExamAttempt::STATUS_PASSED): Enrollment
{
    Storage::fake('public');
    Storage::disk('public')->put(
        'training-sessions/diplomas/certificate.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLh0QAAAABJRU5ErkJggg=='),
    );

    $owner = User::factory()->create();
    $student = User::factory()->create();
    $training = Training::create([
        'title' => 'Prevención de riesgos',
        'description' => 'Contenido de prueba',
        'slug' => 'prevencion-de-riesgos',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->subDay(),
        'require_exam' => true,
        'exam_enabled' => true,
        'issue_certificate' => true,
        'diploma_bg' => 'training-sessions/diplomas/certificate.png',
        'exam_expires_at' => now()->addDay(),
    ]);
    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $student->id,
        'status' => 'completado',
    ]);
    ExamAttempt::create([
        'enrollment_id' => $enrollment->id,
        'status' => $attemptStatus,
        'score' => $attemptStatus === ExamAttempt::STATUS_PASSED ? 100 : null,
        'passed' => $attemptStatus === ExamAttempt::STATUS_PASSED,
        'submitted_at' => now()->subHour(),
        'finalized_at' => $attemptStatus === ExamAttempt::STATUS_PASSED ? now() : null,
    ]);

    return $enrollment->load('user');
}

test('an approved enrollment owner can download its certificate as a PDF', function () {
    $enrollment = certificateEnrollment();

    $response = $this->actingAs($enrollment->user)
        ->get(route('user.training.certificate', $enrollment));

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->getContent())->toStartWith('%PDF');
});

test('a certificate is unavailable until the attempt has a final approved result', function () {
    $enrollment = certificateEnrollment(ExamAttempt::STATUS_PENDING_REVIEW);

    $this->actingAs($enrollment->user)
        ->get(route('user.training.certificate', $enrollment))
        ->assertNotFound();
});

test('a user cannot download another collaborator certificate', function () {
    $enrollment = certificateEnrollment();

    $this->actingAs(User::factory()->create())
        ->get(route('user.training.certificate', $enrollment))
        ->assertForbidden();
});
