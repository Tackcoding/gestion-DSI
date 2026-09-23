<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Demande du directeur : deux categories de materiel seulement.
 *   COMM -> "Visuel"                        (roll-up, oriflamme, lettrine, cubes, light box)
 *   INFO -> "Informatique et audiovisuel"   (ordinateurs, appareil photo, objectif)
 * L'ancienne categorie AUDIO est fusionnee dans INFO puis supprimee.
 *
 * Les codes COMM et INFO sont conserves : la regle du pret a une autre
 * direction repose sur le code COMM (ReservationService::CATEGORIE_PRETABLE).
 *
 * Sur une base neuve, la table est encore vide ici : ce sont les seeders
 * qui creent directement les deux categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories_materiel')->where('code', 'COMM')
            ->update(['libelle' => 'Visuel', 'updated_at' => now()]);

        DB::table('categories_materiel')->where('code', 'INFO')
            ->update(['libelle' => 'Informatique et audiovisuel', 'updated_at' => now()]);

        $audio = DB::table('categories_materiel')->where('code', 'AUDIO')->value('id');
        $info  = DB::table('categories_materiel')->where('code', 'INFO')->value('id');

        if ($audio && $info) {
            DB::table('materiels')->where('categorie_id', $audio)->update(['categorie_id' => $info]);
        }

        if ($audio) {
            DB::table('categories_materiel')->where('id', $audio)->delete();
        }
    }

    public function down(): void
    {
        DB::table('categories_materiel')->where('code', 'COMM')
            ->update(['libelle' => 'Support de communication', 'updated_at' => now()]);

        DB::table('categories_materiel')->where('code', 'INFO')
            ->update(['libelle' => 'Materiel informatique', 'updated_at' => now()]);

        if (! DB::table('categories_materiel')->where('code', 'AUDIO')->exists()) {
            $audio = DB::table('categories_materiel')->insertGetId([
                'code' => 'AUDIO', 'libelle' => 'Materiel audiovisuel', 'actif' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('materiels')
                ->whereIn('designation', ['Appareil photo numérique', 'Appareil photo numerique', 'Objectif'])
                ->update(['categorie_id' => $audio]);
        }
    }
};
