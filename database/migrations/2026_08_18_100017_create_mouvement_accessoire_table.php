<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Constat par accessoire, a la sortie ET au retour.
        // C'est ce qui permet de repondre a "le chargeur est-il revenu ?".
        Schema::create('mouvement_accessoire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mouvement_id')->constrained('mouvements')->cascadeOnDelete();
            $table->foreignId('accessoire_id')->constrained('accessoires')->restrictOnDelete();
            $table->boolean('present');           // coche par le depositaire
            $table->string('etat_constate', 20)->nullable();
            $table->string('observation', 255)->nullable();
            $table->timestamps();

            $table->unique(['mouvement_id', 'accessoire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvement_accessoire');
    }
};
