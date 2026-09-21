{{-- Liste déroulante stylée (chevron vert, hauteur 44 px).
     Usage : <x-ui.selection wire:model.live="statut" libelle="Filtrer par statut">
                 <option value="">Tous les statuts</option>
                 ...
             </x-ui.selection>
     libelle : texte lu par les lecteurs d'écran quand il n'y a pas de <label for> visible
               (un attribut null n'est pas rendu). --}}
@props(['libelle' => null])
<select {{ $attributes->merge(['class' => 'champ', 'aria-label' => $libelle]) }}>
    {{ $slot }}
</select>
