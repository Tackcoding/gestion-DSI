<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeActivation extends Model
{
    protected $table = 'codes_activation';

    protected $fillable = [
        'agent_id', 'code_hash', 'role', 'expire_le', 'utilise_le', 'cree_par_id',
    ];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'role'       => RoleUtilisateur::class,
            'expire_le'  => 'datetime',
            'utilise_le' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par_id');
    }

    /** Codes encore utilisables : ni utilises, ni expires. */
    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->whereNull('utilise_le')->where('expire_le', '>', now());
    }
}
