@props(['value'])

<label {{ $attributes->merge(['class' => 'libelle']) }}>
    {{ $value ?? $slot }}
</label>
