<x-guest-layout>
    <h1 class="titre text-xl">Confirmer votre mot de passe</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Cette zone est protégée. Confirmez votre mot de passe avant de continuer.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf

        <x-ui.champ libelle="Mot de passe" id="password" name="password" type="password"
                    :erreur="$errors->first('password')" required autocomplete="current-password" />

        <div class="flex justify-end pt-1">
            <x-ui.bouton variante="primaire" type="submit">Confirmer</x-ui.bouton>
        </div>
    </form>
</x-guest-layout>
