<?php

namespace App\Enums;

enum StatutDemandeAbsence: string
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

    /** Seules les absences validees rendent un agent indisponible. */
    public function rendIndisponible(): bool
    {
        return $this === self::Validee;
    }
}
