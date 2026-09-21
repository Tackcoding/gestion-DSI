<x-app-layout>
    <x-slot name="titre">Registre du matériel</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Dépositaire comptable</p>
        <h1 class="bandeau-titre">Registre du matériel</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('mouvements.registre-materiel')
    </div>
</x-app-layout>
