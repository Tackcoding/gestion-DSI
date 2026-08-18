<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeAbsence extends Model
{
    use HasFactory;

    protected $table = 'types_absence';

    protected $fillable = [
        'code', 'libelle', 'decompte_solde',
        'quota_type', 'necessite_justificatif', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'decompte_solde'         => 'boolean',
            'necessite_justificatif' => 'boolean',
            'actif'                  => 'boolean',
        ];
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeAbsence::class, 'type_id');
    }
}
