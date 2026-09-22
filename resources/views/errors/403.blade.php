{{-- Accès refusé : par exemple un agent qui tape /tableau-de-bord dans la barre d'adresse. --}}
<x-guest-layout>
    <h1 class="titre text-xl">Accès réservé</h1>
    <p class="mt-2 text-sm text-[var(--midsp-gris)]">
        Cette page n'est pas accessible avec votre compte.
        Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur de l'application.
    </p>

    <div class="mt-6 flex justify-end">
        <x-ui.bouton variante="primaire" href="{{ url('/') }}">Retour à l'accueil</x-ui.bouton>
    </div>
</x-guest-layout>
