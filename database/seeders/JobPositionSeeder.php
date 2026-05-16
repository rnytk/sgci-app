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
            'name' => 'Gerente Administrativo y Financiero',
            'description' => 'Gerente Administrativo y Financiero .',
        ]);

        JobPosition::create([
            'name' => 'Director de Operaciones y Desarrollo de Agencias ',
            'description' => 'Director de Operaciones y Desarrollo de Agencias ',
        ]);

        JobPosition::create([
            'name' => 'Gerente de Agencias y Sucursales ',
            'description' => 'Gerente de Agencias y Sucursales ',
        ]);

        JobPosition::create([
            'name' => 'Director de Sistemas y Soporte Tecnológico ',
            'description' => 'Director de Sistemas y Soporte Tecnológico ',
        ]);

        JobPosition::create([
            'name' => 'Directora de Desarrollo Cooperativo y Comunitario ',
            'description' => 'Directora de Desarrollo Cooperativo y Comunitario ',
        ]);
    }
}
