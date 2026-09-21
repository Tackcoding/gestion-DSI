{{-- Modale Livewire : voile + boîte, titre, corps, pied.
     Usage : <x-ui.modale titre="Nouvel événement" fermer="$set('modaleOuverte', false)" largeur="lg">
                 ...champs...
                 <x-slot:pied>
                     <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                     <x-ui.bouton variante="primaire" wire:click="enregistrer">Enregistrer</x-ui.bouton>
                 </x-slot:pied>
             </x-ui.modale>
     fermer  : expression Livewire déclenchée par la touche Échap
     largeur : sm, md, lg, xl, 2xl --}}
@props(['titre', 'sousTitre' => null, 'largeur' => 'lg', 'fermer' => null])
@php
    $largeurs = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl'];
    $idTitre = 'modale-' . \Illuminate\Support\Str::slug($titre);
@endphp
<div class="voile" @if ($fermer) wire:keydown.escape.window="{{ $fermer }}" @endif>
    <div class="modale {{ $largeurs[$largeur] ?? 'max-w-lg' }}" role="dialog" aria-modal="true" aria-labelledby="{{ $idTitre }}">
        <h3 id="{{ $idTitre }}" class="modale-titre">{{ $titre }}</h3>
        @if ($sousTitre)
            <p class="modale-sous-titre">{{ $sousTitre }}</p>
        @endif

        <div class="modale-corps">
            {{ $slot }}
        </div>

        @isset($pied)
            <div class="modale-pied">
                {{ $pied }}
            </div>
        @endisset
    </div>
</div>
