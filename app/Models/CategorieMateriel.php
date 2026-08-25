<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieMateriel extends Model
{
    use HasFactory;

    protected $table = 'categories_materiel';

    protected $fillable = ['code', 'libelle', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function materiels(): HasMany
    {
        return $this->hasMany(Materiel::class, 'categorie_id');
    }
}
