<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Codes d'acces : le directeur ou l'administrateur genere un code pour un
 * agent ; l'agent l'utilise une seule fois pour choisir lui-meme son e-mail
 * et son mot de passe. Le meme mecanisme sert au mot de passe oublie
 * (le serveur n'envoie pas d'e-mails).
 *
 * Le code n'est jamais stocke en clair : seule son empreinte SHA-256 l'est.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codes_activation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('code_hash', 64)->unique();
            $table->string('role', 30);
            $table->dateTime('expire_le');
            $table->dateTime('utilise_le')->nullable();
            $table->foreignId('cree_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agent_id', 'utilise_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codes_activation');
    }
};
