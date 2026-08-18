<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Service;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('code', 'DVEC')->firstOrFail();

        // Liste RH fournie. Le dernier agent n'a pas encore d'IM (colonne nullable).
        $agents = [
            ['im' => '357941', 'nom' => 'ANDRIAMALALA',    'prenom' => 'Henintsoa Manirantenaina', 'fonction' => 'Chef de service'],
            ['im' => '357965', 'nom' => 'HARENTSOA',       'prenom' => 'Rova Malala Antenaina',    'fonction' => 'Chef de service'],
            ['im' => '448079', 'nom' => 'RAKOTONIRAMBOA',  'prenom' => 'Andriniaina',              'fonction' => 'Chef de service'],
            ['im' => '492043', 'nom' => 'RATOVELO MAHERY', 'prenom' => 'Njato Ny Aina',            'fonction' => 'Chef de service'],
            ['im' => '347933', 'nom' => 'HANTANIRINA',     'prenom' => 'Tiana Herizo',             'fonction' => 'Directeur de la Communication'],
            ['im' => '385792', 'nom' => 'RANDVSON',        'prenom' => 'Sahobiarivelo Charlane',   'fonction' => 'Directeur de la Communication'],
            ['im' => '404602', 'nom' => 'RAZAFINDRAZAKA',  'prenom' => 'Fabrice Antonio',          'fonction' => 'Charge de Communication'],
            ['im' => '409259', 'nom' => 'DEWA ROSSEN',     'prenom' => 'Ilonantenaina Morenot',    'fonction' => 'Secretaire qualifie'],
            ['im' => '492034', 'nom' => 'RAKOTOARISOA',    'prenom' => 'Ando Malala',              'fonction' => 'Depositaire comptable'],
            ['im' => '492047', 'nom' => 'RAKOTONIAINA',    'prenom' => 'Sanitatra Tahiry',         'fonction' => 'Directeur de la Communication'],
            ['im' => null,     'nom' => 'NAHARITRAHY',     'prenom' => 'Aurion Basilio',           'fonction' => 'Charge de Communication'],
        ];

        foreach ($agents as $a) {
            $fonction = Fonction::where('libelle', $a['fonction'])->firstOrFail();

            Agent::updateOrCreate(
                ['nom' => $a['nom'], 'prenom' => $a['prenom']],
                [
                    'im'          => $a['im'],
                    'fonction_id' => $fonction->id,
                    'service_id'  => $service->id,
                    'actif'       => true,
                ]
            );
        }
    }
}
