<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('intitule', 255);
            $table->text('description')->nullable();
            $table->string('lieu', 255)->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('statut', 20)->default('brouillon');
            $table->foreignId('demandeur_id')->constrained('agents')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['date_debut', 'date_fin']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
