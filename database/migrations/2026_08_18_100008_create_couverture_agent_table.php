<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Equipe mobilisee. Remplace la cellule texte "Mahery, Andri, Ando"
        // du fichier Excel, inexploitable en l'etat.
        Schema::create('couverture_agent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couverture_id')->constrained('couvertures')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('role_sur_couverture', 50)->nullable();
            $table->timestamps();

            $table->unique(['couverture_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couverture_agent');
    }
};
