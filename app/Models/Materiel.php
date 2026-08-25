<?php

namespace App\Models;

use App\Enums\EtatMateriel;
use App\Enums\StatutReservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materiel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiels';

    protected $fillable = [
        'categorie_id', 'designation', 'description',
        'marque', 'modele', 'numero_serie', 'code_inventaire',
        'quantite_totale', 'etat', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'etat'  => EtatMateriel::class,
            'actif' => 'boolean',
        ];
    }

    // --- Relations ---

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieMateriel::class, 'categorie_id');
    }

    public function accessoires(): HasMany
    {
        return $this->hasMany(Accessoire::class)->where('actif', true);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // --- Helpers ---

    /**
     * Un materiel identifie individuellement (numero de serie ou code
     * d'inventaire) est unitaire : appareil photo, ordinateur portable.
     * Les supports de communication restent geres en quantite.
     */
    public function estUnitaire(): bool
    {
        return filled($this->numero_serie) || filled($this->code_inventaire);
    }

    public function libelleComplet(): string
    {
        return trim(implode(' ', array_filter([
            $this->designation, $this->marque, $this->modele,
        ])));
    }

    // --- Scopes ---

    public function scopeDisponible(Builder $query): Builder
    {
        return $query->where('actif', true)
                     ->where('etat', '!=', EtatMateriel::HorsService);
    }

    public function quantiteReservee(string $debut, string $fin): int
    {
        return (int) $this->reservations()
            ->where('statut', StatutReservation::Validee)
            ->where('date_debut', '<', $fin)
            ->where('date_fin', '>', $debut)
            ->sum('quantite');
    }
}
