<x-guest-layout>
    <h1 class="titre text-xl">Activer mon compte</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Saisissez le code d'accès remis par l'administrateur ou le directeur,
        puis choisissez l'adresse e-mail et le mot de passe avec lesquels vous vous connecterez.
    </p>

    <form method="POST" action="{{ route('activation.store') }}" class="mt-6">
        @csrf

        <x-ui.champ libelle="Code d'accès" id="code" name="code" value="{{ old('code') }}"
                    :erreur="$errors->first('code')" placeholder="XXXX-XXXX"
                    required autofocus autocomplete="off" />

        <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email" value="{{ old('email') }}"
                    :erreur="$errors->first('email')" required autocomplete="username" />

        <x-ui.champ libelle="Mot de passe" id="password" name="password" type="password"
                    :erreur="$errors->first('password')" aide="8 caractères au minimum"
                    required autocomplete="new-password" />

        <x-ui.champ libelle="Confirmer le mot de passe" id="password_confirmation" name="password_confirmation" type="password"
                    required autocomplete="new-password" />

        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('login') }}" class="lien text-sm">J'ai déjà un compte</a>
            <x-ui.bouton variante="primaire" type="submit">Activer mon compte</x-ui.bouton>
        </div>
    </form>
</x-guest-layout>
