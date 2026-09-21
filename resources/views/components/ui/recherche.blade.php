{{-- Champ de recherche avec loupe.
     Usage : <x-ui.recherche wire:model.live.debounce.300ms="recherche" placeholder="Intitulé ou lieu…" /> --}}
@props(['libelle' => 'Rechercher'])
<div class="champ-groupe">
    <x-ui.icone nom="recherche" />
    <input type="search" autocomplete="off" {{ $attributes->merge(['class' => 'champ', 'aria-label' => $libelle]) }}>
</div>
