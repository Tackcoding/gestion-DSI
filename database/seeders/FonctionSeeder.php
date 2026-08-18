<?php

namespace Database\Seeders;

use App\Models\Fonction;
use Illuminate\Database\Seeder;

class FonctionSeeder extends Seeder
{
    public function run(): void
    {
        // Fonctions relevees dans la liste RH fournie.
        $fonctions = [
            'Chef de service',
            'Directeur de la Communication',
            'Charge de Communication',
            'Secretaire qualifie',
            'Depositaire comptable',
        ];

        foreach ($fonctions as $libelle) {
            Fonction::updateOrCreate(['libelle' => $libelle]);
        }
    }
}
