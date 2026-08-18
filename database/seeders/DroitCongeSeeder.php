<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\DroitConge;
use Illuminate\Database\Seeder;

class DroitCongeSeeder extends Seeder
{
    public function run(): void
    {
        $annee = (int) date('Y');

        // A CONFIRMER : le 15 + 15 est-il uniforme, ou variable selon
        // l'anciennete ou le statut administratif ?
        foreach (Agent::all() as $agent) {
            DroitConge::updateOrCreate(
                ['agent_id' => $agent->id, 'annee' => $annee],
                ['jours_bloc' => 15.0, 'jours_fil_de_eau' => 15.0]
            );
        }
    }
}
