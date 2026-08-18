<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // L'INTENTION d'utiliser du materiel. A distinguer du mouvement physique.
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenements')->cascadeOnDelete();
            $table->foreignId('materiel_id')->constrained('materiels')->restrictOnDelete();
            $table->unsignedSmallInteger('quantite')->default(1);
            // Dates propres a la reservation, pas heritees de l'evenement :
            // on retire souvent le materiel la veille et on le rend le lendemain.
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('statut', 20)->default('demandee');
            $table->foreignId('demandeur_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('validateur_id')->nullable()
                  ->constrained('agents')->nullOnDelete();
            $table->dateTime('valide_le')->nullable();
            $table->text('motif_refus')->nullable();
            $table->timestamps();

            // Index cle pour la detection de chevauchement
            $table->index(['materiel_id', 'date_debut', 'date_fin']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
