<?php

namespace App\Livewire\Agents;

use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Service;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ListeAgents extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $recherche = '';

    #[Url]
    public string $filtreFonction = '';

    // --- Etat du formulaire (modale) ---
    public bool $modaleOuverte = false;
    public ?int $agentId = null;
    public string $im = '';
    public string $nom = '';
    public string $prenom = '';
    public ?int $fonction_id = null;
    public ?int $service_id = null;
    public string $telephone = '';
    public bool $actif = true;

    public ?int $suppressionId = null;

    protected function rules(): array
    {
        return [
            'im'          => ['nullable', 'string', 'max:20',
                              Rule::unique('agents', 'im')->ignore($this->agentId)],
            'nom'         => 'required|string|max:100',
            'prenom'      => 'required|string|max:150',
            'fonction_id' => 'required|exists:fonctions,id',
            'service_id'  => 'required|exists:services,id',
            'telephone'   => 'nullable|string|max:30',
            'actif'       => 'boolean',
        ];
    }

    protected array $messages = [
        'nom.required'         => 'Le nom est obligatoire.',
        'prenom.required'      => 'Le prenom est obligatoire.',
        'fonction_id.required' => 'La fonction est obligatoire.',
        'service_id.required'  => 'Le service est obligatoire.',
        'im.unique'            => 'Cet IM est deja attribue a un autre agent.',
    ];

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreFonction(): void
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
        $agent = Agent::findOrFail($id);

        $this->agentId     = $agent->id;
        $this->im          = $agent->im ?? '';
        $this->nom         = $agent->nom;
        $this->prenom      = $agent->prenom;
        $this->fonction_id = $agent->fonction_id;
        $this->service_id  = $agent->service_id;
        $this->telephone   = $agent->telephone ?? '';
        $this->actif       = $agent->actif;

        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $data = $this->validate();

        // Chaine vide -> null, sinon l'unicite de l'IM casse au 2e agent sans IM.
        $data['im'] = $data['im'] ?: null;

        $modification = (bool) $this->agentId;

        Agent::updateOrCreate(['id' => $this->agentId], $data);

        $this->modaleOuverte = false;
        $this->reinitialiserFormulaire();

        session()->flash('message', $modification ? 'Agent modifie.' : 'Agent ajoute.');
    }

    public function confirmerSuppression(int $id): void
    {
        $this->suppressionId = $id;
    }

    public function supprimer(): void
    {
        $agent = Agent::findOrFail($this->suppressionId);

        if ($agent->couvertures()->exists() || $agent->demandesAbsence()->exists()) {
            $this->suppressionId = null;
            session()->flash('erreur',
                'Cet agent a un historique : desactivez-le plutot que de le supprimer.');
            return;
        }

        $agent->delete();
        $this->suppressionId = null;

        session()->flash('message', 'Agent supprime.');
    }

    private function reinitialiserFormulaire(): void
    {
        $this->reset(['agentId', 'im', 'nom', 'prenom', 'fonction_id',
                      'service_id', 'telephone']);
        $this->actif = true;
        $this->resetValidation();
    }

    public function render()
    {
        $agents = Agent::query()
            ->with(['fonction', 'service'])
            ->when($this->recherche, fn ($q) => $q->where(function ($sq) {
                $sq->where('nom', 'like', "%{$this->recherche}%")
                   ->orWhere('prenom', 'like', "%{$this->recherche}%")
                   ->orWhere('im', 'like', "%{$this->recherche}%");
            }))
            ->when($this->filtreFonction,
                   fn ($q) => $q->where('fonction_id', $this->filtreFonction))
            ->orderBy('nom')
            ->paginate(10);

        return view('livewire.agents.liste-agents', [
            'agents'    => $agents,
            'fonctions' => Fonction::orderBy('libelle')->get(),
            'services'  => Service::orderBy('libelle')->get(),
        ]);
    }
}