<?php

namespace App\Services;

use App\Enums\StatutReservation;
use App\Models\Materiel;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Logique metier des reservations de materiel.
 *
 * Placee dans un service et non dans un composant Livewire :
 * si une API mobile est ajoutee plus tard, elle appelle ce meme code.
 */
class ReservationService
{
    /** Code de la categorie autorisee au pret externe. */
    private const CATEGORIE_PRETABLE = 'COMM';

    /** Quantite encore disponible pour un materiel sur une periode. */
    public function quantiteDisponible(Materiel $materiel, string $debut, string $fin): int
    {
        $engagee = Reservation::query()
            ->where('materiel_id', $materiel->id)
            ->validees()
            ->chevauchant($debut, $fin)
            ->sum('quantite');

        return max(0, $materiel->quantite_totale - (int) $engagee);
    }

    /**
     * Seuls les supports de communication sortent de la direction.
     * Le materiel audiovisuel et informatique reste a la DVEC : il est
     * identifie individuellement et sa perte engage une procedure formelle.
     */
    public function estPretable(Materiel $materiel): bool
    {
        return $materiel->categorie?->code === self::CATEGORIE_PRETABLE;
    }

    /**
     * Cree une reservation en verifiant le stock.
     *
     * MySQL ne sait pas empecher les chevauchements au niveau de la base
     * (contrairement a PostgreSQL et ses contraintes d'exclusion).
     * La verification et l'insertion sont donc enveloppees dans une
     * transaction avec verrou : sans lockForUpdate, deux demandes simultanees
     * sur le dernier exemplaire passeraient toutes les deux.
     */
    public function creer(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $materiel = Materiel::with('categorie')
                ->lockForUpdate()
                ->findOrFail($data['materiel_id']);

            // Regle de pret externe, verifiee avant le stock : inutile de
            // bloquer du materiel pour une demande irrecevable.
            if (filled($data['direction_emprunteuse'] ?? null) && ! $this->estPretable($materiel)) {
                throw new RuntimeException(
                    'Seuls les supports de communication peuvent etre pretes a une autre '
                    . "direction. « {$materiel->designation} » ne sort pas de la direction."
                );
            }

            $disponible = $this->quantiteDisponible(
                $materiel, $data['date_debut'], $data['date_fin']
            );

            if ($data['quantite'] > $disponible) {
                throw new RuntimeException(
                    "Quantite insuffisante : {$disponible} exemplaire(s) disponible(s) "
                    . "pour {$materiel->designation} sur cette periode."
                );
            }

            return Reservation::create($data + ['statut' => StatutReservation::Demandee]);
        });
    }

    /** Validation : reverifie le stock, une autre reservation a pu passer entre-temps. */
    public function valider(Reservation $reservation, int $validateurId): Reservation
    {
        return DB::transaction(function () use ($reservation, $validateurId) {
            $materiel = Materiel::lockForUpdate()->findOrFail($reservation->materiel_id);

            $disponible = $this->quantiteDisponible(
                $materiel,
                $reservation->date_debut->toDateTimeString(),
                $reservation->date_fin->toDateTimeString()
            );

            if ($reservation->quantite > $disponible) {
                throw new RuntimeException(
                    "Impossible de valider : seulement {$disponible} exemplaire(s) disponible(s)."
                );
            }

            $reservation->update([
                'statut'        => StatutReservation::Validee,
                'validateur_id' => $validateurId,
                'valide_le'     => now(),
            ]);

            return $reservation;
        });
    }
}
