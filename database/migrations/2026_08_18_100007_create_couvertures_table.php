<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une sortie terrain datee. Un evenement multi-jours en compte plusieurs.
        Schema::create('couvertures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->constrained('evenements')->cascadeOnDelete();
            $table->date('date');
            $table->time('heure_depart')->nullable();
            $table->string('lieu_depart', 255)->nullable();
            $table->time('heure_retour')->nullable();
            $table->string('lieu_retour', 255)->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couvertures');
    }
};
