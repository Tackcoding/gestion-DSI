<?php

namespace Database\Seeders;

use App\Models\CategorieMateriel;
use Illuminate\Database\Seeder;

class CategorieMaterielSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'COMM',  'libelle' => 'Support de communication'],
            ['code' => 'AUDIO', 'libelle' => 'Materiel audiovisuel'],
            ['code' => 'INFO',  'libelle' => 'Materiel informatique'],
        ];

        foreach ($categories as $c) {
            CategorieMateriel::updateOrCreate(['code' => $c['code']], $c + ['actif' => true]);
        }
    }
}
