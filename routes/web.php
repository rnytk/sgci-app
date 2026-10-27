<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\UserTrainingDetail;
use App\Livewire\UserTrainingExam;
use App\Http\Controllers\TrainingCertificateController;

Route::view('/', 'welcome');

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

require __DIR__.'/auth.php';
