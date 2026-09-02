<?php

namespace App\Services;

use App\Enums\EtatMateriel;
use App\Enums\StatutReservation;
use App\Models\Mouvement;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Sorties et retours physiques de materiel.
 *
 * A distinguer de la reservation, qui n'est qu'une intention.
 * Ce service enregistre ce qui est REELLEMENT sorti et revenu.
 */
class MouvementService
{
    /**
     * Enregistre une sortie.
     *
     * @param array<int, array{present: bool, etat: ?string, observation: ?string}> $accessoires
     *        indexe par id d'accessoire
     */
    public function enregistrerSortie(
        Reservation $reservation,
        int $quantite,
        int $agentId,
        array $accessoires = [],
        ?string $observation = null,
    ): Mouvement {
        if ($reservation->statut !== StatutReservation::Validee) {
            throw new RuntimeException(
                'Seule une reservation validee peut donner lieu a une sortie.'
            );
        }

        $dejaSortie = $reservation->quantiteSortie();
        $restant    = $reservation->quantite - $dejaSortie;

        if ($quantite > $restant) {
            throw new RuntimeException(
                "Quantite trop elevee : {$restant} exemplaire(s) restant(s) a sortir."
            );
        }

        return DB::transaction(function () use ($reservation, $quantite, $agentId, $accessoires, $observation) {
            $mouvement = Mouvement::create([
                'reservation_id' => $reservation->id,
                'type'           => \App\Enums\TypeMouvement::Sortie,
                'quantite'       => $quantite,
                'date_mouvement' => now(),
                'agent_id'       => $agentId,
                'observation'    => $observation,
            ]);

            $this->attacherAccessoires($mouvement, $accessoires);

            return $mouvement;
        });
    }

    /**
     * Enregistre un retour et met a jour l'etat du materiel si besoin.
     */
    public function enregistrerRetour(
        Reservation $reservation,
        int $quantite,
        int $agentId,
        ?string $etatConstate = null,
        array $accessoires = [],
        ?string $observation = null,
    ): Mouvement {
        $enCirculation = $reservation->quantiteEnCirculation();

        if ($enCirculation <= 0) {
            throw new RuntimeException(
                'Aucun exemplaire en circulation sur cette reservation.'
            );
        }

        if ($quantite > $enCirculation) {
            throw new RuntimeException(
                "Quantite trop elevee : {$enCirculation} exemplaire(s) en circulation."
            );
        }

        return DB::transaction(function () use (
            $reservation, $quantite, $agentId, $etatConstate, $accessoires, $observation
        ) {
            $mouvement = Mouvement::create([
                'reservation_id' => $reservation->id,
                'type'           => \App\Enums\TypeMouvement::Retour,
                'quantite'       => $quantite,
                'date_mouvement' => now(),
                'agent_id'       => $agentId,
                'etat_constate'  => $etatConstate,
                'observation'    => $observation,
            ]);

            $this->attacherAccessoires($mouvement, $accessoires);

            // Un materiel rendu hors service sort du stock disponible.
            if ($etatConstate === EtatMateriel::HorsService->value) {
                $reservation->materiel->update(['etat' => EtatMateriel::HorsService]);
            }

            return $mouvement;
        });
    }

    /** @param array<int, array> $accessoires */
    private function attacherAccessoires(Mouvement $mouvement, array $accessoires): void
    {
        foreach ($accessoires as $accessoireId => $constat) {
            $mouvement->accessoires()->attach($accessoireId, [
                'present'       => (bool) ($constat['present'] ?? false),
                'etat_constate' => $constat['etat'] ?? null,
                'observation'   => $constat['observation'] ?? null,
            ]);
        }
    }
}
