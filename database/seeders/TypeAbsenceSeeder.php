<?php

namespace Database\Seeders;

use App\Models\TypeAbsence;
use Illuminate\Database\Seeder;

class TypeAbsenceSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'conge_bloc',     'libelle' => 'Conge annuel (bloc)',          'decompte_solde' => true,  'quota_type' => 'bloc',       'necessite_justificatif' => false],
            ['code' => 'conge_courant',  'libelle' => 'Conge au fil de l\'eau',        'decompte_solde' => true,  'quota_type' => 'fil_de_eau', 'necessite_justificatif' => false],
            ['code' => 'permission',     'libelle' => 'Permission',                    'decompte_solde' => false, 'quota_type' => 'aucun',      'necessite_justificatif' => false],
            ['code' => 'maladie',        'libelle' => 'Absence maladie',               'decompte_solde' => true,  'quota_type' => 'fil_de_eau', 'necessite_justificatif' => true],
            ['code' => 'formation',      'libelle' => 'Formation / mission externe',   'decompte_solde' => false, 'quota_type' => 'aucun',      'necessite_justificatif' => false],
            ['code' => 'non_justifiee',  'libelle' => 'Absence non justifiee',         'decompte_solde' => false, 'quota_type' => 'aucun',      'necessite_justificatif' => false],
        ];

        foreach ($types as $t) {
            TypeAbsence::updateOrCreate(['code' => $t['code']], $t + ['actif' => true]);
        }
    }
}
