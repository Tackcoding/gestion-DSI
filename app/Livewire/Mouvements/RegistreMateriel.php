<?php

namespace App\Livewire\Mouvements;

use App\Enums\EtatMateriel;
use App\Enums\StatutReservation;
use App\Enums\TypeMouvement;
use App\Models\Agent;
use App\Models\Reservation;
use App\Services\MouvementService;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

/**
 * Registre des sorties et retours de materiel.
 *
 * C'est l'ecran du depositaire comptable : il remet le materiel,
 * constate ce qui revient, accessoire par accessoire.
 */
class RegistreMateriel extends Component
{
    #[Url]
    public string $filtre = 'a_sortir';   // a_sortir | en_circulation | tout

    // --- Formulaire de mouvement ---
    public bool $modaleOuverte = false;
    public ?int $reservationId = null;
    public string $typeMouvement = 'sortie';
    public int $quantite = 1;
    public ?int $agent_id = null;
    public string $etat_constate = 'bon';
    public string $observation = '';

    /** Constat par accessoire : [id => ['present' => bool, 'observation' => string]] */
    public array $constats = [];

    protected function rules(): array
    {
        return [
            'quantite' => 'required|integer|min:1',
            'agent_id' => 'required|exists:agents,id',
            'observation' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'agent_id.required' => 'Indiquez l\'agent qui prend ou rend le materiel.',
    ];

    public function ouvrirSortie(int $reservationId): void
    {
        $this->preparerMouvement($reservationId, 'sortie');
    }

    public function ouvrirRetour(int $reservationId): void
    {
        $this->preparerMouvement($reservationId, 'retour');
    }

    private function preparerMouvement(int $reservationId, string $type): void
    {
        $reservation = Reservation::with('materiel.accessoires')->findOrFail($reservationId);

        $this->reservationId  = $reservationId;
        $this->typeMouvement  = $type;
        $this->etat_constate  = 'bon';
        $this->observation    = '';
        $this->agent_id       = auth()->user()->agent?->id;

        // Quantite proposee : ce qui reste a sortir, ou ce qui est en circulation.
        $this->quantite = $type === 'sortie'
            ? max(1, $reservation->quantite - $reservation->quantiteSortie())
            : max(1, $reservation->quantiteEnCirculation());

        // A la sortie, tous les accessoires sont coches par defaut.
        // Au retour, tout est decoche : le depositaire coche ce qu'il voit.
        $this->constats = [];
        foreach ($reservation->materiel->accessoires as $accessoire) {
            $this->constats[$accessoire->id] = [
                'present'     => $type === 'sortie',
                'observation' => '',
            ];
        }

        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $this->authorize('gerer-materiel');

        $data = $this->validate();
        $reservation = Reservation::with('materiel')->findOrFail($this->reservationId);

        $accessoires = [];
        foreach ($this->constats as $id => $constat) {
            $accessoires[$id] = [
                'present'     => (bool) ($constat['present'] ?? false),
                'observation' => $constat['observation'] ?: null,
            ];
        }

        try {
            if ($this->typeMouvement === 'sortie') {
                app(MouvementService::class)->enregistrerSortie(
                    $reservation, $data['quantite'], $data['agent_id'],
                    $accessoires, $data['observation'] ?: null,
                );
                $message = 'Sortie enregistree.';
            } else {
                app(MouvementService::class)->enregistrerRetour(
                    $reservation, $data['quantite'], $data['agent_id'],
                    $this->etat_constate, $accessoires, $data['observation'] ?: null,
                );

                $manquants = collect($accessoires)->filter(fn ($a) => ! $a['present'])->count();
                $message = $manquants > 0
                    ? "Retour enregistre : {$manquants} accessoire(s) manquant(s)."
                    : 'Retour enregistre, materiel complet.';
            }
        } catch (RuntimeException $e) {
            $this->addError('quantite', $e->getMessage());
            return;
        }

        $this->modaleOuverte = false;
        session()->flash('message', $message);
    }

    public function render()
    {
        $reservations = Reservation::query()
            ->with(['materiel.accessoires', 'evenement', 'demandeur', 'mouvements'])
            ->where('statut', StatutReservation::Validee)
            ->orderBy('date_debut')
            ->get()
            // Le filtre porte sur l'etat physique, deduit des mouvements :
            // il ne peut pas se calculer en SQL simple.
            ->filter(function (Reservation $r) {
                return match ($this->filtre) {
                    'a_sortir'       => $r->quantiteSortie() < $r->quantite,
                    'en_circulation' => $r->quantiteEnCirculation() > 0,
                    default          => true,
                };
            });

        $reservationCourante = $this->reservationId
            ? Reservation::with('materiel.accessoires')->find($this->reservationId)
            : null;

        return view('livewire.mouvements.registre-materiel', [
            'reservations'        => $reservations,
            'reservationCourante' => $reservationCourante,
            'agents'              => Agent::actifs()->orderBy('nom')->get(),
            'etats'               => EtatMateriel::cases(),
        ]);
    }
}
