<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Champs propres a chaque demande, repris sur le formulaire officiel :
 * lieu de jouissance du conge, adresse et contact pendant l'absence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_absence', function (Blueprint $table) {
            $table->string('lieu_jouissance', 255)->nullable()->after('motif');
            $table->string('adresse_contact', 255)->nullable()->after('lieu_jouissance');
            $table->string('contact', 50)->nullable()->after('adresse_contact');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_absence', function (Blueprint $table) {
            $table->dropColumn(['lieu_jouissance', 'adresse_contact', 'contact']);
        });
    }
};
