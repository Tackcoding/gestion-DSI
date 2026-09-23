<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($titre) ? $titre . ' · ' : '' }}MIDSP · Gestion des événements et du matériel</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <a href="#contenu" class="evitement">Aller au contenu</a>

        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            @livewireStyles

            {{-- Bandeau de page : $header (sur-titre + titre) et, en option, $actions (bouton principal) --}}
            @isset($header)
                <header class="bandeau">
                    <div class="bandeau-interieur">
                        <div class="bandeau-bloc">
                            {{ $header }}
                        </div>

                        @isset($actions)
                            <div class="bandeau-actions">
                                {{ $actions }}
                            </div>
                        @endisset
                    </div>
                </header>
            @endisset

            <main id="contenu" class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-[var(--midsp-gris-bord)] bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span class="legende">MIDSP · Direction de la Veille Économique et de la Communication</span>
                    <span class="legende">Gestion des événements, du matériel et des absences</span>
                </div>
            </footer>
        </div>
        @livewireScripts
    </body>
</html>
