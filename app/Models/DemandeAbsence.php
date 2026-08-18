<?php

namespace App\Models;

use App\Enums\StatutDemandeAbsence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeAbsence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'demandes_absence';

    protected $fillable = [
        'agent_id', 'type_id', 'date_debut', 'date_fin', 'demi_journee',
        'nb_jours', 'motif', 'origine', 'statut',
        'validateur_id', 'valide_le', 'motif_refus', 'justificatif_path',
    ];

    protected function casts(): array
    {
        return [
            'date_debut'   => 'date',
            'date_fin'     => 'date',
            'demi_journee' => 'boolean',
            'nb_jours'     => 'decimal:1',
            'valide_le'    => 'datetime',
            'statut'       => StatutDemandeAbsence::class,
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeAbsence::class, 'type_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'validateur_id');
    }

    public function scopeValidees(Builder $query): Builder
    {
        return $query->where('statut', StatutDemandeAbsence::Validee);
    }

    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->where('statut', StatutDemandeAbsence::Demandee);
    }

    public function scopeChevauchant(Builder $query, string $debut, string $fin): Builder
    {
        return $query->where('date_debut', '<=', $fin)
                     ->where('date_fin', '>=', $debut);
    }
}
