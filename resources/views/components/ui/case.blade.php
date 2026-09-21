{{-- Case à cocher avec libellé, 44 px de haut.
     Usage : <x-ui.case wire:model="actif">Agent actif</x-ui.case> --}}
<label class="case-libelle">
    <input type="checkbox" {{ $attributes->merge(['class' => 'case']) }}>
    <span>{{ $slot }}</span>
</label>
