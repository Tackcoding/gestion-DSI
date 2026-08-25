<?php

namespace App\Livewire;

use App\Enums\StatutEvenement;
use App\Models\Agent;
use App\Models\Couverture;
use App\Models\Evenement;
use App\Models\Materiel;
use Illuminate\Support\Carbon;
use Livewire\Component;

class TableauDeBord extends Component
{
    public function render()
    {
        $aujourdhui = Carbon::today();

        return view('livewire.tableau-de-bord', [
            // Ce qui se passe aujourd'hui : la premiere question du responsable.
            'couverturesDuJour' => Couverture::with(['evenement', 'agents'])
                ->whereDate('date', $aujourdhui)
                ->orderBy('heure_depart')
                ->get(),

            'prochainesCouvertures' => Couverture::with('evenement')
                ->whereDate('date', '>', $aujourdhui)
                ->orderBy('date')
                ->limit(5)
                ->get(),

            'evenementsEnCours' => Evenement::whereIn('statut', [
                    StatutEvenement::Valide, StatutEvenement::EnCours,
                ])->count(),

            'agentsActifs'    => Agent::actifs()->count(),
            'referencesMateriel' => Materiel::where('actif', true)->count(),
        ]);
    }
}
