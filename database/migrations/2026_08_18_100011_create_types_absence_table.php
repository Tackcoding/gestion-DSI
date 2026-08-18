<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table de reference : un administrateur doit pouvoir en ajouter.
        // (Contrairement aux statuts, qui sont des Enums PHP.)
        Schema::create('types_absence', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('libelle', 150);
            $table->boolean('decompte_solde')->default(true);
            $table->string('quota_type', 20)->default('aucun'); // bloc | fil_de_eau | aucun
            $table->boolean('necessite_justificatif')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_absence');
    }
};
