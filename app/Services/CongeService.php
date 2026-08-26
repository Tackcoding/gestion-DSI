<?php

namespace App\Services;

use App\Enums\StatutDemandeAbsence;
use App\Models\Agent;
use App\Models\DemandeAbsence;
use App\Models\DroitConge;
use App\Models\TypeAbsence;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Regles de conges de la direction :
 *   - conge annuel : 30 jours par an
 *   - permission   : 30 jours par an, 2 jours maximum par demande
 *   - maladie et formation : pas de quota
 *
 * Aucun solde n'est stocke en colonne : tout se recalcule a partir des
 * demandes validees. Une valeur stockee deviendrait fausse des la
 * premiere correction retroactive.
 */
class CongeService
{
    /** Jours accordes a l'agent pour ce type et cette annee. */
    public function joursAccordes(Agent $agent, TypeAbsence $type, ?int $annee = null): float
    {
        $annee ??= (int) date('Y');

        $droit = DroitConge::where('agent_id', $agent->id)
            ->where('type_id', $type->id)
            ->where('annee', $annee)
            ->first();

        // Pas de ligne de droit : on retombe sur le quota par defaut du type.
        return (float) ($droit->jours_accordes ?? $type->quota_annuel ?? 0);
    }

    /** Jours deja consommes (demandes validees uniquement). */
    public function joursConsommes(Agent $agent, TypeAbsence $type, ?int $annee = null): float
    {
        $annee ??= (int) date('Y');

        return (float) DemandeAbsence::where('agent_id', $agent->id)
            ->where('type_id', $type->id)
            ->where('statut', StatutDemandeAbsence::Validee)
            ->whereYear('date_debut', $annee)
            ->sum('nb_jours');
    }

    public function soldeRestant(Agent $agent, TypeAbsence $type, ?int $annee = null): float
    {
        return $this->joursAccordes($agent, $type, $annee)
             - $this->joursConsommes($agent, $type, $annee);
    }

    /** Tous les soldes d'un agent, pour affichage. */
    public function soldes(Agent $agent, ?int $annee = null): array
    {
        $annee ??= (int) date('Y');
        $soldes = [];

        foreach (TypeAbsence::actifs()->whereNotNull('quota_annuel')->get() as $type) {
            $soldes[] = [
                'type'      => $type,
                'accordes'  => $this->joursAccordes($agent, $type, $annee),
                'consommes' => $this->joursConsommes($agent, $type, $annee),
                'restants'  => $this->soldeRestant($agent, $type, $annee),
            ];
        }

        return $soldes;
    }

    /**
     * Nombre de jours ouvres entre deux dates, samedi et dimanche exclus.
     * Une demi-journee compte pour 0,5 et suppose une demande d'un seul jour.
     */
    public function calculerJours(string $debut, string $fin, bool $demiJournee = false): float
    {
        $d = Carbon::parse($debut)->startOfDay();
        $f = Carbon::parse($fin)->startOfDay();

        if ($f->lt($d)) {
            throw new RuntimeException('La date de fin precede la date de debut.');
        }

        $jours = 0;
        for ($jour = $d->copy(); $jour->lte($f); $jour->addDay()) {
            if (! $jour->isWeekend()) {
                $jours++;
            }
        }

        return $demiJournee && $jours === 1 ? 0.5 : (float) $jours;
    }

    /**
     * Verifie une demande avant enregistrement.
     * Renvoie le nombre de jours retenu, ou leve une exception explicite.
     */
    public function verifierDemande(
        Agent $agent,
        TypeAbsence $type,
        string $debut,
        string $fin,
        bool $demiJournee = false,
        ?int $demandeIgnoree = null
    ): float {
        $jours = $this->calculerJours($debut, $fin, $demiJournee);

        if ($jours <= 0) {
            throw new RuntimeException(
                'La periode demandee ne contient aucun jour ouvre.'
            );
        }

        // 1. Plafond par demande (permission : 2 jours)
        if ($type->aUnPlafondParDemande() && $jours > (float) $type->duree_max_par_demande) {
            throw new RuntimeException(sprintf(
                'Une %s ne peut pas depasser %s jour(s) par demande. Demande : %s jour(s).',
                mb_strtolower($type->libelle),
                rtrim(rtrim(number_format((float) $type->duree_max_par_demande, 1, ',', ''), '0'), ','),
                rtrim(rtrim(number_format($jours, 1, ',', ''), '0'), ',')
            ));
        }

        // 2. Chevauchement avec une autre absence deja validee
        $chevauche = DemandeAbsence::where('agent_id', $agent->id)
            ->where('statut', StatutDemandeAbsence::Validee)
            ->when($demandeIgnoree, fn ($q) => $q->where('id', '!=', $demandeIgnoree))
            ->where('date_debut', '<=', $fin)
            ->where('date_fin', '>=', $debut)
            ->exists();

        if ($chevauche) {
            throw new RuntimeException(
                'Une absence deja validee couvre tout ou partie de cette periode.'
            );
        }

        // 3. Solde annuel
        if ($type->decompte_solde && $type->aUnQuota()) {
            $annee  = (int) Carbon::parse($debut)->year;
            $solde  = $this->soldeRestant($agent, $type, $annee);

            if ($jours > $solde) {
                throw new RuntimeException(sprintf(
                    'Solde insuffisant : %s jour(s) restant(s) sur le quota %s %d.',
                    rtrim(rtrim(number_format($solde, 1, ',', ''), '0'), ','),
                    mb_strtolower($type->libelle),
                    $annee
                ));
            }
        }

        return $jours;
    }
}
