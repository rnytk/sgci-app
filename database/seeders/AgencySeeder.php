<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Agency::create([
            'name' => 'Chimaltenango',
            'location' => '1a. Ave. 2-50, zona 4, Chimaltenango, Guatemala',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Tecpan Guatemala',
            'location' => '3a. Calle 1-50, zona 2, Tecpán Guatemala, Chimaltenango.',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'San Martín Jilotepeque',
            'location' => '4a. Ave. 7-21, Barrio el Güite, zona 4, San Martín Jilotepeque, Chimaltenango',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Patzicía',
            'location' => '2a. Calle 3-50, zona 1, Patzicía, Chimaltenango.',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Quetzaltenango',
            'location' => '4a. Calle, 14 Ave. zona 1, primer nivel Centro Comercial Plaza Polanco, Quetzaltenango.',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Santa Cruz del Quiché',
            'location' => '4a. Ave. y 8a. Calle 3-42, zona 1, Santa Cruz del Quiché.',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Joyabaj Quiché',
            'location' => 'Barrio la Libertad, Joyabaj, El Quiché.',
            'is_active' => true,
        ]);
    
    }
}
