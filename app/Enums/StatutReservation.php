<?php

namespace App\Enums;

enum StatutReservation: string
{
    case Demandee = 'demandee';
    case Validee  = 'validee';
    case Refusee  = 'refusee';
    case Annulee  = 'annulee';

    public function libelle(): string
    {
        return match ($this) {
            self::Demandee => 'En attente',
            self::Validee  => 'Validée',
            self::Refusee  => 'Refusée',
            self::Annulee  => 'Annulée',
        };
    }

    /** Seules les reservations validees bloquent du stock. */
    public function bloqueStock(): bool
    {
        return $this === self::Validee;
    }
}
