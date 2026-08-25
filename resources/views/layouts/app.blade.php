<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>MIDSP &middot; Gestion des événements et du matériel</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet"
              href="https://fonts.bunny.net/css?family=newsreader:400,500,600|public-sans:400,500,600&display=swap">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>body { font-family: 'Public Sans', system-ui, sans-serif; }</style>
    </head>
    <body class="antialiased">
        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-[var(--trait)] bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-[var(--trait)] bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-5 text-xs text-[var(--gris)] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span>MIDSP &middot; Direction de la Veille et de la Communication</span>
                    <span>Gestion des événements, du matériel et des absences</span>
                </div>
            </footer>
        </div>
    </body>
</html>
