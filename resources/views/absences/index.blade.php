<x-app-layout>
    <x-slot name="header">
        <p class="eyebrow">Ressources humaines</p>
        <h2 class="titre mt-1 text-2xl">Absences</h2>
         @can('valider-absence')
        <a href="{{ route('absences.validation') }}" class="lien-action">
            Demandes a valider
        </a>
        @endcan
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('absences.mes-absences')
    </div>
</x-app-layout>
