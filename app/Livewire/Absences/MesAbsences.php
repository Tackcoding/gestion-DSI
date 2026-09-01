<?php

namespace App\Livewire\Absences;

use App\Enums\StatutDemandeAbsence;
use App\Models\Agent;
use App\Models\DemandeAbsence;
use App\Models\TypeAbsence;
use App\Services\CongeService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Demandes d'absence.
 *
 * Tout agent peut deposer une demande. Seuls le directeur et
 * l'administrateur valident (voir Gate 'valider-absence').
 */
class MesAbsences extends Component
{
    use WithPagination, WithFileUploads;

    // --- Formulaire ---
    public bool $modaleOuverte = false;
    public ?int $agent_id = null;
    public ?int $type_id = null;
    public string $date_debut = '';
    public string $date_fin = '';
    public bool $demi_journee = false;
    public string $motif = '';
    public $justificatif = null;

    public ?int $suppressionId = null;

    public function mount(): void
    {
        $this->agent_id = $this->agentConnecte()?->id;
    }

    protected function rules(): array
    {
        return [
            'agent_id'      => 'required|exists:agents,id',
            'type_id'       => 'required|exists:types_absence,id',
            'date_debut'    => 'required|date',
            'date_fin'      => 'required|date|after_or_equal:date_debut',
            'demi_journee'  => 'boolean',
            'motif'         => 'nullable|string|max:500',
            'justificatif'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ];
    }

    protected array $messages = [
        'agent_id.required'   => 'Choisissez un agent.',
        'type_id.required'    => 'Choisissez un type d\'absence.',
        'date_fin.after_or_equal' => 'La date de fin ne peut pas preceder la date de debut.',
        'justificatif.mimes'  => 'Le justificatif doit etre un PDF ou une image.',
        'justificatif.max'    => 'Le justificatif ne doit pas depasser 4 Mo.',
    ];

    /** Soldes de l'agent selectionne, recalcules a chaque changement. */
    #[Computed]
    public function soldes(): array
    {
        if (! $this->agent_id) {
            return [];
        }

        $agent = Agent::find($this->agent_id);

        return $agent ? app(CongeService::class)->soldes($agent) : [];
    }

    /** Apercu du decompte avant envoi : nombre de jours ouvres retenus. */
    #[Computed]
    public function apercuJours(): ?float
    {
        if (! $this->date_debut || ! $this->date_fin) {
            return null;
        }

        try {
            return app(CongeService::class)->calculerJours(
                $this->date_debut, $this->date_fin, $this->demi_journee
            );
        } catch (RuntimeException) {
            return null;
        }
    }

    public function ouvrirCreation(): void
    {
        $this->reset(['type_id', 'date_debut', 'date_fin', 'demi_journee', 'motif', 'justificatif']);
        $this->agent_id = $this->agentConnecte()?->id;
        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $data = $this->validate();

        $agent = Agent::findOrFail($data['agent_id']);
        $type  = TypeAbsence::findOrFail($data['type_id']);

        // Toutes les regles metier sont dans CongeService :
        // plafond par demande, chevauchement, solde annuel.
        try {
            $jours = app(CongeService::class)->verifierDemande(
                $agent, $type, $data['date_debut'], $data['date_fin'], $data['demi_journee']
            );
        } catch (RuntimeException $e) {
            $this->addError('date_fin', $e->getMessage());
            return;
        }

        if ($type->necessite_justificatif && ! $this->justificatif) {
            $this->addError('justificatif',
                "Un justificatif est obligatoire pour une {$type->libelle}.");
            return;
        }

        $chemin = $this->justificatif
            ? $this->justificatif->store('justificatifs', 'public')
            : null;

        DemandeAbsence::create([
            'agent_id'          => $agent->id,
            'type_id'           => $type->id,
            'date_debut'        => $data['date_debut'],
            'date_fin'          => $data['date_fin'],
            'demi_journee'      => $data['demi_journee'],
            'nb_jours'          => $jours,
            'motif'             => $data['motif'] ?: null,
            'origine'           => 'demande',
            'statut'            => StatutDemandeAbsence::Demandee,
            'justificatif_path' => $chemin,
        ]);

        $this->modaleOuverte = false;
        $this->reset(['type_id', 'date_debut', 'date_fin', 'demi_journee', 'motif', 'justificatif']);

        session()->flash('message', 'Demande enregistree, en attente de validation.');
    }

    public function confirmerSuppression(int $id): void
    {
        $this->suppressionId = $id;
    }

    public function supprimer(): void
    {
        $demande = DemandeAbsence::findOrFail($this->suppressionId);

        // Une demande deja tranchee ne se retire plus : elle fait foi.
        if ($demande->statut !== StatutDemandeAbsence::Demandee) {
            $this->suppressionId = null;
            session()->flash('erreur',
                'Une demande deja traitee ne peut plus etre supprimee.');
            return;
        }

        $demande->delete();
        $this->suppressionId = null;

        session()->flash('message', 'Demande annulee.');
    }

    private function agentConnecte(): ?Agent
    {
        return auth()->user()->agent;
    }

    public function render()
    {
        $agent = $this->agentConnecte();

        $demandes = DemandeAbsence::query()
            ->with(['agent', 'type', 'validateur'])
            // Un responsable voit tout, un agent ne voit que ses propres demandes.
            ->when(! auth()->user()->can('valider-absence') && $agent,
                   fn ($q) => $q->where('agent_id', $agent->id))
            ->orderByDesc('date_debut')
            ->paginate(10);

        return view('livewire.absences.mes-absences', [
            'demandes' => $demandes,
            'types'    => TypeAbsence::actifs()->where('code', '!=', 'non_justifiee')
                                     ->orderBy('libelle')->get(),
            // Saisir pour un autre agent : reserve aux responsables.
            'agents'   => auth()->user()->can('valider-absence')
                ? Agent::actifs()->orderBy('nom')->get()
                : collect([$agent])->filter(),
        ]);
    }
}
