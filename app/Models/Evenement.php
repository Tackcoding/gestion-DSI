<?php

namespace App\Models;

use App\Enums\StatutEvenement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evenement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'intitule', 'description', 'lieu',
        'date_debut', 'date_fin', 'statut', 'demandeur_id',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin'   => 'date',
            'statut'     => StatutEvenement::class,
        ];
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'demandeur_id');
    }

    public function couvertures(): HasMany
    {
        return $this->hasMany(Couverture::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
