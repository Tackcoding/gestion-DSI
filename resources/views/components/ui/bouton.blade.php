{{-- Bouton ou lien-bouton.
     Usage : <x-ui.bouton variante="primaire" icone="plus" wire:click="creer">Nouvel événement</x-ui.bouton>
             <x-ui.bouton variante="secondaire" href="{{ route('evenements') }}">Retour</x-ui.bouton>
     Variantes : primaire (une seule par page), secondaire, discret, danger
     Icone seule : ajouter class="btn-icone" et mettre le libellé dans <span class="masque-visuel"> --}}
@props(['variante' => 'primaire', 'icone' => null, 'type' => 'button'])
@php $classes = 'btn btn-' . $variante; @endphp
@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icone)<x-ui.icone :nom="$icone" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icone)<x-ui.icone :nom="$icone" />@endif
        {{ $slot }}
    </button>
@endif
