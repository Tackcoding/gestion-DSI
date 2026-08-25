<?php

namespace App\Livewire\Evenements;

use App\Enums\StatutEvenement;
use App\Models\Agent;
use App\Models\Evenement;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ListeEvenements extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $recherche = '';

    #[Url]
    public string $filtreStatut = '';

    // --- Formulaire ---
    public bool $modaleOuverte = false;
    public ?int $evenementId = null;
    public string $intitule = '';
    public string $description = '';
    public string $lieu = '';
    public string $date_debut = '';
    public string $date_fin = '';
    public ?int $demandeur_id = null;

    public ?int $suppressionId = null;

    protected function rules(): array
    {
        return [
            'intitule'     => 'required|string|max:255',
            'description'  => 'nullable|string',
            'lieu'         => 'nullable|string|max:255',
            'date_debut'   => 'required|date',
            // La date de fin ne peut pas preceder le debut : regle metier
            // verifiee ici, la base ne sait pas l'exprimer.
            'date_fin'     => 'required|date|after_or_equal:date_debut',
            'demandeur_id' => 'required|exists:agents,id',
        ];
    }

    protected array $messages = [
        'intitule.required'         => 'L\'intitule est obligatoire.',
        'date_debut.required'       => 'La date de debut est obligatoire.',
        'date_fin.after_or_equal'   => 'La date de fin doit suivre la date de debut.',
        'demandeur_id.required'     => 'Le demandeur est obligatoire.',
    ];

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreStatut(): void
    {
        $this->resetPage();
    }

    public function ouvrirCreation(): void
    {
        $this->reinitialiserFormulaire();
        $this->modaleOuverte = true;
    }

    public function ouvrirEdition(int $id): void
    {
        $evenement = Evenement::findOrFail($id);

        $this->evenementId  = $evenement->id;
        $this->intitule     = $evenement->intitule;
        $this->description  = $evenement->description ?? '';
        $this->lieu         = $evenement->lieu ?? '';
        $this->date_debut   = $evenement->date_debut->format('Y-m-d');
        $this->date_fin     = $evenement->date_fin->format('Y-m-d');
        $this->demandeur_id = $evenement->demandeur_id;

        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $data = $this->validate();
        $modification = (bool) $this->evenementId;

        if (! $modification) {
            $data['statut'] = StatutEvenement::Brouillon;
        }

        Evenement::updateOrCreate(['id' => $this->evenementId], $data);

        $this->modaleOuverte = false;
        $this->reinitialiserFormulaire();

        session()->flash('message', $modification ? 'Evenement modifie.' : 'Evenement cree.');
    }

    /** Passage de brouillon a valide : l'evenement devient operationnel. */
    public function valider(int $id): void
    {
        $evenement = Evenement::findOrFail($id);

        if ($evenement->statut !== StatutEvenement::Brouillon) {
            session()->flash('erreur', 'Seul un brouillon peut etre valide.');
            return;
        }

        $evenement->update(['statut' => StatutEvenement::Valide]);
        session()->flash('message', 'Evenement valide.');
    }

    public function annuler(int $id): void
    {
        $evenement = Evenement::findOrFail($id);
        $evenement->update(['statut' => StatutEvenement::Annule]);

        session()->flash('message', 'Evenement annule.');
    }

    public function confirmerSuppression(int $id): void
    {
        $this->suppressionId = $id;
    }

    public function supprimer(): void
    {
        $evenement = Evenement::findOrFail($this->suppressionId);

        // Garde-fou : un evenement engage (couvertures ou reservations)
        // s'annule, il ne se supprime pas.
        if ($evenement->couvertures()->exists() || $evenement->reservations()->exists()) {
            $this->suppressionId = null;
            session()->flash('erreur',
                'Cet evenement a des couvertures ou des reservations : annulez-le plutot.');
            return;
        }

        $evenement->delete();
        $this->suppressionId = null;

        session()->flash('message', 'Evenement supprime.');
    }

    private function reinitialiserFormulaire(): void
    {
        $this->reset(['evenementId', 'intitule', 'description', 'lieu',
                      'date_debut', 'date_fin', 'demandeur_id']);
        $this->resetValidation();
    }

    public function render()
    {
        $evenements = Evenement::query()
            ->with(['demandeur.fonction'])
            ->withCount(['couvertures', 'reservations'])
            ->when($this->recherche, fn ($q) => $q->where(function ($sq) {
                $sq->where('intitule', 'like', "%{$this->recherche}%")
                   ->orWhere('lieu', 'like', "%{$this->recherche}%");
            }))
            ->when($this->filtreStatut, fn ($q) => $q->where('statut', $this->filtreStatut))
            ->orderByDesc('date_debut')
            ->paginate(10);

        return view('livewire.evenements.liste-evenements', [
            'evenements' => $evenements,
            'statuts'    => StatutEvenement::cases(),
            'agents'     => Agent::actifs()->orderBy('nom')->get(),
        ]);
    }
}
