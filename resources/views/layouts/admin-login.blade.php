<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Admin - Juvenilia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body class="h-full bg-juvenilia-light">
    <div class="min-h-full flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm">
            <div class="flex items-center justify-center gap-3 mb-6">
                <img src="{{ asset('IMG/logo_juvenilia.jpeg') }}" alt="Juvenilia" class="h-10 w-auto object-contain rounded">
                <img src="{{ asset('IMG/logodepte.jpeg') }}" alt="Deporte" class="h-10 w-auto object-contain rounded">
            </div>

            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>

