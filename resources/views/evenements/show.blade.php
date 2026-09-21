<x-app-layout>
    <x-slot name="titre">{{ $evenement->intitule }}</x-slot>

    <x-slot name="header">
        <a href="{{ route('evenements.index') }}" class="bandeau-surtitre">
            <x-ui.icone nom="fleche-gauche" class="h-4 w-4" />
            Tous les événements
        </a>
        <h1 class="bandeau-titre">{{ $evenement->intitule }}</h1>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
        @livewire('evenements.detail-evenement', ['evenement' => $evenement])

        @livewire('reservations.reservations-evenement', ['evenement' => $evenement])
    </div>
</x-app-layout>
