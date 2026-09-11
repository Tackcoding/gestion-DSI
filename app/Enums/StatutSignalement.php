<?php

namespace App\Enums;

enum StatutSignalement: string
{
    case Brouillon = 'brouillon';
    case Vise      = 'vise';
    case Classe    = 'classe';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'En attente de visa',
            self::Vise      => 'Vise par le directeur',
            self::Classe    => 'Classe',
        };
    }

    /** Un signalement non vise n'a aucune valeur opposable. */
    public function estOpposable(): bool
    {
        return $this !== self::Brouillon;
    }
}
