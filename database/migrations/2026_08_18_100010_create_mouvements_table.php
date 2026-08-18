<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Le FAIT physique : sortie ou retour reellement constate.
        // Reserve != sorti. Une sortie sans retour = materiel en circulation.
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->restrictOnDelete();
            $table->string('type', 10); // sortie | retour
            $table->unsignedSmallInteger('quantite');
            $table->dateTime('date_mouvement');
            $table->foreignId('agent_id')->constrained('agents')->restrictOnDelete();
            $table->string('etat_constate', 20)->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['reservation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};
