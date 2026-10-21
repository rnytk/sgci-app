<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TrainingPortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', [TrainingPortalController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('my-trainings/{enrollment}', [TrainingPortalController::class, 'show'])
        ->name('training-portal.show');

    Route::get('my-trainings/{enrollment}/exam', [TrainingPortalController::class, 'examLobby'])
        ->name('training-portal.exam.lobby');

    Route::post('my-trainings/{enrollment}/exam/start', [TrainingPortalController::class, 'startExam'])
        ->name('training-portal.exam.start');

    Route::get('my-trainings/{enrollment}/exam/attempts/{attempt}', [TrainingPortalController::class, 'attempt'])
        ->name('training-portal.exam.attempt');

    Route::post('my-trainings/{enrollment}/exam/attempts/{attempt}/save', [TrainingPortalController::class, 'saveAnswers'])
        ->name('training-portal.exam.save');

    Route::post('my-trainings/{enrollment}/exam/attempts/{attempt}/submit', [TrainingPortalController::class, 'submitExam'])
        ->name('training-portal.exam.submit');

    Route::get('attendance/sessions/{trainingSession}/join', [AttendanceController::class, 'joinVirtual'])
        ->name('attendance.virtual.join');

    Route::get('attendance/check-in/{token}', [AttendanceController::class, 'checkInQr'])
        ->name('attendance.qr.check-in');
});

require __DIR__.'/auth.php';
