<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin - Juvenilia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col" x-data="{ menuOpen: false }">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3 gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 shrink-0">
                            <img src="{{ asset('IMG/logo_juvenilia.jpeg') }}" alt="Juvenilia" class="h-8 sm:h-10 w-auto object-contain">
                            <img src="{{ asset('IMG/logodepte.jpeg') }}" alt="Deporte" class="h-8 sm:h-10 w-auto object-contain rounded">
                        </a>
                        <div class="min-w-0">
                            <h1 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Panel administrativo</h1>
                            <p class="text-xs text-gray-500 hidden sm:block">Juvenilia</p>
                        </div>
                    </div>

                    <nav class="hidden lg:flex flex-wrap items-center gap-1 text-sm">
                        <a href="{{ route('admin.dashboard') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Inicio</a>
                        <a href="{{ route('admin.groups') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.groups') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Grupos</a>
                        <a href="{{ route('admin.students') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.students') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Alumnos</a>
                        <a href="{{ route('admin.teachers') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.teachers') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Profesores</a>
                        <a href="{{ route('admin.announcements') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.announcements') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Novedades</a>
                        <a href="{{ route('admin.fees.generate') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.fees.generate') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Generar cuotas</a>
                        <a href="{{ route('admin.treasury') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.treasury') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Revisión de pagos</a>
                        <a href="{{ route('admin.fee-manager') }}" class="px-2 py-1.5 rounded text-gray-700 hover:bg-gray-100 hover:text-blue-600 {{ request()->routeIs('admin.fee-manager') ? 'bg-gray-100 text-blue-600 font-medium' : '' }}">Deudas</a>
                    </nav>

                    <button
                        type="button"
                        @click="menuOpen = !menuOpen"
                        class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                        aria-label="Abrir menú"
                    >
                        <svg x-show="!menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="menuOpen" x-transition class="lg:hidden border-t border-gray-100">
                    <div class="py-2 flex flex-col gap-0.5">
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Inicio</a>
                        <a href="{{ route('admin.groups') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.groups') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Grupos</a>
                        <a href="{{ route('admin.students') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.students') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Alumnos</a>
                        <a href="{{ route('admin.teachers') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.teachers') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Profesores</a>
                        <a href="{{ route('admin.announcements') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.announcements') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Novedades</a>
                        <a href="{{ route('admin.fees.generate') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.fees.generate') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Generar cuotas</a>
                        <a href="{{ route('admin.treasury') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.treasury') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Revisión de pagos</a>
                        <a href="{{ route('admin.fee-manager') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request()->routeIs('admin.fee-manager') ? 'bg-gray-50 font-medium text-blue-600' : '' }}">Deudas</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
