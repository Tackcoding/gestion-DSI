@php
    $agent = $demande->agent;

    // Nombre de jours sans zero inutile : 2 et non 2,0 ; 0,5 pour une demi-journee
    $jours = rtrim(rtrim(number_format((float) $demande->nb_jours, 1, ',', ''), '0'), ',');

    // Rubriques du formulaire papier : la rubrique retenue est en gras,
    // les autres sont barrees, comme on raye la mention inutile a la main.
    $rubriques = [
        'conge_annuel' => 'CONGÉ ANNUEL',
        'fraction'     => 'FRACTION DE CONGÉ',
        'permission'   => 'PERMISSION / AUTORISATION D’ABSENCE',
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Demande — {{ $agent->nom }} — {{ $demande->date_debut->format('d/m/Y') }}</title>
    <style>
        /* Format du formulaire papier : A5 a l'italienne (demi-feuille A4) */
        @page { margin: 7mm 12mm 6mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.2pt;
            line-height: 1.2;
            color: #000;
        }

        /* En-tete administratif */
        .entete {
            width: 60%;
            text-align: center;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .entete .trait {
            width: 24mm;
            margin: 1mm auto 1.2mm;
            border-bottom: 0.6pt dashed #000;
        }

        /* Bloc "Demande" a droite */
        table.demande {
            margin: 0.5mm 0 0 47%;
            border-collapse: collapse;
            text-transform: uppercase;
        }
        table.demande td { padding: 0.1mm 0; vertical-align: top; }
        table.demande td.titre { padding-right: 2mm; white-space: nowrap; }
        .barree  { text-decoration: line-through; color: #444; }
        .retenue { font-weight: bold; }
        td.annee-valeur {
            border-bottom: 0.8pt solid #000;
            width: 36mm;
            text-align: center;
            font-weight: bold;
        }

        /* Lignes a pointilles */
        table.ligne {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.9mm;
        }
        table.ligne td {
            padding: 0;
            vertical-align: bottom;
        }
        td.libelle {
            width: 1%;
            white-space: nowrap;
            padding-right: 1.5mm;
        }
        td.valeur {
            border-bottom: 0.8pt dotted #000;
            padding-left: 2mm;
            padding-right: 1mm;
            padding-bottom: 0.2mm;
            font-weight: bold;
        }
        td.suite { width: 4mm; }

        .separation {
            margin-top: 2.5mm;
            border-top: 0.6pt dashed #777;
        }

        table.pied {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.2mm;
        }
        table.pied td { vertical-align: top; padding: 0; }
        .lieu-date { padding-left: 52% !important; }
        .signature {
            width: 48mm;
            margin-top: 8mm;
            border-bottom: 0.8pt solid #000;
        }
    </style>
</head>
<body>

    {{-- En-tête --}}
    <div class="entete">
        Ministère de l’Industrialisation<br>
        et du Développement du Secteur Privé
        <div class="trait"></div>
        Secrétariat Général
        <div class="trait"></div>
    </div>

    <table class="ligne" style="width: 82%; margin-left: 5mm; margin-top: 0.5mm;">
        <tr>
            <td class="libelle">DIRECTION :&nbsp;</td>
            <td class="valeur" style="font-weight: normal;">{{ $agent->service?->libelle }}</td>
        </tr>
    </table>

    {{-- Nature de la demande --}}
    <table class="demande">
        @foreach ($rubriques as $code => $libelle)
            <tr>
                @if ($loop->first)
                    <td class="titre" rowspan="{{ count($rubriques) }}">Demande :</td>
                @endif
                <td class="{{ $code === $nature ? 'retenue' : 'barree' }}">{{ $libelle }}</td>
            </tr>
        @endforeach
    </table>

    <table class="demande" style="margin-top: 1mm;">
        <tr>
            <td class="titre">Année :</td>
            <td class="annee-valeur">{{ $demande->date_debut->format('Y') }}</td>
        </tr>
    </table>

    {{-- Identité et carrière --}}
    <table class="ligne" style="margin-top: 2.5mm;">
        <tr>
            <td class="libelle">Nom et Prénom(s) :&nbsp;</td>
            <td class="valeur">{{ $agent->nom }} {{ $agent->prenom }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Matricule :&nbsp;</td>
            <td class="valeur" style="width: 28%;">{{ $agent->im }}</td>
            <td class="suite"></td>
            <td class="libelle">Grade :&nbsp;</td>
            <td class="valeur">{{ $agent->grade }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Classe :&nbsp;</td>
            <td class="valeur" style="width: 30%;">{{ $agent->classe }}</td>
            <td class="suite"></td>
            <td class="libelle">Échelon :&nbsp;</td>
            <td class="valeur" style="width: 24%;">{{ $agent->echelon }}</td>
            <td class="suite"></td>
            <td class="libelle">Indice :&nbsp;</td>
            <td class="valeur">{{ $agent->indice }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">En service :&nbsp;</td>
            <td class="valeur" style="width: 62%;">{{ $agent->service?->libelle }}</td>
            <td class="suite"></td>
            <td class="libelle">Chapitre :&nbsp;</td>
            <td class="valeur">{{ $agent->chapitre }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Date d’entrée dans l’Administration :&nbsp;</td>
            <td class="valeur">{{ $agent->date_entree_administration?->format('d/m/Y') }}</td>
        </tr>
    </table>

    {{-- La demande elle-même --}}
    <table class="ligne">
        <tr>
            <td class="libelle">Lieu de Jouissance :&nbsp;</td>
            <td class="valeur">{{ $demande->lieu_jouissance }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Nombre de jour demandé :&nbsp;</td>
            <td class="valeur">{{ $jours }} {{ (float) $demande->nb_jours > 1 ? 'jours' : 'jour' }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Date de départ :&nbsp;</td>
            <td class="valeur">
                {{ $demande->date_debut->format('d/m/Y') }}{{ $demande->demi_journee ? ' (demi-journée)' : '' }}
            </td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Motif de la demande :&nbsp;</td>
            <td class="valeur">{{ $demande->motif }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="libelle">Adresse où l’on peut joindre le bénéficiaire en cas de besoin :&nbsp;</td>
            <td class="valeur">{{ $demande->adresse_contact }}</td>
        </tr>
    </table>

    <table class="ligne">
        <tr>
            <td class="valeur" style="width: 52%;"></td>
            <td class="suite"></td>
            <td class="libelle">Contact :&nbsp;</td>
            <td class="valeur">{{ $demande->contact ?: $agent->telephone }}</td>
        </tr>
    </table>

    {{-- Lieu, date et signature --}}
    <div class="separation"></div>

    <table class="pied">
        <tr>
            <td class="lieu-date">
                Antananarivo, le {{ $demande->created_at?->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td><div class="signature"></div></td>
        </tr>
    </table>

</body>
</html>
