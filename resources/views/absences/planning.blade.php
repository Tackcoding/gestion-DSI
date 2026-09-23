<x-app-layout>
    <x-slot name="titre">Planning des absences</x-slot>

    <x-slot name="header">
        <a href="{{ route('absences.index') }}" class="bandeau-surtitre">
            <x-ui.icone nom="fleche-gauche" class="h-4 w-4" />
            Toutes les absences
        </a>
        <h1 class="bandeau-titre">Planning des absences</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('absences.planning-absences')
    </div>
</x-app-layout>
