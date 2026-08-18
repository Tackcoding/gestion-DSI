<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case Agent          = 'agent';
    case ChefService    = 'chef_service';
    case Directeur      = 'directeur';
    case Depositaire    = 'depositaire';
    case Administrateur = 'administrateur';

    public function libelle(): string
    {
        return match ($this) {
            self::Agent          => 'Agent',
            self::ChefService    => 'Chef de service',
            self::Directeur      => 'Directeur',
            self::Depositaire    => 'Dépositaire comptable',
            self::Administrateur => 'Administrateur',
        };
    }

    /** A ajuster une fois les niveaux de validation confirmes par la DSI. */
    public function peutValiderAbsence(): bool
    {
        return in_array($this, [self::ChefService, self::Directeur, self::Administrateur], true);
    }

    public function peutValiderReservation(): bool
    {
        return in_array($this, [self::Depositaire, self::Directeur, self::Administrateur], true);
    }
}
