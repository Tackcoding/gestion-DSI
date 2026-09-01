<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case Agent          = 'agent';
    case ChefService    = 'chef_service';
    case Depositaire    = 'depositaire';
    case Directeur      = 'directeur';
    case Administrateur = 'administrateur';

    public function libelle(): string
    {
        return match ($this) {
            self::Agent          => 'Agent',
            self::ChefService    => 'Chef de service',
            self::Depositaire    => 'Dépositaire comptable',
            self::Directeur      => 'Directeur',
            self::Administrateur => 'Administrateur',
        };
    }

    public function estResponsable(): bool
    {
        return in_array($this, [self::Directeur, self::Administrateur], true);
    }

    public function peutGererMateriel(): bool
    {
        return $this === self::Depositaire || $this->estResponsable();
    }

    public function peutGererEvenements(): bool
    {
        return $this === self::ChefService || $this->estResponsable();
    }

    public function peutValiderAbsence(): bool
    {
        return $this === self::ChefService || $this->estResponsable();
    }

    public function peutValiderReservation(): bool
    {
        return $this === self::ChefService || $this->estResponsable();
    }

    public function peutGererAgents(): bool
    {
        return $this === self::ChefService || $this->estResponsable();
    }

    public function estLectureSeule(): bool
    {
        return $this === self::Agent;
    }
}