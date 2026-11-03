<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SgciRolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Filament::setCurrentPanel('admin');

        $permissions = collect(FilamentShield::getEntitiesPermissions())
            ->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        $superAdmin = Role::findOrCreate('super_administrador', 'web');
        $administrator = Role::findOrCreate('administrador_capacitaciones', 'web');
        $evaluator = Role::findOrCreate('evaluador', 'web');
        Role::findOrCreate('colaborador', 'web');

        $superAdmin->syncPermissions($permissions);

        $administratorResources = [
            'Agency',
            'JobPosition',
            'Training',
            'TrainingSession',
            'Exam',
        ];

        $administratorPermissions = $permissions->filter(
            fn (string $permission) => in_array(Str::afterLast($permission, ':'), $administratorResources, true)
                || $permission === 'View:Dashboard'
                || $permission === 'Review:ExamAttempt',
        );
        $administrator->syncPermissions($administratorPermissions);

        $evaluator->syncPermissions($permissions->filter(
            fn (string $permission) => $permission === 'Review:ExamAttempt',
        ));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
