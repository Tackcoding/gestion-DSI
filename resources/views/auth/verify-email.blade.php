<x-guest-layout>
    <h1 class="titre text-xl">Vérifiez votre adresse e-mail</h1>
    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
        Merci pour votre inscription. Avant de commencer, cliquez sur le lien que nous venons de vous envoyer par e-mail.
        Si vous ne l'avez pas reçu, nous pouvons vous en envoyer un autre.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="etat etat-succes mt-4">
            Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="lien text-sm">Se déconnecter</button>
        </form>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.bouton variante="primaire" type="submit">Renvoyer l'e-mail</x-ui.bouton>
        </form>
    </div>
</x-guest-layout>
