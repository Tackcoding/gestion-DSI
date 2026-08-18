<?php

namespace App\Enums;

enum EtatMateriel: string
{
    case Bon         = 'bon';
    case Moyen       = 'moyen';
    case HorsService = 'hors_service';

    public function libelle(): string
    {
        return match ($this) {
            self::Bon         => 'Bon état',
            self::Moyen       => 'État moyen',
            self::HorsService => 'Hors service',
        };
    }

    public function estUtilisable(): bool
    {
        return $this !== self::HorsService;
    }
}
