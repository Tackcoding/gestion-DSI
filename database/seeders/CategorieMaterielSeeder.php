<?php

namespace Database\Seeders;

use App\Models\CategorieMateriel;
use Illuminate\Database\Seeder;

class CategorieMaterielSeeder extends Seeder
{
    public function run(): void
    {
        // Deux categories, a la demande du directeur. Le code COMM porte la
        // regle du pret a une autre direction (ReservationService).
        $categories = [
            ['code' => 'COMM', 'libelle' => 'Visuel'],
            ['code' => 'INFO', 'libelle' => 'Informatique et audiovisuel'],
        ];

        foreach ($categories as $c) {
            CategorieMateriel::updateOrCreate(['code' => $c['code']], $c + ['actif' => true]);
        }
    }
}
