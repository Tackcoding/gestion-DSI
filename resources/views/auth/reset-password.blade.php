<x-guest-layout>
    <h1 class="titre text-xl">Nouveau mot de passe</h1>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.champ libelle="Adresse e-mail" id="email" name="email" type="email"
                    value="{{ old('email', $request->email) }}"
                    :erreur="$errors->first('email')" required autofocus autocomplete="username" />

        <x-ui.champ libelle="Nouveau mot de passe" id="password" name="password" type="password"
                    :erreur="$errors->first('password')" required autocomplete="new-password" />

        <x-ui.champ libelle="Confirmer le mot de passe" id="password_confirmation" name="password_confirmation" type="password"
                    :erreur="$errors->first('password_confirmation')" required autocomplete="new-password" />

        <div class="flex justify-end pt-1">
            <x-ui.bouton variante="primaire" type="submit">Enregistrer le mot de passe</x-ui.bouton>
        </div>
    </form>
</x-guest-layout>
