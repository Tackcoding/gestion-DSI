<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use Barryvdh\DomPDF\Facade\Pdf;

class SignalementPdfController extends Controller
{
    public function __invoke(Signalement $signalement)
    {
        $this->authorize('gerer-materiel');

        $signalement->load([
            'materiel', 'accessoire', 'mouvement',
            'constatePar.fonction', 'agentResponsable', 'visePar',
        ]);

        return Pdf::loadView('pdf.signalement', compact('signalement'))
            ->setPaper('a4')
            ->stream("{$signalement->reference}.pdf");
    }
}
