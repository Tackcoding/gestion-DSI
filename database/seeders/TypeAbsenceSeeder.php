<?php

namespace Database\Seeders;

use App\Models\TypeAbsence;
use Illuminate\Database\Seeder;

class TypeAbsenceSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'conge_annuel',
                'libelle' => 'Congé annuel',
                'decompte_solde' => true,
                'quota_annuel' => 30.0,
                'duree_max_par_demande' => null,
                'necessite_justificatif' => false,
            ],
            [
                'code' => 'permission',
                'libelle' => 'Permission',
                'decompte_solde' => true,
                'quota_annuel' => 30.0,
                'duree_max_par_demande' => 2.0,   // regle propre a la direction
                'necessite_justificatif' => false,
            ],
            [
                'code' => 'maladie',
                'libelle' => 'Absence maladie',
                'decompte_solde' => false,
                'quota_annuel' => null,
                'duree_max_par_demande' => null,
                'necessite_justificatif' => true,
            ],
            [
                'code' => 'formation',
                'libelle' => 'Formation / mission externe',
                'decompte_solde' => false,
                'quota_annuel' => null,
                'duree_max_par_demande' => null,
                'necessite_justificatif' => false,
            ],
            [
                'code' => 'non_justifiee',
                'libelle' => 'Absence non justifiée',
                'decompte_solde' => false,
                'quota_annuel' => null,
                'duree_max_par_demande' => null,
                'necessite_justificatif' => false,
            ],
        ];

        foreach ($types as $t) {
            TypeAbsence::updateOrCreate(['code' => $t['code']], $t + ['actif' => true]);
        }

        // Les anciens types du decoupage 15+15 n'existent plus.
        TypeAbsence::whereIn('code', ['conge_bloc', 'conge_courant'])->delete();
    }
}
