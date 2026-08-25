<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ordre impose par les dependances entre tables.
        $this->call([
            UserSeeder::class,
            ServiceSeeder::class,
            FonctionSeeder::class,
            AgentSeeder::class,
            CategorieMaterielSeeder::class,
            MaterielSeeder::class,
            TypeAbsenceSeeder::class,
            DroitCongeSeeder::class,
        ]);
    }
}
