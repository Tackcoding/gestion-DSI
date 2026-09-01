<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('absences.index') }}" class="eyebrow hover:text-[var(--encre)]">
            &larr; Toutes les absences
        </a>
        <h2 class="titre mt-1 text-2xl">Validation des absences</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @livewire('absences.validation-absences')
    </div>
</x-app-layout>
