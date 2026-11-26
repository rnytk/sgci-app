<?php

use App\Filament\Resources\Exams\Pages\CreateExam;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use Database\Seeders\SgciRolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Livewire\Livewire;

function criticalUniquenessEnrollment(): Enrollment
{
    $owner = User::factory()->create();
    $training = Training::create([
        'title' => 'Capacitación de integridad',
        'description' => 'Contenido de prueba',
        'slug' => 'capacitacion-de-integridad',
        'user_id' => $owner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now(),
        'exam_expires_at' => now()->addDay(),
    ]);

    return Enrollment::create([
        'training_session_id' => $session->id,
        'user_id' => User::factory()->create()->id,
    ]);
}

test('a training can only have one exam', function () {
    $enrollment = criticalUniquenessEnrollment();

    Exam::create([
        'training_id' => $enrollment->trainingSession->training_id,
        'passing_score' => 70,
        'time_limit' => 60,
    ]);

    expect(fn () => Exam::create([
        'training_id' => $enrollment->trainingSession->training_id,
        'passing_score' => 80,
        'time_limit' => 90,
    ]))->toThrow(QueryException::class);
});

test('the exam form shows a clear error when the selected training already has an exam', function () {
    $enrollment = criticalUniquenessEnrollment();
    $trainingId = $enrollment->trainingSession->training_id;
    $owner = $enrollment->trainingSession->trainer;
    $this->seed(SgciRolesSeeder::class);
    $owner->assignRole('super_administrador');
    Exam::create([
        'training_id' => $trainingId,
        'passing_score' => 70,
        'time_limit' => 60,
    ]);

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($owner)
        ->test(CreateExam::class)
        ->fillForm([
            'training_id' => $trainingId,
            'passing_score' => 70,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'training_id' => 'unique',
        ]);
});

test('an enrollment can only have one exam attempt', function () {
    $enrollment = criticalUniquenessEnrollment();

    ExamAttempt::create([
        'enrollment_id' => $enrollment->id,
        'status' => ExamAttempt::STATUS_SUBMITTED,
    ]);

    expect(fn () => ExamAttempt::create([
        'enrollment_id' => $enrollment->id,
        'status' => ExamAttempt::STATUS_SUBMITTED,
    ]))->toThrow(QueryException::class);
});
