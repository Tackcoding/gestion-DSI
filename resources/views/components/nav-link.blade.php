@props(['active' => false])

@php
$base = 'inline-flex items-center h-20 px-1 border-b-4 text-base transition';
$classes = $active
    ? "$base border-[var(--midsp-or)] text-[var(--midsp-vert-profond)]"
    : "$base border-transparent text-[var(--midsp-gris)] hover:border-[var(--midsp-gris-bord)] hover:text-[var(--midsp-noir)]";
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
