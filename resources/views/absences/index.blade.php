<x-app-layout>
    <x-slot name="titre">Absences</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Ressources humaines</p>
        <h1 class="bandeau-titre">Absences</h1>
    </x-slot>

    @can('valider-absence')
        <x-slot name="actions">
            <x-ui.bouton variante="secondaire" icone="calendrier" href="{{ route('absences.planning') }}">
                Planning
            </x-ui.bouton>
            <x-ui.bouton variante="secondaire" icone="coche" href="{{ route('absences.validation') }}">
                Demandes à valider
            </x-ui.bouton>
        </x-slot>
    @endcan

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('absences.mes-absences')
    </div>
</x-app-layout>
