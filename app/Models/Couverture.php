<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Couverture extends Model
{
    use HasFactory;

    protected $fillable = [
        'evenement_id', 'date', 'heure_depart', 'lieu_depart',
        'heure_retour', 'lieu_retour', 'observation',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class);
    }

    /** L'equipe mobilisee. */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'couverture_agent')
                    ->withPivot('role_sur_couverture')
                    ->withTimestamps();
    }
}
