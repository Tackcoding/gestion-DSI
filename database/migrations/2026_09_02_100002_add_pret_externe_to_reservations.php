<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pret a une autre direction. Autorise uniquement pour les supports
     * de communication (categorie COMM) : roll-up, oriflamme, cubes.
     * Le materiel audiovisuel et informatique ne sort pas de la direction.
     *
     * Un simple champ texte suffit : l'application n'a pas a tenir
     * l'annuaire des directions du ministere.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('direction_emprunteuse', 255)->nullable()->after('demandeur_id');
            $table->string('contact_emprunteur', 150)->nullable()->after('direction_emprunteuse');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['direction_emprunteuse', 'contact_emprunteur']);
        });
    }
};
