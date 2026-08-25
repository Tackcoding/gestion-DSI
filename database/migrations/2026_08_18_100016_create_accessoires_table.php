<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Accessoires rattaches a un materiel : batterie, chargeur, souris...
        // Table separee et non champ texte, parce que le depositaire
        // constate physiquement leur retour.
        Schema::create('accessoires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materiel_id')->constrained('materiels')->cascadeOnDelete();
            $table->string('libelle', 150);
            $table->unsignedSmallInteger('quantite')->default(1);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index('materiel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessoires');
    }
};
