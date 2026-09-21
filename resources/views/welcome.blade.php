{{-- La racine "/" doit être gérée par une route de redirection dans routes/web.php :
     Route::get('/', fn () => redirect()->route(auth()->check() ? 'tableau-de-bord' : 'login'));
     Cette vue n'est conservée que par sécurité, au cas où elle serait encore appelée. --}}
<x-guest-layout>
    <p class="text-center">
        <a href="{{ auth()->check() ? route('tableau-de-bord') : route('login') }}" class="btn btn-primaire">
            Accéder à l'application
        </a>
    </p>
</x-guest-layout>
