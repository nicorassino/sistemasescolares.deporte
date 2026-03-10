<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin - Juvenilia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Juvenilia - Panel administrativo</h1>
                    <p class="text-xs text-gray-500">Gestión de grupos y alumnos</p>
                </div>
                <nav class="flex flex-wrap gap-3 text-sm">
                    <a href="{{ route('admin.groups') }}" class="text-gray-700 hover:text-blue-600">Grupos</a>
                    <a href="{{ route('admin.students') }}" class="text-gray-700 hover:text-blue-600">Alumnos</a>
                    <a href="{{ route('admin.fees.generate') }}" class="text-gray-700 hover:text-blue-600">Generar cuotas</a>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>

