<?php

namespace Database\Seeders;

use App\Enums\EtatMateriel;
use App\Models\Accessoire;
use App\Models\CategorieMateriel;
use App\Models\Materiel;
use Illuminate\Database\Seeder;

class MaterielSeeder extends Seeder
{
    public function run(): void
    {
        $cat = CategorieMateriel::pluck('id', 'code');

        // Categorie Visuel (supports de communication) : geres en quantite, pas d'identification
        // individuelle (aucun numero de serie, aucun accessoire).
        $supports = [
            ['designation' => 'Roll up MIDSP',   'quantite_totale' => 2],
            ['designation' => 'Light box',       'quantite_totale' => 1],
            ['designation' => 'Oriflamme MIDSP', 'quantite_totale' => 2, 'description' => 'Support béton'],
            ['designation' => 'Lettrine MIDSP',  'quantite_totale' => 1],
            ['designation' => 'Cubes MIDSP',     'quantite_totale' => 3],
        ];

        foreach ($supports as $s) {
            Materiel::updateOrCreate(
                ['designation' => $s['designation']],
                $s + [
                    'categorie_id' => $cat['COMM'],
                    'etat'         => EtatMateriel::Bon,
                    'actif'        => true,
                ]
            );
        }

        // Categorie Informatique et audiovisuel : unitaire, avec accessoires
        // constates a chaque sortie et retour.
        $equipements = [
            [
                'designation'     => 'Appareil photo numérique',
                'marque'          => 'CANON',
                'modele'          => '90D',
                'categorie'       => 'INFO',
                'accessoires'     => ['Batterie', 'Chargeur'],
            ],
            [
                'designation'     => 'Objectif',
                'marque'          => 'CANON',
                'modele'          => '18-55mm F4',
                'categorie'       => 'INFO',
                'accessoires'     => [],
            ],
            [
                'designation'     => 'Ordinateur portable',
                'marque'          => 'Asus',
                'modele'          => 'ExpertBook',
                'description'     => 'Intel Core i5 13e génération, SSD 1 To, RAM 16 Go, carte graphique 4 Go',
                'categorie'       => 'INFO',
                'accessoires'     => ['Souris sans fil', 'Chargeur'],
            ],
            [
                'designation'     => 'Ordinateur portable',
                'marque'          => 'HP',
                'modele'          => 'Omen',
                'description'     => 'Core i5, RAM 8 Go, SSD 128 Go, HDD 1 To, GTX 960',
                'categorie'       => 'INFO',
                'accessoires'     => ['Chargeur'],
            ],
        ];

        foreach ($equipements as $e) {
            $materiel = Materiel::updateOrCreate(
                ['designation' => $e['designation'], 'marque' => $e['marque'], 'modele' => $e['modele']],
                [
                    'categorie_id'    => $cat[$e['categorie']],
                    'description'     => $e['description'] ?? null,
                    'quantite_totale' => 1,
                    'etat'            => EtatMateriel::Bon,
                    'actif'           => true,
                ]
            );

            foreach ($e['accessoires'] as $libelle) {
                Accessoire::updateOrCreate(
                    ['materiel_id' => $materiel->id, 'libelle' => $libelle],
                    ['quantite' => 1, 'actif' => true]
                );
            }
        }

        // A COMPLETER : numeros de serie et codes d'inventaire,
        // a recuperer aupres du depositaire comptable.
    }
}
