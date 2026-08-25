<?php

namespace App\Livewire\Evenements;

use App\Models\Couverture;
use App\Models\Evenement;
use App\Services\DisponibiliteService;
use Livewire\Component;

class DetailEvenement extends Component
{
    public Evenement $evenement;

    // --- Formulaire couverture ---
    public bool $modaleCouverture = false;
    public ?int $couvertureId = null;
    public string $date = '';
    public string $heure_depart = '';
    public string $lieu_depart = '';
    public string $heure_retour = '';
    public string $lieu_retour = '';
    public string $observation = '';

    // --- Affectation d'equipe ---
    public ?int $couvertureEquipeId = null;
    public array $agentsSelectionnes = [];

    public ?int $suppressionCouvertureId = null;

    public function mount(Evenement $evenement): void
    {
        $this->evenement = $evenement;
    }

    protected function rules(): array
    {
        return [
            'date'         => 'required|date',
            'heure_depart' => 'nullable|date_format:H:i',
            'lieu_depart'  => 'nullable|string|max:255',
            'heure_retour' => 'nullable|date_format:H:i',
            'lieu_retour'  => 'nullable|string|max:255',
            'observation'  => 'nullable|string',
        ];
    }

    public function ouvrirCreationCouverture(): void
    {
        $this->reinitialiserCouverture();
        $this->date = $this->evenement->date_debut->format('Y-m-d');
        $this->modaleCouverture = true;
    }

    public function ouvrirEditionCouverture(int $id): void
    {
        $c = Couverture::findOrFail($id);

        $this->couvertureId = $c->id;
        $this->date         = $c->date->format('Y-m-d');
        $this->heure_depart = $c->heure_depart ? substr($c->heure_depart, 0, 5) : '';
        $this->lieu_depart  = $c->lieu_depart ?? '';
        $this->heure_retour = $c->heure_retour ? substr($c->heure_retour, 0, 5) : '';
        $this->lieu_retour  = $c->lieu_retour ?? '';
        $this->observation  = $c->observation ?? '';

        $this->resetValidation();
        $this->modaleCouverture = true;
    }

    public function enregistrerCouverture(): void
    {
        $data = $this->validate();

        // Chaines vides -> null, sinon les colonnes TIME rejettent ''
        foreach (['heure_depart', 'heure_retour', 'lieu_depart', 'lieu_retour', 'observation'] as $champ) {
            $data[$champ] = $data[$champ] ?: null;
        }

        $data['evenement_id'] = $this->evenement->id;

        Couverture::updateOrCreate(['id' => $this->couvertureId], $data);

        $this->modaleCouverture = false;
        $this->reinitialiserCouverture();

        session()->flash('message', 'Couverture enregistree.');
    }

    /**
     * Ouvre l'affectation d'equipe.
     * C'est le point ou les deux modules de l'application se parlent :
     * seuls les agents ni absents ni deja mobilises sont proposes.
     */
    public function ouvrirEquipe(int $couvertureId): void
    {
        $this->couvertureEquipeId = $couvertureId;

        $couverture = Couverture::with('agents')->findOrFail($couvertureId);
        $this->agentsSelectionnes = $couverture->agents->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function enregistrerEquipe(): void
    {
        $couverture = Couverture::findOrFail($this->couvertureEquipeId);

        $couverture->agents()->sync($this->agentsSelectionnes);

        $this->couvertureEquipeId = null;
        $this->agentsSelectionnes = [];

        session()->flash('message', 'Equipe mise a jour.');
    }

    public function confirmerSuppressionCouverture(int $id): void
    {
        $this->suppressionCouvertureId = $id;
    }

    public function supprimerCouverture(): void
    {
        Couverture::findOrFail($this->suppressionCouvertureId)->delete();
        $this->suppressionCouvertureId = null;

        session()->flash('message', 'Couverture supprimee.');
    }

    private function reinitialiserCouverture(): void
    {
        $this->reset(['couvertureId', 'date', 'heure_depart', 'lieu_depart',
                      'heure_retour', 'lieu_retour', 'observation']);
        $this->resetValidation();
    }

    public function render(DisponibiliteService $disponibilite)
    {
        $couvertures = $this->evenement->couvertures()
            ->with('agents.fonction')
            ->orderBy('date')
            ->get();

        // Agents proposables pour l'affectation en cours.
        $agentsDisponibles = collect();
        $couvertureEnCours = null;

        if ($this->couvertureEquipeId) {
            $couvertureEnCours = $couvertures->firstWhere('id', $this->couvertureEquipeId);

            if ($couvertureEnCours) {
                $jour = $couvertureEnCours->date->format('Y-m-d');

                // Les agents deja affectes a CETTE couverture doivent rester
                // dans la liste, sinon on ne pourrait plus les decocher.
                $dejaAffectes = $couvertureEnCours->agents->pluck('id');

                $agentsDisponibles = $disponibilite
                    ->agentsDisponibles($jour, $jour)
                    ->concat($couvertureEnCours->agents)
                    ->unique('id')
                    ->sortBy('nom')
                    ->values();
            }
        }

        return view('livewire.evenements.detail-evenement', [
            'couvertures'       => $couvertures,
            'agentsDisponibles' => $agentsDisponibles,
            'couvertureEnCours' => $couvertureEnCours,
        ]);
    }
}
