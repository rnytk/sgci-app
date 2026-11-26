<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class SuperAdministratorSeeder extends Seeder
{
    public function run(): void
    {
        $administrator = config('sgci.initial_super_administrator');
        $email = $administrator['email'] ?? null;
        $password = $administrator['password'] ?? null;

        if (blank($email) || blank($password)) {
            throw new InvalidArgumentException(
                'Define SGCI_ADMIN_EMAIL y SGCI_ADMIN_PASSWORD antes de ejecutar los seeders.',
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('SGCI_ADMIN_EMAIL debe contener un correo electrónico válido.');
        }

        User::withoutEvents(function () use ($administrator, $email, $password): void {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $administrator['name'] ?: 'Administrador SGCI',
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->assignRole('super_administrador');
        });
    }
}
