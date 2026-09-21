@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'etat etat-succes']) }}>
        {{ $status }}
    </div>
@endif
