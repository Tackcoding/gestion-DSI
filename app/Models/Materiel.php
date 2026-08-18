<?php

namespace App\Models;

use App\Enums\EtatMateriel;
use App\Enums\StatutReservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materiel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiels';

    protected $fillable = [
        'designation', 'description', 'quantite_totale', 'etat', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'etat'  => EtatMateriel::class,
            'actif' => 'boolean',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeDisponible(Builder $query): Builder
    {
        return $query->where('actif', true)
                     ->where('etat', '!=', EtatMateriel::HorsService);
    }

    /**
     * Quantite deja engagee sur une periode (reservations validees uniquement).
     * Le calcul de disponibilite reel passe par ReservationService,
     * qui pose un verrou de transaction.
     */
    public function quantiteReservee(string $debut, string $fin): int
    {
        return (int) $this->reservations()
            ->where('statut', StatutReservation::Validee)
            ->where('date_debut', '<', $fin)
            ->where('date_fin', '>', $debut)
            ->sum('quantite');
    }
}
