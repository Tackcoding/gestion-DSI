<section>
    <header>
        <h2 class="titre text-lg">Informations du compte</h2>
        <p class="mt-1 text-sm text-[var(--midsp-gris)]">
            Votre nom et votre adresse e-mail de connexion.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <x-ui.champ libelle="Nom" id="name" name="name" value="{{ old('name', $user->name) }}"
                    :erreur="$errors->first('name')" required autofocus autocomplete="name" />

        <div>
            <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                        :erreur="$errors->first('email')" required autocomplete="username" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-2 text-sm">
                    Votre adresse e-mail n'est pas vérifiée.
                    <button form="send-verification" class="lien text-sm">Renvoyer l'e-mail de vérification.</button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm text-[var(--midsp-vert-profond)]">
                        Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                    </p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-ui.bouton variante="primaire" type="submit">Enregistrer</x-ui.bouton>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-[var(--midsp-vert-profond)]">Enregistré.</p>
            @endif
        </div>
    </form>
</section>
