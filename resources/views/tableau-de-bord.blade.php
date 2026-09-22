<x-app-layout>
    <x-slot name="titre">Tableau de bord</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Direction de la Veille Économique et de la Communication</p>
        <h1 class="bandeau-titre">Tableau de bord</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('tableau-de-bord')
    </div>
</x-app-layout>
