<x-app-layout>
    <x-slot name="titre">Événements</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Couverture des événements</p>
        <h1 class="bandeau-titre">Événements</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('evenements.liste-evenements')
    </div>
</x-app-layout>
