<x-guest-layout>
    <h1 class="titre text-xl">Connexion</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Identifiez-vous avec l'adresse e-mail et le mot de passe choisis à l'activation de votre compte.
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6">
        @csrf

        <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email"
                    value="{{ old('email') }}" :erreur="$errors->first('email')"
                    required autofocus autocomplete="username" />

        <x-ui.champ libelle="Mot de passe" id="password" name="password" type="password"
                    :erreur="$errors->first('password')"
                    required autocomplete="current-password" />

        <div class="mt-2">
            <x-ui.case name="remember">Rester connecté sur cet ordinateur</x-ui.case>
        </div>

        <x-ui.bouton variante="primaire" type="submit" class="mt-4 w-full">Se connecter</x-ui.bouton>
    </form>

    <div class="mt-6 space-y-3 border-t border-[var(--midsp-gris-filet)] pt-4 text-sm">
        <p>
            <span class="font-medium">Première connexion ?</span>
            <a href="{{ route('activation') }}" class="lien">Activer mon compte</a>
        </p>
        <p class="text-[var(--midsp-gris)]">
            Mot de passe oublié ? Demandez un nouveau code d'accès à l'administrateur :
            il vous permettra d'en choisir un nouveau.
        </p>
    </div>
</x-guest-layout>
