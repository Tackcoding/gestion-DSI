<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accessoire extends Model
{
    use HasFactory;

    protected $fillable = ['materiel_id', 'libelle', 'quantite', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function materiel(): BelongsTo
    {
        return $this->belongsTo(Materiel::class);
    }

    /** Constats de presence, a la sortie comme au retour. */
    public function mouvements(): BelongsToMany
    {
        return $this->belongsToMany(Mouvement::class, 'mouvement_accessoire')
                    ->withPivot('present', 'etat_constate', 'observation')
                    ->withTimestamps();
    }
}
