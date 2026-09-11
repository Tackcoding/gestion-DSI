<?php

namespace App\Models;

use App\Enums\StatutSignalement;
use App\Enums\TypeSignalement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signalement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'type', 'mouvement_id', 'materiel_id', 'accessoire_id',
        'quantite', 'date_constat', 'constate_par_id', 'agent_responsable_id',
        'circonstances', 'suite_donnee', 'statut',
        'vise_par_id', 'vise_le', 'observation_visa',
    ];

    protected function casts(): array
    {
        return [
            'type'         => TypeSignalement::class,
            'statut'       => StatutSignalement::class,
            'date_constat' => 'date',
            'vise_le'      => 'datetime',
        ];
    }

    public function materiel(): BelongsTo
    {
        return $this->belongsTo(Materiel::class);
    }

    public function accessoire(): BelongsTo
    {
        return $this->belongsTo(Accessoire::class);
    }

    public function mouvement(): BelongsTo
    {
        return $this->belongsTo(Mouvement::class);
    }

    public function constatePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'constate_par_id');
    }

    public function agentResponsable(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_responsable_id');
    }

    public function visePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'vise_par_id');
    }

    /** Objet du signalement : le materiel, ou l'un de ses accessoires. */
    public function objet(): string
    {
        if ($this->accessoire) {
            return "{$this->accessoire->libelle} ({$this->materiel->designation})";
        }

        return $this->materiel->libelleComplet();
    }

    public function scopeEnAttenteDeVisa(Builder $query): Builder
    {
        return $query->where('statut', StatutSignalement::Brouillon);
    }
}
