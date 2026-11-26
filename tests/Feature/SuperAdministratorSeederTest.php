<?php

use App\Models\User;
use Database\Seeders\SgciRolesSeeder;
use Database\Seeders\SuperAdministratorSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    config()->set('sgci.initial_super_administrator', [
        'name' => 'Administrador Inicial',
        'email' => 'admin-inicial@example.test',
        'password' => 'una-clave-inicial-segura',
    ]);

    $this->seed(SgciRolesSeeder::class);
});

test('creates the configured super administrator and assigns its role', function () {
    $this->seed(SuperAdministratorSeeder::class);

    $administrator = User::query()
        ->where('email', 'admin-inicial@example.test')
        ->sole();

    expect($administrator->name)->toBe('Administrador Inicial')
        ->and($administrator->is_active)->toBeTrue()
        ->and($administrator->email_verified_at)->not->toBeNull()
        ->and(Hash::check('una-clave-inicial-segura', $administrator->password))->toBeTrue()
        ->and($administrator->hasRole('super_administrador'))->toBeTrue();
});

test('does not duplicate or overwrite an existing administrator account', function () {
    $administrator = User::factory()->create([
        'name' => 'Cuenta Existente',
        'email' => 'admin-inicial@example.test',
        'password' => 'clave-original',
    ]);
    $administrator->assignRole('colaborador');

    $this->seed(SuperAdministratorSeeder::class);

    expect(User::query()->where('email', 'admin-inicial@example.test')->count())->toBe(1)
        ->and($administrator->fresh()->name)->toBe('Cuenta Existente')
        ->and(Hash::check('clave-original', $administrator->fresh()->password))->toBeTrue()
        ->and($administrator->fresh()->hasRole('colaborador'))->toBeTrue()
        ->and($administrator->fresh()->hasRole('super_administrador'))->toBeTrue();
});

test('requires administrator credentials in the environment configuration', function () {
    config()->set('sgci.initial_super_administrator.email', null);

    expect(fn () => $this->seed(SuperAdministratorSeeder::class))
        ->toThrow(InvalidArgumentException::class, 'SGCI_ADMIN_EMAIL');
});
