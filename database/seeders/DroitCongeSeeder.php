<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\DroitConge;
use App\Models\TypeAbsence;
use Illuminate\Database\Seeder;

class DroitCongeSeeder extends Seeder
{
    public function run(): void
    {
        $annee = (int) date('Y');

        // Un droit par agent et par type a quota.
        $typesAvecQuota = TypeAbsence::whereNotNull('quota_annuel')->get();

        foreach (Agent::all() as $agent) {
            foreach ($typesAvecQuota as $type) {
                DroitConge::updateOrCreate(
                    ['agent_id' => $agent->id, 'annee' => $annee, 'type_id' => $type->id],
                    ['jours_accordes' => $type->quota_annuel]
                );
            }
        }
    }
}
