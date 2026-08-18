<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DroitConge extends Model
{
    use HasFactory;

    protected $table = 'droits_conges';

    protected $fillable = ['agent_id', 'annee', 'jours_bloc', 'jours_fil_de_eau'];

    protected function casts(): array
    {
        return [
            'jours_bloc'       => 'decimal:1',
            'jours_fil_de_eau' => 'decimal:1',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    // Le solde restant n'est pas stocke : il se calcule dans CongeService.
}
