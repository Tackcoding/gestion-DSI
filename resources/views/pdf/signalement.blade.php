<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $signalement->reference }}</title>
    <style>
        
        @page { margin: 18mm 20mm 22mm; }

        body {
            font-family: Arial, "Liberation Sans", sans-serif;
            font-size: 10.5pt;
            line-height: 1.4;
            color: #1D1D1B;
        }

        
        .entete { width: 100%; margin-bottom: 4mm; }
        .entete td { vertical-align: middle; }
        .entete .sceau  { width: 32%; text-align: left; }
        .entete .vide   { width: 36%; }
        .entete .logo   { width: 32%; text-align: right; }
        .entete img     { height: 16mm; }

        .filet { border-bottom: 0.75pt solid #C9A227; margin-bottom: 7mm; }

        h1 {
            font-size: 14pt;
            color: #1D561B;
            text-align: center;
            margin: 0 0 2mm;
            letter-spacing: 0.5pt;
        }

        .reference {
            text-align: center;
            font-size: 9pt;
            color: #575756;
            margin-bottom: 8mm;
        }

        .bandeau {
            background: #EBD793;
            color: #1D561B;
            font-weight: bold;
            padding: 2mm 3mm;
            margin: 6mm 0 3mm;
            font-size: 10pt;
        }

        table.fiche { width: 100%; border-collapse: collapse; }
        table.fiche td { padding: 1.5mm 0; vertical-align: top; }
        table.fiche td.libelle { width: 45mm; color: #575756; }

        .encadre {
            border: 0.5pt solid #9D9D9C;
            padding: 3mm;
            min-height: 20mm;
        }

        .signatures { width: 100%; margin-top: 12mm; }
        .signatures td { width: 50%; padding-top: 18mm; vertical-align: bottom; }
        .signatures .ligne { border-top: 0.5pt solid #1D1D1B; padding-top: 1.5mm; font-size: 9pt; }

        .pied {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8.5pt;
            color: #575756;
            border-top: 0.5pt solid #C6C6C6;
            padding-top: 2mm;
        }
    </style>
</head>
<body>

    <table class="entete">
        <tr>
            <td class="sceau">
                <img src="{{ public_path('img/sceau-republique-400.png') }}"
                     alt="Repoblikan'i Madagasikara">
            </td>
            <td class="vide"></td>
            <td class="logo">
                <img src="{{ public_path('img/midsp-logo-600.png') }}"
                     alt="MIDSP — Ministère de l'Industrialisation et du Développement du Secteur Privé">
            </td>
        </tr>
    </table>
    <div class="filet"></div>

    <h1>{{ $signalement->type->intitule() }}</h1>
    <p class="reference">
        Référence {{ $signalement->reference }} &middot;
        établi le {{ $signalement->created_at->format('d/m/Y') }}
    </p>

    <div class="bandeau">1. Matériel concerné</div>
    <table class="fiche">
        <tr>
            <td class="libelle">Désignation</td>
            <td><strong>{{ $signalement->objet() }}</strong></td>
        </tr>
        @if ($signalement->materiel->code_inventaire)
            <tr>
                <td class="libelle">Code d'inventaire</td>
                <td>{{ $signalement->materiel->code_inventaire }}</td>
            </tr>
        @endif
        @if ($signalement->materiel->numero_serie)
            <tr>
                <td class="libelle">Numéro de série</td>
                <td>{{ $signalement->materiel->numero_serie }}</td>
            </tr>
        @endif
        <tr>
            <td class="libelle">Quantité</td>
            <td>{{ $signalement->quantite }}</td>
        </tr>
        <tr>
            <td class="libelle">Nature du fait</td>
            <td>{{ $signalement->type->libelle() }}</td>
        </tr>
    </table>

    <div class="bandeau">2. Constat</div>
    <table class="fiche">
        <tr>
            <td class="libelle">Date du constat</td>
            <td>{{ $signalement->date_constat->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="libelle">Constaté par</td>
            <td>
                {{ $signalement->constatePar->nom }} {{ $signalement->constatePar->prenom }}
                — {{ $signalement->constatePar->fonction->libelle }}
            </td>
        </tr>
        @if ($signalement->agentResponsable)
            <tr>
                <td class="libelle">Agent détenteur</td>
                <td>
                    {{ $signalement->agentResponsable->nom }}
                    {{ $signalement->agentResponsable->prenom }}
                </td>
            </tr>
        @endif
        @if ($signalement->mouvement)
            <tr>
                <td class="libelle">Mouvement d'origine</td>
                <td>
                    {{ $signalement->mouvement->type->libelle() }} du
                    {{ $signalement->mouvement->date_mouvement->format('d/m/Y') }}
                </td>
            </tr>
        @endif
    </table>

    <div class="bandeau">3. Circonstances</div>
    <div class="encadre">{{ $signalement->circonstances }}</div>

    @if ($signalement->suite_donnee)
        <div class="bandeau">4. Suite donnée</div>
        <div class="encadre">{{ $signalement->suite_donnee }}</div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <div class="ligne">
                    Le dépositaire comptable<br>
                    {{ $signalement->constatePar->nom }} {{ $signalement->constatePar->prenom }}
                </div>
            </td>
            <td style="padding-left: 10mm;">
                <div class="ligne">
                    Visa du Directeur<br>
                    @if ($signalement->visePar)
                        {{ $signalement->visePar->nom }} {{ $signalement->visePar->prenom }}<br>
                        <em>Visé le {{ $signalement->vise_le->format('d/m/Y') }}</em>
                    @else
                        <em>En attente</em>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="pied">
        {{ $signalement->reference }} &middot;
        MIDSP — Direction de la Veille Économique et de la Communication
    </div>

</body>
</html>
