<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('evenements.index') }}" class="eyebrow hover:text-[var(--encre)]">
            &larr; Tous les événements
        </a>
        <h2 class="titre mt-1 text-2xl">{{ $evenement->intitule }}</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('evenements.detail-evenement', ['evenement' => $evenement])
    </div>
     <div class="mt-10">
        @livewire('reservations.reservations-evenement', ['evenement' => $evenement])
    </div>
</x-app-layout>
