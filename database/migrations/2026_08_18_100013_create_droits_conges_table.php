<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Les DROITS de l'annee. Le solde restant n'est jamais stocke :
        // il se calcule a partir des demandes validees, sinon il devient faux
        // des la premiere correction retroactive.
        Schema::create('droits_conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->decimal('jours_bloc', 4, 1)->default(15.0);
            $table->decimal('jours_fil_de_eau', 4, 1)->default(15.0);
            $table->timestamps();

            $table->unique(['agent_id', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('droits_conges');
    }
};
