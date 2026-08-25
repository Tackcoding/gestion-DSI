<?php

namespace App\Models;

use App\Enums\EtatMateriel;
use App\Enums\TypeMouvement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mouvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'type', 'quantite', 'date_mouvement',
        'agent_id', 'etat_constate', 'observation',
    ];

    protected function casts(): array
    {
        return [
            'type'           => TypeMouvement::class,
            'date_mouvement' => 'datetime',
            'etat_constate'  => EtatMateriel::class,
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /** Constat accessoire par accessoire. */
    public function accessoires(): BelongsToMany
    {
        return $this->belongsToMany(Accessoire::class, 'mouvement_accessoire')
                    ->withPivot('present', 'etat_constate', 'observation')
                    ->withTimestamps();
    }

    /** Accessoires constates absents sur ce mouvement. */
    public function accessoiresManquants()
    {
        return $this->accessoires()->wherePivot('present', false)->get();
    }
}
