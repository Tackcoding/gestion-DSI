<x-guest-layout>
    <h1 class="titre text-xl">Créer un compte</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Les comptes sont réservés aux agents de la direction.
    </p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf

        <x-ui.champ libelle="Nom" id="name" name="name" value="{{ old('name') }}"
                    :erreur="$errors->first('name')" required autofocus autocomplete="name" />

        <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email" value="{{ old('email') }}"
                    :erreur="$errors->first('email')" required autocomplete="username" />

        <x-ui.champ libelle="Mot de passe" id="password" name="password" type="password"
                    :erreur="$errors->first('password')" required autocomplete="new-password" />

        <x-ui.champ libelle="Confirmer le mot de passe" id="password_confirmation" name="password_confirmation" type="password"
                    :erreur="$errors->first('password_confirmation')" required autocomplete="new-password" />

        <div class="flex items-center justify-between pt-1">
            <a href="{{ route('login') }}" class="lien text-sm">Déjà inscrit ?</a>
            <x-ui.bouton variante="primaire" type="submit">Créer le compte</x-ui.bouton>
        </div>
    </form>
</x-guest-layout>
