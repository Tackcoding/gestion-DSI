<x-app-layout>
    <x-slot name="titre">Matériel</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Inventaire</p>
        <h1 class="bandeau-titre">Matériel</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('materiels.liste-materiels')
    </div>
</x-app-layout>
