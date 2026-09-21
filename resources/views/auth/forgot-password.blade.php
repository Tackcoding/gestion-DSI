<x-guest-layout>
    <h1 class="titre text-xl">Mot de passe oublié</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Indiquez votre adresse e-mail : vous recevrez un lien pour choisir un nouveau mot de passe.
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf

        <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email" value="{{ old('email') }}"
                    :erreur="$errors->first('email')" required autofocus autocomplete="username" />

        <div class="flex items-center justify-between pt-1">
            <a href="{{ route('login') }}" class="lien text-sm">Retour à la connexion</a>
            <x-ui.bouton variante="primaire" type="submit">Envoyer le lien</x-ui.bouton>
        </div>
    </form>
</x-guest-layout>
