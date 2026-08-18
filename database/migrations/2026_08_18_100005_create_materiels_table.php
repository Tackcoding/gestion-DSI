<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiels', function (Blueprint $table) {
            $table->id();
            $table->string('designation', 255);
            $table->text('description')->nullable();
            // Gestion par quantite : l'inventaire ne distingue pas les exemplaires
            // (Roll up x2, Cubes x3...). Pas de numero d'inventaire individuel.
            $table->unsignedSmallInteger('quantite_totale')->default(1);
            $table->string('etat', 20)->default('bon');
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiels');
    }
};
