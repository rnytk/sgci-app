<?php

use App\Http\Controllers\TrainingCertificateController;
use App\Http\Controllers\TrainingSessionCertificateArchiveController;
use App\Livewire\UserTrainingDetail;
use App\Livewire\UserTrainingExam;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('capacitaciones/{enrollment}', UserTrainingDetail::class)
    ->middleware(['auth'])
    ->name('user.training.detail');

Route::get('capacitaciones/{enrollment}/examen', UserTrainingExam::class)
    ->middleware(['auth'])
    ->name('user.training.exam');

Route::get('capacitaciones/{enrollment}/diploma', TrainingCertificateController::class)
    ->middleware(['auth'])
    ->name('user.training.certificate');

Route::get('programaciones/{trainingSession}/diplomas', TrainingSessionCertificateArchiveController::class)
    ->middleware(['auth'])
    ->name('training-sessions.certificates.download');

require __DIR__.'/auth.php';
