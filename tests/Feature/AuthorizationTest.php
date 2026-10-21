<?php

use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('super_admin', 'web');
    Role::findOrCreate('Colaborador', 'web');
});

test('only operational roles can access the Filament panel', function () {
    $collaborator = User::factory()->create();
    $collaborator->assignRole('Colaborador');

    $this->actingAs($collaborator)
        ->get('/admin')
        ->assertForbidden();
});

test('an active administrator can access the Filament panel', function () {
    $administrator = User::factory()->create();
    $administrator->assignRole('super_admin');

    expect($administrator->is_active)->toBeTrue()
        ->and($administrator->hasRole('super_admin'))->toBeTrue()
        ->and($administrator->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();

    $this->actingAs($administrator)
        ->get('/admin')
        ->assertOk();
});

test('inactive users cannot access the Filament panel even with a role', function () {
    $administrator = User::factory()->create(['is_active' => false]);
    $administrator->assignRole('super_admin');

    $this->actingAs($administrator)
        ->get('/admin')
        ->assertForbidden();
});
