@props(['active' => false])

@php
$base = 'flex items-center w-full min-h-[44px] ps-3 pe-4 py-2 border-l-4 text-start text-base transition';
$classes = $active
    ? "$base border-[var(--midsp-or)] bg-[var(--midsp-vert-clair)] text-[var(--midsp-vert-profond)]"
    : "$base border-transparent text-[var(--midsp-gris)] hover:bg-[var(--midsp-gris-fond)] hover:text-[var(--midsp-noir)]";
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
