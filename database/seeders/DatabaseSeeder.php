<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ordre impose par les dependances entre tables.
        $this->call([
            ServiceSeeder::class,
            FonctionSeeder::class,
            AgentSeeder::class,
            MaterielSeeder::class,
            TypeAbsenceSeeder::class,
            DroitCongeSeeder::class,
        ]);
    }
}
