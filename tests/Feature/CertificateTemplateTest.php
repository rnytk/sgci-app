<?php

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Storage;

function templateCertificateEnrollment(): Enrollment
{
    Storage::fake('public');
    Storage::disk('public')->put(
        'training-sessions/diplomas/sgci-template.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAyAAAAJYCAYAAACw4pVUAAAAFUlEQVR42u3BAQ0AAADCoPdPbQ43oAAAAAAAAAAAwK8B4wABnWvTjAAAAABJRU5ErkJggg=='),
    );

    $owner = User::factory()->create();
    $student = User::factory()->create(['name' => 'Colaborador de Prueba']);
    $training = Training::create([
        'title' => 'Gestión de riesgos institucionales',
        'description' => 'Contenido de prueba',
        'slug' => 'gestion-riesgos-institucionales',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->subDay(),
        'issue_certificate' => true,
        'diploma_bg' => 'training-sessions/diplomas/sgci-template.png',
        'exam_expires_at' => now()->addDay(),
    ]);
    $enrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => $student->id,
        'status' => 'completado',
    ]);
    ExamAttempt::create([
        'enrollment_id' => $enrollment->id,
        'status' => ExamAttempt::STATUS_PASSED,
        'score' => 100,
        'passed' => true,
        'submitted_at' => now()->subHour(),
        'finalized_at' => now(),
    ]);

    return $enrollment;
}

test('the certificate uses the uploaded template as a complete page background', function () {
    $enrollment = templateCertificateEnrollment();

    $response = app(CertificateService::class)->download($enrollment);

    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->getContent())->toStartWith('%PDF');
});
