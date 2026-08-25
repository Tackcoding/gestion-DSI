<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('evenements.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                &larr; Evenements
            </a>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detail de l'evenement
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @livewire('evenements.detail-evenement', ['evenement' => $evenement])
        </div>
    </div>
</x-app-layout>
