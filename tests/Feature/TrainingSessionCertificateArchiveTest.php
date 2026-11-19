<?php

use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CertificateArchiveService;
use Database\Seeders\SgciRolesSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(SgciRolesSeeder::class);
    Storage::fake('public');
    Storage::disk('public')->put(
        'training-sessions/diplomas/archive-template.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLh0QAAAABJRU5ErkJggg=='),
    );
});

function certificateArchiveContext(): array
{
    $owner = User::factory()->create();
    $owner->assignRole('administrador_capacitaciones');
    $training = Training::create([
        'title' => 'Atención al asociado',
        'description' => 'Contenido de prueba',
        'slug' => 'atencion-al-asociado',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now()->subDay(),
        'issue_certificate' => true,
        'diploma_bg' => 'training-sessions/diplomas/archive-template.png',
        'exam_expires_at' => now()->addDay(),
    ]);

    $passedEnrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => User::factory()->create(['name' => 'Ana Aprobada'])->id,
        'status' => 'completado',
    ]);
    ExamAttempt::create([
        'enrollment_id' => $passedEnrollment->id,
        'status' => ExamAttempt::STATUS_PASSED,
        'score' => 100,
        'passed' => true,
        'submitted_at' => now()->subHour(),
        'finalized_at' => now(),
    ]);

    $pendingEnrollment = Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => User::factory()->create(['name' => 'Pedro Pendiente'])->id,
        'status' => 'en progreso',
    ]);
    ExamAttempt::create([
        'enrollment_id' => $pendingEnrollment->id,
        'status' => ExamAttempt::STATUS_PENDING_REVIEW,
        'submitted_at' => now()->subHour(),
    ]);

    return compact('owner', 'session', 'passedEnrollment', 'pendingEnrollment');
}

test('the bulk download contains only final approved certificates', function () {
    ['session' => $session, 'passedEnrollment' => $passedEnrollment] = certificateArchiveContext();

    $response = app(CertificateArchiveService::class)->download($session);

    ob_start();
    $response->sendContent();
    $archiveContents = ob_get_clean();
    $archivePath = tempnam(sys_get_temp_dir(), 'sgci-test-');
    file_put_contents($archivePath, $archiveContents);

    $archive = new ZipArchive;
    expect($response->headers->get('content-type'))->toContain('application/zip')
        ->and($archive->open($archivePath))->toBeTrue()
        ->and($archive->numFiles)->toBe(1)
        ->and($archive->getNameIndex(0))->toBe("diploma-atencion-al-asociado-{$passedEnrollment->id}.pdf")
        ->and($archive->getFromIndex(0))->toStartWith('%PDF');

    $archive->close();
    unlink($archivePath);
});

test('only the programming owner or super administrator can request the bulk download', function () {
    ['owner' => $owner, 'session' => $session] = certificateArchiveContext();
    $otherAdministrator = User::factory()->create();
    $otherAdministrator->assignRole('administrador_capacitaciones');

    $this->actingAs($owner)
        ->get(route('training-sessions.certificates.download', $session))
        ->assertOk()
        ->assertHeader('content-type', 'application/zip');

    $this->actingAs($otherAdministrator)
        ->get(route('training-sessions.certificates.download', $session))
        ->assertForbidden();
});
