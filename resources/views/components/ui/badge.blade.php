{{-- Badge d'etat : un point de couleur et un mot.
     Usage : <x-ui.badge etat="ok">Validé</x-ui.badge>
     Etats : ok (vert), attente (or), alerte (rouge), neutre (gris) --}}
@props(['etat' => 'neutre'])
<span {{ $attributes->merge(['class' => 'badge badge-' . $etat]) }}>{{ $slot }}</span>
