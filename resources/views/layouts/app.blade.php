<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>MIDSP &middot; Gestion des événements et du matériel</title>

        {{-- Symbole seul : exception prevue par la charte p.9 pour les
             favicons, ou l'acronyme serait illisible sous 64 px. --}}
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        {{-- Navigation au clavier : premier element atteignable --}}
        <a href="#contenu" class="evitement">Aller au contenu</a>

        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            @isset($header)
                {{-- Bandeau Or Sable : le dispositif que la charte emploie
                     sur tous ses supports. Texte en Vert Profond (6,1:1),
                     jamais de blanc sur l'or. --}}
                <header class="bandeau">
                    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main id="contenu" class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-[var(--midsp-gris-acier)] bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span class="legende">MIDSP &middot; Direction de la Veille et de la Communication</span>
                    <span class="legende">Gestion des événements, du matériel et des absences</span>
                </div>
            </footer>
        </div>
    </body>
</html>
