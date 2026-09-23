<?php

namespace App\Livewire\Absences;

use App\Enums\StatutDemandeAbsence;
use App\Models\Agent;
use App\Models\DemandeAbsence;
use App\Models\TypeAbsence;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Planning des absences : une ligne par agent, une colonne par jour du mois.
 *
 * Demande du directeur : identifier d'un coup d'oeil qui est absent.
 * La colonne d'un jour dit qui est absent ce jour-la ; la ligne d'un agent
 * dit quand il est absent. Reserve au directeur et a l'administrateur
 * (route protegee par le Gate 'valider-absence').
 *
 * Seuls les jours ouvres sont marques : un conge qui couvre un week-end
 * ne decompte pas le samedi et le dimanche (voir CongeService).
 */
class PlanningAbsences extends Component
{
    /** Mois affiche, au format AAAA-MM. Garde dans l'URL pour pouvoir partager le lien. */
    #[Url]
    public string $mois = '';

    /** Code de type -> lettre affichee dans la case (lisible sans la couleur). */
    public const LETTRES = [
        'conge_annuel'  => 'C',
        'permission'    => 'P',
        'maladie'       => 'M',
        'formation'     => 'F',
        'non_justifiee' => 'N',
    ];

    public function mount(): void
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->mois)) {
            $this->mois = now()->format('Y-m');
        }
    }

    public function moisPrecedent(): void
    {
        $this->mois = $this->debutMois()->subMonthNoOverflow()->format('Y-m');
    }

    public function moisSuivant(): void
    {
        $this->mois = $this->debutMois()->addMonthNoOverflow()->format('Y-m');
    }

    public function moisCourant(): void
    {
        $this->mois = now()->format('Y-m');
    }

    private function debutMois(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', "{$this->mois}-01")->startOfDay();
    }

    public function render()
    {
        $debut = $this->debutMois();
        $fin   = $debut->copy()->endOfMonth()->startOfDay();

        $jours = collect(CarbonPeriod::create($debut, $fin))
            ->map(fn ($jour) => Carbon::instance($jour));

        $agents = Agent::actifs()->with('fonction')->orderBy('nom')->get();

        // Demandes validees et en attente qui touchent le mois affiche.
        // Les validees passent en premier : si une demande en attente
        // chevauche une absence deja validee, c'est la validee qui s'affiche.
        $demandes = DemandeAbsence::query()
            ->with('type')
            ->whereIn('statut', [StatutDemandeAbsence::Validee, StatutDemandeAbsence::Demandee])
            ->chevauchant($debut->toDateString(), $fin->toDateString())
            ->get()
            ->sortBy(fn ($d) => $d->statut === StatutDemandeAbsence::Validee ? 0 : 1);

        // $grille[agent_id]['AAAA-MM-JJ'] = demande
        $grille = [];
        // $absentsParJour['AAAA-MM-JJ'] = nombre d'absents valides
        $absentsParJour = [];

        foreach ($demandes as $demande) {
            $periode = CarbonPeriod::create(
                $demande->date_debut->max($debut),
                $demande->date_fin->min($fin)
            );

            foreach ($periode as $jour) {
                if ($jour->isWeekend()) {
                    continue;
                }

                $cle = $jour->toDateString();

                if (isset($grille[$demande->agent_id][$cle])) {
                    continue;
                }

                $grille[$demande->agent_id][$cle] = $demande;

                if ($demande->statut === StatutDemandeAbsence::Validee) {
                    $absentsParJour[$cle] = ($absentsParJour[$cle] ?? 0) + 1;
                }
            }
        }

        // Absents du jour, quand le mois affiche est le mois courant.
        $aujourdhui = now()->toDateString();
        $absentsAujourdhui = $agents
            ->filter(function ($agent) use ($grille, $aujourdhui) {
                $demande = $grille[$agent->id][$aujourdhui] ?? null;
                return $demande && $demande->statut === StatutDemandeAbsence::Validee;
            })
            ->map(fn ($agent) => [
                'agent' => $agent,
                'type'  => $grille[$agent->id][$aujourdhui]->type,
            ]);

        return view('livewire.absences.planning-absences', [
            'jours'             => $jours,
            'agents'            => $agents,
            'grille'            => $grille,
            'absentsParJour'    => $absentsParJour,
            'absentsAujourdhui' => $absentsAujourdhui,
            'estMoisCourant'    => $this->mois === now()->format('Y-m'),
            'libelleMois'       => ucfirst($debut->locale('fr')->translatedFormat('F Y')),
            'types'             => TypeAbsence::actifs()->orderBy('libelle')->get(),
            'lettres'           => self::LETTRES,
        ]);
    }
}
