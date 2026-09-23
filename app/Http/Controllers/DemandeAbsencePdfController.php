<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use Barryvdh\DomPDF\Facade\Pdf;


class DemandeAbsencePdfController extends Controller
{
    public function __invoke(DemandeAbsence $demande)
    {
        $demande->load(['agent.service', 'agent.fonction', 'type']);

        abort_unless($demande->estImprimable(), 404);

        $nomFichier = sprintf(
            'demande-%s-%s-%s.pdf',
            $demande->type->code,
            str($demande->agent->nom)->slug(),
            $demande->date_debut->format('Y-m-d')
        );

        return Pdf::loadView('pdf.demande-absence', [
                'demande' => $demande,
                'nature'  => $demande->natureFormulaire(),
            ])
            ->setPaper('a5', 'landscape')
            ->stream($nomFichier);
    }
}
