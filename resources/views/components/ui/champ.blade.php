{{-- Champ de formulaire avec libelle, erreur et aide.
     Usage : <x-ui.champ libelle="Intitulé" wire:model="intitule" :erreur="$errors->first('intitule')" required />
             <x-ui.champ libelle="Lieu" wire:model="lieu" aide="Ville ou salle" />
     Pour un textarea : <x-ui.champ libelle="Observations" wire:model="observations" type="textarea" /> --}}
@props(['libelle', 'erreur' => null, 'aide' => null, 'type' => 'text', 'id' => null])
@php
    $id = $id
        ?? $attributes->get('id')
        ?? 'champ-' . \Illuminate\Support\Str::slug($attributes->whereStartsWith('wire:model')->first() ?? $attributes->get('name') ?? $libelle);
    $classes = 'champ' . ($erreur ? ' champ-erreur' : '');
@endphp
<div class="champ-bloc">
    <label for="{{ $id }}" class="libelle">
        {{ $libelle }}@if ($attributes->has('required')) <span aria-hidden="true">*</span>@endif
    </label>
    @if ($type === 'textarea')
        <textarea id="{{ $id }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</textarea>
    @else
        <input id="{{ $id }}" type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @endif
    @if ($erreur)
        <p class="champ-message">{{ $erreur }}</p>
    @elseif ($aide)
        <p class="champ-aide">{{ $aide }}</p>
    @endif
</div>
