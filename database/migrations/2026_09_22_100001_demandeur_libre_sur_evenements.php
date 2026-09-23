<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Demande du directeur : le demandeur d'un evenement est saisi librement.
 * Ce n'est pas forcement un agent de la direction (cabinet, autre
 * direction, partenaire exterieur).
 *
 * Les demandeurs deja saisis sont recopies en texte avant la suppression
 * du lien vers la table agents : aucun evenement ne perd son demandeur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            // Nullable en base, obligatoire dans le formulaire (voir ListeEvenements).
            $table->string('demandeur', 255)->nullable()->after('statut');
        });

        DB::table('evenements')
            ->join('agents', 'agents.id', '=', 'evenements.demandeur_id')
            ->select('evenements.id', 'agents.nom', 'agents.prenom')
            ->orderBy('evenements.id')
            ->get()
            ->each(function ($ligne) {
                DB::table('evenements')
                    ->where('id', $ligne->id)
                    ->update(['demandeur' => trim("{$ligne->nom} {$ligne->prenom}")]);
            });

        Schema::table('evenements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demandeur_id');
        });
    }

    public function down(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->foreignId('demandeur_id')->nullable()->after('statut')
                  ->constrained('agents')->restrictOnDelete();
        });

        // Retour arriere au mieux : on relie les demandeurs qui correspondent
        // exactement au nom complet d'un agent ; les autres restent vides.
        $agents = DB::table('agents')->get(['id', 'nom', 'prenom'])
            ->mapWithKeys(fn ($a) => [trim("{$a->nom} {$a->prenom}") => $a->id]);

        DB::table('evenements')->whereNotNull('demandeur')->get(['id', 'demandeur'])
            ->each(function ($e) use ($agents) {
                if ($id = $agents[$e->demandeur] ?? null) {
                    DB::table('evenements')->where('id', $e->id)->update(['demandeur_id' => $id]);
                }
            });

        Schema::table('evenements', function (Blueprint $table) {
            $table->dropColumn('demandeur');
        });
    }
};
