<?php

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Training;
use App\Models\TrainingSession;
use App\Models\User;
use App\Filament\Resources\ExamAttempts\ExamAttemptResource;
use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Resources\TrainingSessions\TrainingSessionResource;
use Database\Seeders\SgciRolesSeeder;

beforeEach(function () {
    $this->seed(SgciRolesSeeder::class);
});

function ownershipScopeContext(): array
{
    $owner = User::factory()->create();
    $otherOwner = User::factory()->create();
    $student = User::factory()->create();
    $otherStudent = User::factory()->create();

    $training = Training::create([
        'title' => 'Capacitación propia',
        'description' => 'Contenido',
        'slug' => 'capacitacion-propia',
        'user_id' => $owner->id,
    ]);
    $otherTraining = Training::create([
        'title' => 'Capacitación ajena',
        'description' => 'Contenido',
        'slug' => 'capacitacion-ajena',
        'user_id' => $otherOwner->id,
    ]);
    $session = TrainingSession::create([
        'training_id' => $training->id,
        'trainer_id' => $owner->id,
        'scheduled_at' => now(),
        'exam_expires_at' => now()->addDay(),
    ]);
    $otherSession = TrainingSession::create([
        'training_id' => $otherTraining->id,
        'trainer_id' => $otherOwner->id,
        'scheduled_at' => now(),
        'exam_expires_at' => now()->addDay(),
    ]);
    $exam = Exam::create(['training_id' => $training->id, 'passing_score' => 70]);
    $otherExam = Exam::create(['training_id' => $otherTraining->id, 'passing_score' => 70]);
    $enrollment = Enrollment::create(['training_session_id' => $session->id, 'user_id' => $student->id]);
    $otherEnrollment = Enrollment::create(['training_session_id' => $otherSession->id, 'user_id' => $otherStudent->id]);
    $attempt = ExamAttempt::create(['enrollment_id' => $enrollment->id, 'status' => ExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);
    $otherAttempt = ExamAttempt::create(['enrollment_id' => $otherEnrollment->id, 'status' => ExamAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

    return compact('owner', 'otherOwner', 'student', 'otherStudent', 'training', 'otherTraining', 'session', 'otherSession', 'exam', 'otherExam', 'attempt', 'otherAttempt');
}

test('owner visibility scopes restrict trainings, sessions, exams and enrollments to their creator', function () {
    ['owner' => $owner, 'training' => $training, 'session' => $session, 'exam' => $exam, 'otherTraining' => $otherTraining, 'otherSession' => $otherSession, 'otherExam' => $otherExam] = ownershipScopeContext();

    expect(Training::visibleTo($owner)->pluck('id')->all())->toBe([$training->id])
        ->and(TrainingSession::visibleTo($owner)->pluck('id')->all())->toBe([$session->id])
        ->and(Exam::visibleTo($owner)->pluck('id')->all())->toBe([$exam->id])
        ->and(Training::visibleTo($owner)->pluck('id')->all())->not->toContain($otherTraining->id)
        ->and(TrainingSession::visibleTo($owner)->pluck('id')->all())->not->toContain($otherSession->id)
        ->and(Exam::visibleTo($owner)->pluck('id')->all())->not->toContain($otherExam->id);
});

test('super administrator sees all records and evaluator sees all exam attempts', function () {
    ['student' => $student, 'attempt' => $attempt, 'otherAttempt' => $otherAttempt] = ownershipScopeContext();

    $superAdministrator = User::factory()->create();
    $superAdministrator->assignRole('super_administrador');

    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluador');

    expect(Training::visibleTo($superAdministrator)->count())->toBe(2)
        ->and(ExamAttempt::visibleTo($superAdministrator)->count())->toBe(2)
        ->and(ExamAttempt::visibleTo($evaluator)->count())->toBe(2)
        ->and(ExamAttempt::visibleTo($student)->pluck('id')->all())->toBe([$attempt->id])
        ->and(ExamAttempt::visibleTo($student)->pluck('id')->all())->not->toContain($otherAttempt->id);
});

test('training administrator sees attempts only from trainings created by that administrator', function () {
    ['owner' => $owner, 'attempt' => $attempt, 'otherAttempt' => $otherAttempt] = ownershipScopeContext();

    $owner->assignRole('administrador_capacitaciones');

    expect(ExamAttempt::visibleTo($owner)->pluck('id')->all())->toBe([$attempt->id])
        ->and(ExamAttempt::visibleTo($owner)->pluck('id')->all())->not->toContain($otherAttempt->id);
});

test('filament resource queries apply record visibility before listing or searching', function () {
    ['owner' => $owner, 'student' => $student, 'training' => $training, 'session' => $session, 'exam' => $exam, 'attempt' => $attempt] = ownershipScopeContext();

    $this->actingAs($owner);

    expect(TrainingResource::getEloquentQuery()->pluck('id')->all())->toBe([$training->id])
        ->and(TrainingSessionResource::getEloquentQuery()->pluck('id')->all())->toBe([$session->id])
        ->and(ExamResource::getEloquentQuery()->pluck('id')->all())->toBe([$exam->id]);

    $this->actingAs($student);

    expect(ExamAttemptResource::getEloquentQuery()->pluck('id')->all())->toBe([$attempt->id]);

    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluador');
    $this->actingAs($evaluator);

    expect(ExamAttemptResource::getEloquentQuery()->count())->toBe(2);
});

test('training session enrollment count is available for the scheduling table', function () {
    ['session' => $session] = ownershipScopeContext();

    expect(TrainingSession::query()->withCount('enrollments')->findOrFail($session->id)->enrollments_count)
        ->toBe(1);
});
