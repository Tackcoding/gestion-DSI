<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeAbsence extends Model
{
    use HasFactory;

    protected $table = 'types_absence';

    protected $fillable = [
        'code', 'libelle', 'decompte_solde', 'quota_annuel',
        'duree_max_par_demande', 'necessite_justificatif', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'decompte_solde'         => 'boolean',
            'necessite_justificatif' => 'boolean',
            'actif'                  => 'boolean',
            'quota_annuel'           => 'decimal:1',
            'duree_max_par_demande'  => 'decimal:1',
        ];
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeAbsence::class, 'type_id');
    }

    public function droits(): HasMany
    {
        return $this->hasMany(DroitConge::class, 'type_id');
    }

    /** Types soumis a un quota annuel : conge annuel et permission. */
    public function aUnQuota(): bool
    {
        return $this->quota_annuel !== null;
    }

    public function aUnPlafondParDemande(): bool
    {
        return $this->duree_max_par_demande !== null;
    }

    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('actif', true);
    }
}
