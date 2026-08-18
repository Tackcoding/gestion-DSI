<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\DemandeAbsence;
use App\Models\DroitConge;

/**
 * Calcul des soldes de conges.
 *
 * Le solde n'est JAMAIS stocke en colonne : il se recalcule a partir
 * des demandes validees. Une valeur stockee deviendrait fausse des
 * la premiere correction retroactive.
 */
class CongeService
{
    /** @return array{bloc: float, fil_de_eau: float} */
    public function soldes(Agent $agent, ?int $annee = null): array
    {
        $annee = $annee ?? (int) date('Y');

        $droits = DroitConge::firstOrNew(
            ['agent_id' => $agent->id, 'annee' => $annee]
        );

        return [
            'bloc'       => (float) $droits->jours_bloc - $this->consomme($agent, $annee, 'bloc'),
            'fil_de_eau' => (float) $droits->jours_fil_de_eau - $this->consomme($agent, $annee, 'fil_de_eau'),
        ];
    }

    private function consomme(Agent $agent, int $annee, string $quotaType): float
    {
        return (float) DemandeAbsence::query()
            ->where('agent_id', $agent->id)
            ->validees()
            ->whereYear('date_debut', $annee)
            ->whereHas('type', fn ($q) => $q->where('quota_type', $quotaType)
                                            ->where('decompte_solde', true))
            ->sum('nb_jours');
    }
}
