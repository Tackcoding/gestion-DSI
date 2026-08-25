<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materiels', function (Blueprint $table) {
            $table->foreignId('categorie_id')->nullable()->after('id')
                  ->constrained('categories_materiel')->nullOnDelete();

            // Identification individuelle : necessaire pour le materiel
            // audiovisuel et informatique (patrimoine public tracable).
            // Reste null pour les supports de communication geres en quantite.
            $table->string('marque', 100)->nullable()->after('description');
            $table->string('modele', 150)->nullable()->after('marque');
            $table->string('numero_serie', 100)->nullable()->unique()->after('modele');
            $table->string('code_inventaire', 50)->nullable()->unique()->after('numero_serie');

            $table->index('categorie_id');
        });
    }

    public function down(): void
    {
        Schema::table('materiels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categorie_id');
            $table->dropColumn(['marque', 'modele', 'numero_serie', 'code_inventaire']);
        });
    }
};
