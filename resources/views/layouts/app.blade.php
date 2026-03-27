<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin - Juvenilia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-juvenilia-light">
    <div class="min-h-full flex flex-col" x-data="{ menuOpen: false }">
        <header class="bg-blue-900 bg-juvenilia-blue sticky top-0 z-40 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3 gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 shrink-0">
                            {{-- Principal: escuela de fútbol --}}
                            <img
                                src="{{ asset('IMG/logodepte.png') }}"
                                alt="Juvenilia Fútbol"
                                class="h-10 sm:h-12 w-auto object-contain drop-shadow-md"
                            >
                            <span class="text-white font-black text-sm tracking-wide">Administración</span>
                        </a>
                    </div>

                    <nav class="hidden lg:flex flex-wrap items-center gap-1 text-sm">
                        <a href="{{ route('admin.dashboard') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.dashboard') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Inicio</a>
                        <a href="{{ route('admin.groups') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.groups') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Grupos</a>
                        <a href="{{ route('admin.students') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.students') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Alumnos</a>
                        <a href="{{ route('admin.teachers') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.teachers') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Profesores</a>
                        <a href="{{ route('admin.announcements') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.announcements') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Novedades</a>
                        <a href="{{ route('admin.fees.generate') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.fees.generate') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Generar cuotas</a>
                        <a href="{{ route('admin.treasury') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.treasury') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Revisión de pagos</a>
                        <a href="{{ route('admin.fee-manager') }}" class="px-2 py-1.5 rounded-full
                           {{ request()->routeIs('admin.fee-manager') ? 'bg-white text-juvenilia-blue font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">Deudas</a>
                    </nav>

                    @auth
                        @if(auth()->user()?->role === 'admin')
                            <div class="hidden lg:flex items-center gap-2 shrink-0">
                                <a
                                    href="{{ route('admin.change-password') }}"
                                    class="px-3 py-1.5 rounded-full text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white"
                                >
                                    Cambiar clave
                                </a>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="px-3 py-1.5 rounded-full text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white"
                                    >
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth

                    <button
                        type="button"
                        @click="menuOpen = !menuOpen"
                        class="lg:hidden p-2 rounded-md text-white/80 hover:bg-white/10 hover:text-white"
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

                <div x-show="menuOpen" x-transition class="lg:hidden border-t border-white/10">
                    <div class="py-2 flex flex-col gap-0.5">
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.dashboard') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Inicio</a>
                        <a href="{{ route('admin.groups') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.groups') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Grupos</a>
                        <a href="{{ route('admin.students') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.students') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Alumnos</a>
                        <a href="{{ route('admin.teachers') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.teachers') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Profesores</a>
                        <a href="{{ route('admin.announcements') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.announcements') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Novedades</a>
                        <a href="{{ route('admin.fees.generate') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.fees.generate') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Generar cuotas</a>
                        <a href="{{ route('admin.treasury') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.treasury') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Revisión de pagos</a>
                        <a href="{{ route('admin.fee-manager') }}" class="px-4 py-2 text-sm
                           {{ request()->routeIs('admin.fee-manager') ? 'bg-white text-juvenilia-blue font-semibold' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">Deudas</a>

                        @auth
                            @if(auth()->user()?->role === 'admin')
                                <div class="h-px bg-white/10 my-1"></div>
                                <a
                                    href="{{ route('admin.change-password') }}"
                                    class="px-4 py-2 text-sm text-white/90 hover:bg-white/10 hover:text-white"
                                >
                                    Cambiar clave
                                </a>
                                <form action="{{ route('admin.logout') }}" method="POST" class="px-0">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-white/90 hover:bg-white/10 hover:text-white"
                                    >
                                        Cerrar sesión
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <div class="h-[3px] header-rainbow"></div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-gray-200/70 bg-white/70 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-center gap-2.5">
                <img src="{{ asset('IMG/logo_juvenilia.png') }}" alt="Institución Juvenilia" class="h-12 w-auto object-contain opacity-80">
                <span class="text-sm text-gray-500">Institución Juvenilia</span>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
