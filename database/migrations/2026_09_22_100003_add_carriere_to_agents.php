<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Donnees de carriere de l'agent, reprises sur le formulaire officiel de
 * demande de conge / permission (export PDF). Toutes facultatives : une
 * valeur absente laisse la ligne pointillee a remplir a la main.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('grade', 100)->nullable()->after('service_id');
            $table->string('classe', 30)->nullable()->after('grade');
            $table->string('echelon', 30)->nullable()->after('classe');
            $table->string('indice', 20)->nullable()->after('echelon');
            $table->string('chapitre', 30)->nullable()->after('indice');
            $table->date('date_entree_administration')->nullable()->after('chapitre');
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'grade', 'classe', 'echelon', 'indice', 'chapitre', 'date_entree_administration',
            ]);
        });
    }
};
