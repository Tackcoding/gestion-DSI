<?php

namespace App\Livewire\Absences;

use App\Enums\StatutDemandeAbsence;
use App\Models\DemandeAbsence;
use App\Services\CongeService;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Validation des demandes d'absence.
 * Reserve au directeur et a l'administrateur (Gate 'valider-absence').
 */
class ValidationAbsences extends Component
{
    use WithPagination;

    public ?int $refusId = null;
    public string $motif_refus = '';

    public function mount(): void
    {
        $this->authorize('valider-absence');
    }

    public function valider(int $id): void
    {
        $this->authorize('valider-absence');

        $demande = DemandeAbsence::with(['agent', 'type'])->findOrFail($id);

        // Le solde a pu bouger depuis le depot : on reverifie avant d'engager.
        try {
            app(CongeService::class)->verifierDemande(
                $demande->agent,
                $demande->type,
                $demande->date_debut->toDateString(),
                $demande->date_fin->toDateString(),
                $demande->demi_journee,
                $demande->id,   // on ignore la demande elle-meme
            );
        } catch (RuntimeException $e) {
            session()->flash('erreur', "Validation impossible : {$e->getMessage()}");
            return;
        }

        $demande->update([
            'statut'        => StatutDemandeAbsence::Validee,
            'validateur_id' => auth()->user()->agent?->id,
            'valide_le'     => now(),
        ]);

        session()->flash('message', 'Absence validee.');
    }

    public function ouvrirRefus(int $id): void
    {
        $this->authorize('valider-absence');

        $this->refusId = $id;
        $this->motif_refus = '';
    }

    public function refuser(): void
    {
        $this->authorize('valider-absence');

        $this->validate(['motif_refus' => 'required|string|min:5'], [
            'motif_refus.required' => 'Indiquez le motif du refus.',
            'motif_refus.min'      => 'Le motif doit etre explicite.',
        ]);

        DemandeAbsence::findOrFail($this->refusId)->update([
            'statut'        => StatutDemandeAbsence::Refusee,
            'validateur_id' => auth()->user()->agent?->id,
            'valide_le'     => now(),
            'motif_refus'   => $this->motif_refus,
        ]);

        $this->refusId = null;
        $this->motif_refus = '';

        session()->flash('message', 'Demande refusee.');
    }

    public function render()
    {
        return view('livewire.absences.validation-absences', [
            'enAttente' => DemandeAbsence::with(['agent.fonction', 'type'])
                ->where('statut', StatutDemandeAbsence::Demandee)
                ->orderBy('date_debut')
                ->paginate(10),
        ]);
    }
}
