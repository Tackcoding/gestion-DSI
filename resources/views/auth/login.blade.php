<x-guest-layout>
    <h1 class="titre text-xl">Connexion</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Identifiez-vous avec votre adresse professionnelle.
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email"
                    value="{{ old('email') }}" :erreur="$errors->first('email')"
                    required autofocus autocomplete="username" />

        <x-ui.champ libelle="Mot de passe" id="password" name="password" type="password"
                    :erreur="$errors->first('password')"
                    required autocomplete="current-password" />

        <x-ui.case name="remember">Rester connecté sur cet ordinateur</x-ui.case>

        <div class="flex items-center justify-between pt-1">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="lien text-sm">Mot de passe oublié</a>
            @else
                <span></span>
            @endif

            <x-ui.bouton variante="primaire" type="submit">Se connecter</x-ui.bouton>
        </div>
    </form>
</x-guest-layout>
