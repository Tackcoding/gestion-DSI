<?php

namespace App\Enums;

enum StatutEvenement: string
{
    case Brouillon = 'brouillon';
    case Valide    = 'valide';
    case EnCours   = 'en_cours';
    case Termine   = 'termine';
    case Annule    = 'annule';

    public function libelle(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Valide    => 'Validé',
            self::EnCours   => 'En cours',
            self::Termine   => 'Terminé',
            self::Annule    => 'Annulé',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::Brouillon => 'gray',
            self::Valide    => 'blue',
            self::EnCours   => 'amber',
            self::Termine   => 'green',
            self::Annule    => 'red',
        };
    }
}
