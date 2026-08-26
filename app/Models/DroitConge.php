<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DroitConge extends Model
{
    use HasFactory;

    protected $table = 'droits_conges';

    protected $fillable = ['agent_id', 'type_id', 'annee', 'jours_accordes'];

    protected function casts(): array
    {
        return ['jours_accordes' => 'decimal:1'];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeAbsence::class, 'type_id');
    }

    // Le solde restant n'est pas stocke : il se calcule dans CongeService.
}
