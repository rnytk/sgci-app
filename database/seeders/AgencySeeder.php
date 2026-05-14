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
            'name' => 'Agencia Quetzaltenango',
            'location' => 'Quetzaltenango, Guatemala',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Agencia Tecpan Guatemala',
            'location' => 'Tecpan Guatemala, Chimaltenango',
            'is_active' => true,
        ]);

        Agency::create([
            'name' => 'Totonicapan',
            'location' => 'Totonicapan, Guatemala',
            'is_active' => true,
        ]);
    
    }
}
