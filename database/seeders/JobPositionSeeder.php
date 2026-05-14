<?php

namespace Database\Seeders;
use App\Models\JobPosition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PHPUnit\Util\PHP\Job;

class JobPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JobPosition::create([
            'name' => 'Gerente General',
            'description' => 'Gerente General.',
        ]);

        JobPosition::create([
            'name' => 'Gerente de Operaciones',
            'description' => 'Gerente de Operaciones.',
        ]);

        JobPosition::create([
            'name' => 'Gerente de Administración y Finanzas',
            'description' => 'Gerente de Administración y Finanzas.',
        ]);

        JobPosition::create([
            'name' => 'Gerente de Recursos Humanos',
            'description' => 'Gerente de Recursos Humanos.',
        ]);
    }
}
