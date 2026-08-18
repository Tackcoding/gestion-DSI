<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Support\Collection;

/**
 * Disponibilite des agents.
 *
 * C'est le point ou les deux modules de l'application se parlent :
 * un agent en absence validee ne peut pas etre mobilise sur une couverture.
 */
class DisponibiliteService
{
    public function agentsDisponibles(string $debut, string $fin): Collection
    {
        return Agent::disponibles($debut, $fin)
                    ->with(['fonction', 'service'])
                    ->orderBy('nom')
                    ->get();
    }

    public function estDisponible(Agent $agent, string $debut, string $fin): bool
    {
        return Agent::disponibles($debut, $fin)->whereKey($agent->id)->exists();
    }
}
