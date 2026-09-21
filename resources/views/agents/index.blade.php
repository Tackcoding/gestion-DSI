<x-app-layout>
    <x-slot name="titre">Agents</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">Ressources humaines</p>
        <h1 class="bandeau-titre">Agents</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('agents.liste-agents')
    </div>
</x-app-layout>
