{{-- Action de ligne dans un tableau : icone + libelle.
     Usage : <x-ui.action icone="crayon" libelle="Modifier" wire:click="modifier({{ $e->id }})" texte />
             <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="supprimer({{ $e->id }})" wire:confirm="Supprimer cet événement ?" danger />
             <x-ui.action icone="oeil" libelle="Détail" href="{{ route('evenements.detail', $e) }}" />
     texte  : affiche le libelle a cote de l'icone (sinon il reste lisible par les lecteurs d'ecran)
     danger : rouge au survol, pour les suppressions et refus uniquement --}}
@props(['icone', 'libelle', 'texte' => false, 'danger' => false])
@php $classes = 'action' . ($danger ? ' action-danger' : ''); @endphp
@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes, 'title' => $libelle]) }}>
        <x-ui.icone :nom="$icone" />
        <span @class(['masque-visuel' => ! $texte])>{{ $libelle }}</span>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'title' => $libelle]) }}>
        <x-ui.icone :nom="$icone" />
        <span @class(['masque-visuel' => ! $texte])>{{ $libelle }}</span>
    </button>
@endif
