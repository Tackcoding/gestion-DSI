<?php

namespace App\Livewire\Signalements;

use App\Enums\StatutSignalement;
use App\Enums\TypeSignalement;
use App\Models\Accessoire;
use App\Models\Agent;
use App\Models\Materiel;
use App\Models\Signalement;
use App\Services\SignalementService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

class ListeSignalements extends Component
{
    use WithPagination;

    #[Url]
    public string $filtreStatut = '';

    // --- Formulaire ---
    public bool $modaleOuverte = false;
    public string $type = 'casse';
    public ?int $materiel_id = null;
    public ?int $accessoire_id = null;
    public int $quantite = 1;
    public string $date_constat = '';
    public ?int $agent_responsable_id = null;
    public string $circonstances = '';

    // --- Visa ---
    public ?int $visaId = null;
    public string $observation_visa = '';

    // --- Classement ---
    public ?int $classementId = null;
    public string $suite_donnee = '';

    protected function rules(): array
    {
        return [
            'type'                 => 'required|in:perte,casse',
            'materiel_id'          => 'required|exists:materiels,id',
            'accessoire_id'        => 'nullable|exists:accessoires,id',
            'quantite'             => 'required|integer|min:1',
            'date_constat'         => 'required|date|before_or_equal:today',
            'agent_responsable_id' => 'nullable|exists:agents,id',
            'circonstances'        => 'required|string|min:20|max:2000',
        ];
    }

    protected array $messages = [
        'materiel_id.required'   => 'Choisissez le materiel concerne.',
        'circonstances.required' => 'Les circonstances doivent etre decrites.',
        'circonstances.min'      => 'La description doit etre suffisamment precise (20 caracteres minimum).',
        'date_constat.before_or_equal' => 'La date du constat ne peut pas etre dans le futur.',
    ];

    public function ouvrirCreation(): void
    {
        $this->authorize('gerer-materiel');

        $this->reset(['materiel_id', 'accessoire_id', 'agent_responsable_id', 'circonstances']);
        $this->type = 'casse';
        $this->quantite = 1;
        $this->date_constat = now()->format('Y-m-d');

        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    /** Changer de materiel remet l'accessoire a zero : il n'appartient plus au bon parent. */
    public function updatedMaterielId(): void
    {
        $this->accessoire_id = null;
    }

    public function enregistrer(): void
    {
        $this->authorize('gerer-materiel');

        $data = $this->validate();

        app(SignalementService::class)->creer($data + [
            'constate_par_id' => auth()->user()->agent?->id,
        ]);

        $this->modaleOuverte = false;
        session()->flash('message', 'Signalement enregistre, en attente du visa du directeur.');
    }

    // --- Visa du directeur ---

    public function ouvrirVisa(int $id): void
    {
        $this->authorize('viser-signalement');

        $this->visaId = $id;
        $this->observation_visa = '';
    }

    public function viser(): void
    {
        $this->authorize('viser-signalement');

        try {
            app(SignalementService::class)->viser(
                Signalement::findOrFail($this->visaId),
                auth()->user()->agent?->id,
                $this->observation_visa ?: null,
            );
        } catch (RuntimeException $e) {
            session()->flash('erreur', $e->getMessage());
            $this->visaId = null;
            return;
        }

        $this->visaId = null;
        session()->flash('message', 'Signalement vise.');
    }

    // --- Classement ---

    public function ouvrirClassement(int $id): void
    {
        $this->authorize('viser-signalement');

        $this->classementId = $id;
        $this->suite_donnee = '';
    }

    public function classer(): void
    {
        $this->authorize('viser-signalement');

        $this->validate(['suite_donnee' => 'required|string|min:10'], [
            'suite_donnee.required' => 'Indiquez la suite donnee au signalement.',
        ]);

        try {
            app(SignalementService::class)->classer(
                Signalement::findOrFail($this->classementId),
                $this->suite_donnee,
            );
        } catch (RuntimeException $e) {
            session()->flash('erreur', $e->getMessage());
            $this->classementId = null;
            return;
        }

        $this->classementId = null;
        session()->flash('message', 'Signalement classe.');
    }

    public function render()
    {
        $signalements = Signalement::query()
            ->with(['materiel', 'accessoire', 'constatePar', 'agentResponsable', 'visePar'])
            ->when($this->filtreStatut, fn ($q) => $q->where('statut', $this->filtreStatut))
            ->orderByDesc('date_constat')
            ->paginate(10);

        return view('livewire.signalements.liste-signalements', [
            'signalements' => $signalements,
            'statuts'      => StatutSignalement::cases(),
            'types'        => TypeSignalement::cases(),
            'materiels'    => Materiel::orderBy('designation')->get(),
            'accessoires'  => $this->materiel_id
                ? Accessoire::where('materiel_id', $this->materiel_id)->get()
                : collect(),
            'agents'       => Agent::actifs()->orderBy('nom')->get(),
        ]);
    }
}
