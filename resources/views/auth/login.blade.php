<x-guest-layout>
    <h1 class="titre text-xl">Connexion</h1>
    <p class="mt-1 text-sm text-[var(--gris)]">
        Identifiez-vous avec votre adresse professionnelle.
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="libelle">Adresse e-mail</label>
            <input id="email" name="email" type="email" class="champ"
                   value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <span class="erreur">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="libelle">Mot de passe</label>
            <input id="password" name="password" type="password" class="champ"
                   required autocomplete="current-password">
            @error('password') <span class="erreur">{{ $message }}</span> @enderror
        </div>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="remember"
                   class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
            <span class="text-sm text-[var(--gris)]">Rester connecté sur cet ordinateur</span>
        </label>

        <div class="flex items-center justify-between pt-1">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="lien-action">
                    Mot de passe oublié
                </a>
            @else
                <span></span>
            @endif

            <button type="submit" class="btn btn-principal">Se connecter</button>
        </div>
    </form>
</x-guest-layout>
