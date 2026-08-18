<?php

namespace App\Livewire\Materiels;

use App\Enums\EtatMateriel;
use App\Models\Materiel;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ListeMateriels extends Component
{
    use WithPagination;

    /** #[Url] garde le filtre dans l'URL : la page reste partageable et rechargeable. */
    #[Url(as: 'q')]
    public string $recherche = '';

    #[Url]
    public string $filtreEtat = '';

    // --- Etat du formulaire (modale) ---
    public bool $modaleOuverte = false;
    public ?int $materielId = null;
    public string $designation = '';
    public string $description = '';
    public int $quantite_totale = 1;
    public string $etat = 'bon';
    public bool $actif = true;

    // --- Suppression ---
    public ?int $suppressionId = null;

    protected function rules(): array
    {
        return [
            'designation'     => 'required|string|max:255',
            'description'     => 'nullable|string',
            'quantite_totale' => 'required|integer|min:1|max:9999',
            'etat'            => 'required|string|in:bon,moyen,hors_service',
            'actif'           => 'boolean',
        ];
    }

    protected array $messages = [
        'designation.required'     => 'La designation est obligatoire.',
        'quantite_totale.min'      => 'La quantite doit etre au moins de 1.',
    ];

    /** Revenir page 1 quand on filtre, sinon on peut se retrouver sur une page vide. */
    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreEtat(): void
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
        $materiel = Materiel::findOrFail($id);

        $this->materielId      = $materiel->id;
        $this->designation     = $materiel->designation;
        $this->description     = $materiel->description ?? '';
        $this->quantite_totale = $materiel->quantite_totale;
        $this->etat            = $materiel->etat->value;
        $this->actif           = $materiel->actif;

        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $data = $this->validate();

        Materiel::updateOrCreate(['id' => $this->materielId], $data);

        $this->modaleOuverte = false;
        $this->reinitialiserFormulaire();

        session()->flash('message', $this->materielId
            ? 'Materiel modifie.'
            : 'Materiel ajoute.');
    }

    public function confirmerSuppression(int $id): void
    {
        $this->suppressionId = $id;
    }

    public function supprimer(): void
    {
        $materiel = Materiel::findOrFail($this->suppressionId);

        // Garde-fou : on ne supprime pas un materiel deja engage.
        if ($materiel->reservations()->exists()) {
            $this->suppressionId = null;
            session()->flash('erreur',
                'Ce materiel a des reservations : desactivez-le plutot que de le supprimer.');
            return;
        }

        $materiel->delete(); // softDelete
        $this->suppressionId = null;

        session()->flash('message', 'Materiel supprime.');
    }

    private function reinitialiserFormulaire(): void
    {
        $this->reset(['materielId', 'designation', 'description', 'quantite_totale', 'etat', 'actif']);
        $this->quantite_totale = 1;
        $this->etat = 'bon';
        $this->actif = true;
        $this->resetValidation();
    }

    public function render()
    {
        $materiels = Materiel::query()
            ->when($this->recherche, fn ($q) => $q->where('designation', 'like', "%{$this->recherche}%"))
            ->when($this->filtreEtat, fn ($q) => $q->where('etat', $this->filtreEtat))
            ->orderBy('designation')
            ->paginate(10);

        return view('livewire.materiels.liste-materiels', [
            'materiels' => $materiels,
            'etats'     => EtatMateriel::cases(),
        ]);
    }
}
