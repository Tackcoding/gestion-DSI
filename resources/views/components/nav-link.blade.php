@props(['active'])

@php
// Zone interactive de 44 px de haut minimum (charte p.18).
$base = 'inline-flex items-center h-16 px-1 border-b-4 text-base transition';
$classes = ($active ?? false)
    ? "$base border-[var(--midsp-or)] text-[var(--midsp-vert-profond)]"
    : "$base border-transparent text-[var(--midsp-gris)] hover:border-[var(--midsp-gris-acier)] hover:text-[var(--midsp-noir)]";
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
