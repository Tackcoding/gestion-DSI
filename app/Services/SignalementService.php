<?php

namespace App\Services;

use App\Enums\EtatMateriel;
use App\Enums\StatutSignalement;
use App\Enums\TypeSignalement;
use App\Models\Signalement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Procedure formelle de perte ou de deterioration.
 *
 * Un signalement n'est opposable qu'une fois vise par le directeur :
 * tant qu'il est en brouillon, il n'engage personne.
 */
class SignalementService
{
    public function creer(array $data): Signalement
    {
        return DB::transaction(function () use ($data) {
            $signalement = Signalement::create($data + [
                'reference' => $this->genererReference($data['type']),
                'statut'    => StatutSignalement::Brouillon,
            ]);

            // Une casse constatee sort le materiel du stock disponible.
            // Une perte aussi : il n'est plus la.
            if ($signalement->accessoire_id === null) {
                $signalement->materiel->update(['etat' => EtatMateriel::HorsService]);
            }

            return $signalement;
        });
    }

    /** Le visa du directeur rend le document opposable. */
    public function viser(Signalement $signalement, int $directeurId, ?string $observation = null): Signalement
    {
        if ($signalement->statut !== StatutSignalement::Brouillon) {
            throw new RuntimeException('Ce signalement a deja ete vise.');
        }

        $signalement->update([
            'statut'           => StatutSignalement::Vise,
            'vise_par_id'      => $directeurId,
            'vise_le'          => now(),
            'observation_visa' => $observation,
        ]);

        return $signalement;
    }

    public function classer(Signalement $signalement, string $suiteDonnee): Signalement
    {
        if ($signalement->statut !== StatutSignalement::Vise) {
            throw new RuntimeException('Seul un signalement vise peut etre classe.');
        }

        $signalement->update([
            'statut'       => StatutSignalement::Classe,
            'suite_donnee' => $suiteDonnee,
        ]);

        return $signalement;
    }

    /**
     * Reference du proces-verbal : PV-PER-2026-001.
     * Numerotation annuelle, remise a zero chaque annee, comme un
     * registre papier.
     */
    private function genererReference(TypeSignalement|string $type): string
    {
        $type = $type instanceof TypeSignalement ? $type : TypeSignalement::from($type);
        $code = $type === TypeSignalement::Perte ? 'PER' : 'DET';
        $annee = date('Y');

        $dernier = Signalement::where('reference', 'like', "PV-{$code}-{$annee}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $numero = $dernier ? ((int) substr($dernier, -3)) + 1 : 1;

        return sprintf('PV-%s-%s-%03d', $code, $annee, $numero);
    }
}
