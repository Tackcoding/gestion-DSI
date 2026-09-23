{{-- Icônes tracées à la main, un seul trait, sans bibliothèque externe.
     Usage : <x-ui.icone nom="recherche" />
     Noms : recherche, plus, crayon, corbeille, oeil, chevron, fermer, coche, alerte,
            calendrier, personnes, sortie, retour, document, trombone, boite, tampon, fleche-gauche, cle --}}
@props(['nom'])
@php
$traces = [
    'recherche'     => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'plus'          => '<path d="M12 5v14M5 12h14"/>',
    'crayon'        => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m13.5 6.5 3 3"/>',
    'corbeille'     => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
    'oeil'          => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
    'chevron'       => '<path d="m6 9 6 6 6-6"/>',
    'fermer'        => '<path d="M6 6l12 12M18 6 6 18"/>',
    'coche'         => '<path d="m5 12 4.5 4.5L19 7"/>',
    'alerte'        => '<path d="M12 9v4M12 17h.01M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>',
    'calendrier'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    'personnes'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17" cy="9" r="2.5"/><path d="M15.5 14.5a5 5 0 0 1 6 4.5"/>',
    'sortie'        => '<path d="M14 5h5v5M19 5l-8 8M12 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-6"/>',
    'retour'        => '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/>',
    'document'      => '<path d="M6 3h8l5 5v13H6z"/><path d="M14 3v5h5M9 13h7M9 17h7"/>',
    'trombone'      => '<path d="m20 11-8.5 8.5a5 5 0 0 1-7-7L13 4a3.5 3.5 0 0 1 5 5l-8.5 8.5a2 2 0 0 1-3-3L15 6"/>',
    'boite'         => '<path d="M3 8h18v12H3z"/><path d="M2 4h20v4H2zM10 12h4"/>',
    'tampon'        => '<path d="M5 20h14M8 20v-3a4 4 0 0 1 8 0v3M12 4a3 3 0 0 0-3 3c0 2 3 4 3 4s3-2 3-4a3 3 0 0 0-3-3Z"/>',
    'fleche-gauche' => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
    'cle'           => '<circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 9.2-9.2M17 6l3 3M14 9l2 2"/>',
];
@endphp
<svg {{ $attributes->merge(['class' => 'icone']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $traces[$nom] ?? '' !!}</svg>
