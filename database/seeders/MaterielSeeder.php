<?php

namespace Database\Seeders;

use App\Enums\EtatMateriel;
use App\Models\Materiel;
use Illuminate\Database\Seeder;

class MaterielSeeder extends Seeder
{
    public function run(): void
    {
        // Inventaire fourni : gestion par quantite, pas de numero individuel.
        $materiels = [
            ['designation' => 'Roll up MIDSP',    'quantite_totale' => 2, 'description' => null],
            ['designation' => 'Light box',        'quantite_totale' => 1, 'description' => null],
            ['designation' => 'Oriflamme MIDSP',  'quantite_totale' => 2, 'description' => 'Support beton'],
            ['designation' => 'Lettrine MIDSP',   'quantite_totale' => 1, 'description' => null],
            ['designation' => 'Cubes MIDSP',      'quantite_totale' => 3, 'description' => null],
        ];

        foreach ($materiels as $m) {
            Materiel::updateOrCreate(
                ['designation' => $m['designation']],
                $m + ['etat' => EtatMateriel::Bon, 'actif' => true]
            );
        }
    }
}
