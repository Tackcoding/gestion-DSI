<?php

namespace App\Enums;

enum TypeMouvement: string
{
    case Sortie = 'sortie';
    case Retour = 'retour';

    public function libelle(): string
    {
        return match ($this) {
            self::Sortie => 'Sortie',
            self::Retour => 'Retour',
        };
    }
}
