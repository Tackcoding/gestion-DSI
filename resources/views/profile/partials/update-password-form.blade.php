<section>
    <header>
        <h2 class="titre text-lg">Mot de passe</h2>
        <p class="mt-1 text-sm text-[var(--midsp-gris)]">
            Choisissez un mot de passe long, que vous n'utilisez nulle part ailleurs.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <x-ui.champ libelle="Mot de passe actuel" id="update_password_current_password" name="current_password" type="password"
                    :erreur="$errors->updatePassword->first('current_password')" autocomplete="current-password" />

        <x-ui.champ libelle="Nouveau mot de passe" id="update_password_password" name="password" type="password"
                    :erreur="$errors->updatePassword->first('password')" autocomplete="new-password" />

        <x-ui.champ libelle="Confirmer le nouveau mot de passe" id="update_password_password_confirmation" name="password_confirmation" type="password"
                    :erreur="$errors->updatePassword->first('password_confirmation')" autocomplete="new-password" />

        <div class="flex items-center gap-4">
            <x-ui.bouton variante="primaire" type="submit">Enregistrer</x-ui.bouton>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-[var(--midsp-vert-profond)]">Enregistré.</p>
            @endif
        </div>
    </form>
</section>
