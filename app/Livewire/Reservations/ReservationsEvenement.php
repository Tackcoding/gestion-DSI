<?php

namespace App\Livewire\Reservations;

use App\Enums\StatutReservation;
use App\Models\Agent;
use App\Models\Evenement;
use App\Models\Materiel;
use App\Models\Reservation;
use App\Services\ReservationService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

/**
 * Reservations de materiel rattachees a un evenement.
 *
 * Le calcul de disponibilite et l'enregistrement passent tous deux par
 * ReservationService : c'est lui qui pose le verrou de transaction et
 * qui applique la regle du pret externe. Le composant ne fait que de
 * l'interface.
 */
class ReservationsEvenement extends Component
{
    public Evenement $evenement;

    // --- Formulaire ---
    public bool $modaleOuverte = false;
    public ?int $reservationId = null;
    public ?int $materiel_id = null;
    public int $quantite = 1;
    public string $date_debut = '';
    public string $date_fin = '';

    // --- Pret a une autre direction ---
    public bool $pretExterne = false;
    public string $direction_emprunteuse = '';
    public string $contact_emprunteur = '';

    // --- Refus ---
    public ?int $refusId = null;
    public string $motif_refus = '';

    public ?int $suppressionId = null;

    public function mount(Evenement $evenement): void
    {
        $this->evenement = $evenement;
    }

    protected function rules(): array
    {
        return [
            'materiel_id'           => 'required|exists:materiels,id',
            'quantite'              => 'required|integer|min:1',
            'date_debut'            => 'required|date',
            'date_fin'              => 'required|date|after:date_debut',
            'direction_emprunteuse' => 'nullable|string|max:255',
            'contact_emprunteur'    => 'nullable|string|max:150',
        ];
    }

    protected array $messages = [
        'materiel_id.required' => 'Choisissez un materiel.',
        'quantite.min'         => 'La quantite doit etre au moins de 1.',
        'date_fin.after'       => 'Le retour doit etre posterieur au retrait.',
    ];

    /** Le materiel selectionne peut-il sortir de la direction ? */
    #[Computed]
    public function materielPretable(): bool
    {
        if (! $this->materiel_id) {
            return false;
        }

        $materiel = Materiel::with('categorie')->find($this->materiel_id);

        return $materiel && app(ReservationService::class)->estPretable($materiel);
    }

    /**
     * Disponibilite du materiel selectionne sur la periode saisie.
     * Recalculee a chaque frappe : le formulaire previent avant l'envoi.
     */
    #[Computed]
    public function disponibilite(): ?array
    {
        if (! $this->materiel_id || ! $this->date_debut || ! $this->date_fin) {
            return null;
        }

        $materiel = Materiel::find($this->materiel_id);
        if (! $materiel) {
            return null;
        }

        $dispo = app(ReservationService::class)->quantiteDisponible(
            $materiel, $this->date_debut, $this->date_fin,
        );

        return [
            'materiel'   => $materiel,
            'total'      => $materiel->quantite_totale,
            'disponible' => $dispo,
            'suffisant'  => $this->quantite <= $dispo,
        ];
    }

    /** Changer de materiel peut invalider le pret : on le remet a zero. */
    public function updatedMaterielId(): void
    {
        if (! $this->materielPretable()) {
            $this->pretExterne = false;
            $this->direction_emprunteuse = '';
            $this->contact_emprunteur = '';
        }
    }

    public function updatedPretExterne(): void
    {
        if (! $this->pretExterne) {
            $this->direction_emprunteuse = '';
            $this->contact_emprunteur = '';
        }
    }

