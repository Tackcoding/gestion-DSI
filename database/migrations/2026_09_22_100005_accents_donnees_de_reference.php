<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remet les accents dans les donnees de reference deja en base.
 * Les seeders ont ete corriges de la meme facon : une base neuve est
 * creee directement avec les bons libelles.
 */
return new class extends Migration
{
    /** [table, colonne, ancienne valeur, nouvelle valeur] */
    private array $corrections = [
        ['types_absence', 'libelle', 'Conge annuel',            'Congé annuel'],
        ['types_absence', 'libelle', 'Absence non justifiee',   'Absence non justifiée'],
        ['fonctions',     'libelle', 'Charge de Communication', 'Chargé(e) de communication'],
        ['fonctions',     'libelle', 'Secretaire qualifie',     'Secrétaire qualifié(e)'],
        ['fonctions',     'libelle', 'Depositaire comptable',   'Dépositaire comptable'],
        ['materiels',     'designation', 'Appareil photo numerique', 'Appareil photo numérique'],
        ['materiels',     'description', 'Support beton',           'Support béton'],
        ['materiels',     'description',
            'Intel Core i5 13e generation, SSD 1 To, RAM 16 Go, carte graphique 4 Go',
            'Intel Core i5 13e génération, SSD 1 To, RAM 16 Go, carte graphique 4 Go'],
    ];

    public function up(): void
    {
        foreach ($this->corrections as [$table, $colonne, $avant, $apres]) {
            DB::table($table)->where($colonne, $avant)->update([$colonne => $apres]);
        }
    }

    public function down(): void
    {
        foreach ($this->corrections as [$table, $colonne, $avant, $apres]) {
            DB::table($table)->where($colonne, $apres)->update([$colonne => $avant]);
        }
    }
};
