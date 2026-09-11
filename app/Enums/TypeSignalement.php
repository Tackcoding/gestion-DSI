<?php

namespace App\Enums;

enum TypeSignalement: string
{
    case Perte = 'perte';
    case Casse = 'casse';

    public function libelle(): string
    {
        return match ($this) {
            self::Perte => 'Perte',
            self::Casse => 'Casse ou deterioration',
        };
    }

    /** Intitule du document genere. */
    public function intitule(): string
    {
        return match ($this) {
            self::Perte => 'PROCES-VERBAL DE PERTE DE MATERIEL',
            self::Casse => 'PROCES-VERBAL DE DETERIORATION DE MATERIEL',
        };
    }
}
