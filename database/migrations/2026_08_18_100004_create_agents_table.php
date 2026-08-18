<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            // Nullable : un agent de la liste RH n'a pas encore d'IM.
            // En SQL, UNIQUE autorise plusieurs NULL — comportement voulu.
            $table->string('im', 20)->nullable()->unique();
            $table->string('nom', 100);
            $table->string('prenom', 150);
            $table->foreignId('fonction_id')->constrained('fonctions')->restrictOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            // Separe la personne physique du compte de connexion :
            // tout agent existe dans le systeme, tous n'ont pas de compte.
            $table->foreignId('user_id')->nullable()->unique()
                  ->constrained('users')->nullOnDelete();
            $table->string('telephone', 30)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_id', 'actif']);
            $table->index(['nom', 'prenom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
