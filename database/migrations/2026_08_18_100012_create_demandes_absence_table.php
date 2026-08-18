<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_absence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('types_absence')->restrictOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('demi_journee')->default(false);
            $table->decimal('nb_jours', 4, 1);
            $table->text('motif')->nullable();
            // Distingue la demande faite par l'agent du constat pose par
            // un responsable (absence non justifiee), qui n'a pas de workflow.
            $table->string('origine', 15)->default('demande'); // demande | constat
            $table->string('statut', 20)->default('demandee');
            $table->foreignId('validateur_id')->nullable()
                  ->constrained('agents')->nullOnDelete();
            $table->dateTime('valide_le')->nullable();
            $table->text('motif_refus')->nullable();
            $table->string('justificatif_path', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index cle pour le calcul de disponibilite d'un agent
            $table->index(['agent_id', 'date_debut', 'date_fin']);
            $table->index('statut');
            $table->index('type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_absence');
    }
};
