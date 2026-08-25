<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Connexion &middot; MIDSP</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet"
              href="https://fonts.bunny.net/css?family=newsreader:400,500,600|public-sans:400,500,600&display=swap">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>body { font-family: 'Public Sans', system-ui, sans-serif; }</style>
    </head>
    <body class="antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

            <div class="mb-8 text-center">
                <a href="/" class="inline-block">
                    <x-application-logo class="mx-auto" />
                </a>
                <p class="eyebrow mt-3">Direction de la Veille et de la Communication</p>
            </div>

            <div class="carte w-full max-w-md px-7 py-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-[var(--gris)]">
                Accès réservé aux agents de la direction.
            </p>
        </div>
    </body>
</html>
