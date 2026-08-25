<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow">Direction de la Veille et de la Communication</p>
        <h2 class="titre mt-1 text-2xl">Tableau de bord</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('tableau-de-bord')
    </div>
</x-app-layout>
