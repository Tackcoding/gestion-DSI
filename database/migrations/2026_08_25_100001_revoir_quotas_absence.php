<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types_absence', function (Blueprint $table) {
            $table->decimal('quota_annuel', 5, 1)->nullable()->after('decompte_solde');
            $table->decimal('duree_max_par_demande', 4, 1)->nullable()->after('quota_annuel');
        });

        Schema::table('droits_conges', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->after('agent_id')
                  ->constrained('types_absence')->cascadeOnDelete();
            $table->decimal('jours_accordes', 5, 1)->default(0)->after('annee');
        });

        // MySQL s'appuie sur l'index unique pour la cle etrangere agent_id :
        // il faut retirer la contrainte avant de pouvoir toucher a l'index.
        Schema::table('droits_conges', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('droits_conges', function (Blueprint $table) {
            $table->dropUnique('droits_conges_agent_id_annee_unique');
            $table->unique(['agent_id', 'annee', 'type_id']);
            $table->foreign('agent_id')->references('id')->on('agents')->cascadeOnDelete();
        });

        Schema::table('droits_conges', function (Blueprint $table) {
            $table->dropColumn(['jours_bloc', 'jours_fil_de_eau']);
        });

        Schema::table('types_absence', function (Blueprint $table) {
            $table->dropColumn('quota_type');
        });
    }

    public function down(): void
    {
        // Migration structurelle : retour arriere non pris en charge.
    }
};