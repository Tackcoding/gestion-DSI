<x-app-layout>
    <x-slot name="titre">Mon compte</x-slot>

    <x-slot name="header">
        <p class="bandeau-surtitre">{{ Auth::user()->role->libelle() }}</p>
        <h1 class="bandeau-titre">Mon compte</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="carte p-5 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="carte p-5 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Pas de suppression de compte par l'agent : les comptes sont gérés
             par le directeur et l'administrateur (page Agents), qui les désactivent. --}}
    </div>
</x-app-layout>
