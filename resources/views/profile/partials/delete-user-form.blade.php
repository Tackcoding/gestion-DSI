<section class="space-y-5">
    <header>
        <h2 class="titre text-lg">Supprimer le compte</h2>
        <p class="mt-1 text-sm text-[var(--midsp-gris)]">
            La suppression du compte est définitive. Si vous quittez la direction, demandez plutôt à l'administrateur
            de désactiver votre compte : l'historique des événements et des mouvements reste ainsi lisible.
        </p>
    </header>

    <x-ui.bouton variante="danger" icone="corbeille" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Supprimer mon compte
    </x-ui.bouton>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable maxWidth="md">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <h3 class="modale-titre">Supprimer votre compte ?</h3>
            <p class="modale-sous-titre">
                Toutes vos données seront supprimées. Saisissez votre mot de passe pour confirmer.
            </p>

            <div class="modale-corps">
                <x-ui.champ libelle="Mot de passe" id="delete_password" name="password" type="password"
                            :erreur="$errors->userDeletion->first('password')" required autocomplete="current-password" />
            </div>

            <div class="modale-pied">
                <x-ui.bouton variante="secondaire" x-on:click="$dispatch('close')">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" type="submit">Supprimer définitivement</x-ui.bouton>
            </div>
        </form>
    </x-modal>
</section>
