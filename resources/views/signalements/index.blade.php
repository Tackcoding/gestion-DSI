<x-app-layout>
    <x-slot name="titre">Signalements</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Patrimoine</p>
        <h1 class="bandeau-titre">Signalements</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('signalements.liste-signalements')
    </div>
</x-app-layout>
