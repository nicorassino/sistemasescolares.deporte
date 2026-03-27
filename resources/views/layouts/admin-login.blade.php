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
    <div class="min-h-full flex flex-col">
        <header class="bg-blue-900 bg-juvenilia-blue sticky top-0 z-30 shadow-lg">
            <div class="px-4 py-3 flex items-center justify-start">
                <div class="flex items-center gap-2.5">
                    <img
                        src="{{ asset('IMG/logo_juvenilia.png') }}"
                        alt="Institución Juvenilia"
                        class="h-12 w-auto object-contain drop-shadow-md"
                    >
                    <span class="text-white font-black text-sm tracking-wide">Administración</span>
                </div>
            </div>
            <div class="h-[3px] header-rainbow"></div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>