    public function ouvrirCreation(): void
    {
        $this->reinitialiser();

        // Par defaut : la veille de l'evenement au lendemain de sa fin.
        // Le materiel se retire souvent avant et se rend apres.
        $this->date_debut = $this->evenement->date_debut->copy()->subDay()->format('Y-m-d\TH:i');
        $this->date_fin   = $this->evenement->date_fin->copy()->addDay()->format('Y-m-d\TH:i');

        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $data = $this->validate();

        if ($this->pretExterne && blank($this->direction_emprunteuse)) {
            $this->addError('direction_emprunteuse', 'Indiquez la direction emprunteuse.');
            return;
        }

        try {
            app(ReservationService::class)->creer([
                'evenement_id'          => $this->evenement->id,
                'materiel_id'           => $data['materiel_id'],
                'quantite'              => $data['quantite'],
                'date_debut'            => $data['date_debut'],
                'date_fin'              => $data['date_fin'],
                'demandeur_id'          => $this->agentConnecte()->id,
                'direction_emprunteuse' => $this->pretExterne ? $data['direction_emprunteuse'] : null,
                'contact_emprunteur'    => $this->pretExterne ? ($data['contact_emprunteur'] ?: null) : null,
            ]);
        } catch (RuntimeException $e) {
            // Message metier du service : quantite insuffisante ou pret interdit.
            $this->addError('quantite', $e->getMessage());
            return;
        }

        $this->modaleOuverte = false;
        $this->reinitialiser();

        session()->flash('message', 'Demande de reservation enregistree.');
    }

    // --- Validation par le chef de service ou un responsable ---

    public function valider(int $id): void
    {
        $this->authorize('valider-reservation');

        $reservation = Reservation::findOrFail($id);

        try {
            app(ReservationService::class)->valider($reservation, $this->agentConnecte()->id);
        } catch (RuntimeException $e) {
            session()->flash('erreur', $e->getMessage());
            return;
        }

        session()->flash('message', 'Reservation validee.');
    }

    public function ouvrirRefus(int $id): void
    {
        $this->authorize('valider-reservation');

        $this->refusId = $id;
        $this->motif_refus = '';
    }

    public function refuser(): void
    {
        $this->authorize('valider-reservation');

        $this->validate(['motif_refus' => 'required|string|min:5'], [
            'motif_refus.required' => 'Indiquez le motif du refus.',
            'motif_refus.min'      => 'Le motif doit etre explicite.',
        ]);

        Reservation::findOrFail($this->refusId)->update([
            'statut'        => StatutReservation::Refusee,
            'validateur_id' => $this->agentConnecte()->id,
            'valide_le'     => now(),
            'motif_refus'   => $this->motif_refus,
        ]);

        $this->refusId = null;
        $this->motif_refus = '';

        session()->flash('message', 'Reservation refusee.');
    }

    public function confirmerSuppression(int $id): void
    {
        $this->suppressionId = $id;
    }

    public function supprimer(): void
    {
        $reservation = Reservation::findOrFail($this->suppressionId);

        // Garde-fou : une reservation deja sortie ne se supprime pas,
        // elle laisserait du materiel en circulation sans trace.
        if ($reservation->mouvements()->exists()) {
            $this->suppressionId = null;
            session()->flash('erreur',
                'Cette reservation a des mouvements enregistres : elle ne peut plus etre supprimee.');
            return;
        }

        $reservation->delete();
        $this->suppressionId = null;

        session()->flash('message', 'Reservation supprimee.');
    }

    private function reinitialiser(): void
    {
        $this->reset([
            'reservationId', 'materiel_id', 'date_debut', 'date_fin',
            'pretExterne', 'direction_emprunteuse', 'contact_emprunteur',
        ]);
        $this->quantite = 1;
        $this->resetValidation();
    }

    /** L'agent lie au compte connecte. */
    private function agentConnecte(): Agent
    {
        return auth()->user()->agent
            ?? Agent::firstOrFail();   // A AFFINER : compte sans agent rattache
    }

    public function render()
    {
        return view('livewire.reservations.reservations-evenement', [
            'reservations' => $this->evenement->reservations()
                ->with(['materiel.categorie', 'demandeur', 'validateur'])
                ->orderBy('date_debut')
                ->get(),

            'materiels' => Materiel::disponible()
                ->with('categorie')
                ->orderBy('designation')
                ->get(),
        ]);
    }
}
