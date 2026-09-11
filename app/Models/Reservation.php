<?php

namespace App\Models;

use App\Enums\StatutReservation;
use App\Enums\TypeMouvement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evenement_id', 'materiel_id', 'quantite',
        'date_debut', 'date_fin', 'statut',
        'demandeur_id', 'direction_emprunteuse', 'contact_emprunteur',
        'validateur_id', 'valide_le', 'motif_refus',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin'   => 'datetime',
            'valide_le'  => 'datetime',
            'statut'     => StatutReservation::class,
        ];
    }

    // --- Relations ---

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    public function materiel(): BelongsTo
    {
        return $this->belongsTo(Materiel::class);
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'demandeur_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'validateur_id');
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(Mouvement::class);
    }

    // --- Etat physique, deduit des mouvements ---

    public function quantiteSortie(): int
    {
        return (int) $this->mouvements()
            ->where('type', TypeMouvement::Sortie)->sum('quantite');
    }

    public function quantiteRendue(): int
    {
        return (int) $this->mouvements()
            ->where('type', TypeMouvement::Retour)->sum('quantite');
    }

    /** Materiel encore en circulation sur cette reservation. */
    public function quantiteEnCirculation(): int
    {
        return $this->quantiteSortie() - $this->quantiteRendue();
    }

    /** Le materiel sort-il de la direction ? */
    public function estUnPretExterne(): bool
    {
        return filled($this->direction_emprunteuse);
    }

    // --- Scopes ---

    public function scopeValidees(Builder $query): Builder
    {
        return $query->where('statut', StatutReservation::Validee);
    }

    /** Reservations chevauchant une periode donnee. */
    public function scopeChevauchant(Builder $query, string $debut, string $fin): Builder
    {
        return $query->where('date_debut', '<', $fin)
                     ->where('date_fin', '>', $debut);
    }

    public function scopePretsExternes(Builder $query): Builder
    {
        return $query->whereNotNull('direction_emprunteuse');
    }
}
