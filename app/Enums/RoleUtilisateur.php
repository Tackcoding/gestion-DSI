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
        return $this->estResponsable();
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

    // --- Comptes de connexion ---

    /** Generer des codes d'acces, changer un role, desactiver un compte. */
    public function peutGererComptes(): bool
    {
        return $this->estResponsable();
    }

    /**
     * Le directeur a les memes pouvoirs que l'administrateur, sauf un :
     * il ne peut ni creer un administrateur, ni toucher a un compte
     * administrateur. Personne ne peut donner plus que ce qu'il a.
     */
    public function peutAttribuer(self $role): bool
    {
        return match ($this) {
            self::Administrateur => true,
            self::Directeur      => $role !== self::Administrateur,
            default              => false,
        };
    }

    /** @return array<int, self> */
    public function rolesAttribuables(): array
    {
        return array_values(array_filter(self::cases(), fn (self $role) => $this->peutAttribuer($role)));
    }
}
