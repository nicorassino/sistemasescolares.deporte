<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal del Profesor - Juvenilia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-full flex flex-col">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="px-3 py-3 flex items-center justify-between">
                <h1 class="text-base font-semibold text-gray-900">Portal del Profesor - Juvenilia</h1>
                @auth
                    <form action="{{ route('profesor.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 py-1 px-2">
                            Cerrar sesión
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
