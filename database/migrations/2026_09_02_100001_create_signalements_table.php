<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perte ou casse de materiel : procedure formelle, avec visa du directeur.
     * Le champ observation d'un mouvement ne suffit pas : c'est une trace,
     * pas un acte engageant une responsabilite.
     */
    public function up(): void
    {
        Schema::create('signalements', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->string('type', 20);              // perte | casse
            $table->foreignId('mouvement_id')->nullable()
                  ->constrained('mouvements')->nullOnDelete();
            $table->foreignId('materiel_id')->constrained('materiels')->restrictOnDelete();
            $table->foreignId('accessoire_id')->nullable()
                  ->constrained('accessoires')->nullOnDelete();
            $table->unsignedSmallInteger('quantite')->default(1);
            $table->date('date_constat');

            // Qui a constate, qui est mis en cause
            $table->foreignId('constate_par_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('agent_responsable_id')->nullable()
                  ->constrained('agents')->nullOnDelete();

            $table->text('circonstances');
            $table->text('suite_donnee')->nullable();

            $table->string('statut', 20)->default('brouillon');  // brouillon | vise | classe

            // Visa du directeur : c'est lui qui rend le document opposable.
            $table->foreignId('vise_par_id')->nullable()
                  ->constrained('agents')->nullOnDelete();
            $table->dateTime('vise_le')->nullable();
            $table->text('observation_visa')->nullable();

            $table->timestamps();

            $table->index(['statut', 'date_constat']);
            $table->index('materiel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
