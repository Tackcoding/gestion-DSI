<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Connexion &middot; MIDSP</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

            
            <div class="mb-8 text-center">
                <a href="/" class="inline-block">
                    <img src="{{ asset('img/midsp-logo-600.png') }}"
                         alt="MIDSP — Ministère de l'Industrialisation et du Développement du Secteur Privé"
                         class="mx-auto h-auto" style="width: 220px;">
                </a>
                <p class="legende mt-4">Direction de la Veille Économique et de la Communication</p>
            </div>

            <div class="carte w-full max-w-md px-7 py-8">
                {{ $slot }}
            </div>

            <p class="legende mt-6">Accès réservé aux agents de la direction.</p>
        </div>
    </body>
</html>
