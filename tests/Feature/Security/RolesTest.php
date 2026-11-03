<?php

use App\Models\User;
use Database\Seeders\SgciRolesSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->seed(SgciRolesSeeder::class);
});

test('only administrative roles can access the Filament panel', function () {
    $panel = Filament::getPanel('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_administrador');

    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluador');

    $collaborator = User::factory()->create();
    $collaborator->assignRole('colaborador');

    expect($superAdmin->canAccessPanel($panel))->toBeTrue()
        ->and($evaluator->canAccessPanel($panel))->toBeTrue()
        ->and($collaborator->canAccessPanel($panel))->toBeFalse();
});

test('inactive administrative users cannot access the Filament panel', function () {
    $user = User::factory()->create(['is_active' => false]);
    $user->assignRole('administrador_capacitaciones');

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

test('administrators, evaluators and super administrators receive the manual grading permission', function () {
    $administrator = User::factory()->create();
    $administrator->assignRole('administrador_capacitaciones');

    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluador');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_administrador');

    expect($administrator->can('Review:ExamAttempt'))->toBeTrue()
        ->and($evaluator->can('Review:ExamAttempt'))->toBeTrue()
        ->and($superAdmin->can('Review:ExamAttempt'))->toBeTrue();
});
