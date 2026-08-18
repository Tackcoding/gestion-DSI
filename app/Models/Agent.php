<?php

namespace App\Models;

use App\Enums\StatutDemandeAbsence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'im', 'nom', 'prenom', 'fonction_id', 'service_id',
        'user_id', 'telephone', 'actif',
    ];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    // --- Relations ---

    public function fonction(): BelongsTo
    {
        return $this->belongsTo(Fonction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function couvertures(): BelongsToMany
    {
        return $this->belongsToMany(Couverture::class, 'couverture_agent')
                    ->withPivot('role_sur_couverture')
                    ->withTimestamps();
    }

    public function demandesAbsence(): HasMany
    {
        return $this->hasMany(DemandeAbsence::class);
    }

    public function droitsConges(): HasMany
    {
        return $this->hasMany(DroitConge::class);
    }

    // --- Accesseurs ---

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->nom} {$this->prenom}");
    }

    // --- Scopes ---

    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('actif', true);
    }

    /**
     * Agents disponibles sur une periode : ni absence validee, ni couverture.
     * "En mission" n'est jamais saisi, il est deduit de couverture_agent.
     */
    public function scopeDisponibles(Builder $query, string $debut, string $fin): Builder
    {
        return $query->actifs()
            ->whereDoesntHave('demandesAbsence', function (Builder $q) use ($debut, $fin) {
                $q->where('statut', StatutDemandeAbsence::Validee)
                  ->where('date_debut', '<=', $fin)
                  ->where('date_fin', '>=', $debut);
            })
            ->whereDoesntHave('couvertures', function (Builder $q) use ($debut, $fin) {
                $q->whereBetween('date', [$debut, $fin]);
            });
    }
}
