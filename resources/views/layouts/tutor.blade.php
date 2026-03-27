<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal de Padres - Juvenilia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body class="h-full bg-juvenilia-light">
    <div class="min-h-full flex flex-col">
        <header class="bg-blue-900 bg-juvenilia-blue sticky top-0 z-30 shadow-lg">
            <div class="px-4 py-3 flex items-center justify-between gap-4">
                <a href="{{ route('tutor.dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    @if(request()->routeIs('tutor.login'))
                        <img
                            src="{{ asset('IMG/logo_juvenilia.png') }}"
                            alt="Institución Juvenilia"
                            class="h-12 w-auto object-contain drop-shadow-md"
                        >
                    @else
                        <img
                            src="{{ asset('IMG/logodepte.png') }}"
                            alt="Juvenilia Fútbol"
                            class="h-12 w-auto object-contain drop-shadow-md"
                        >
                    @endif
                    <span class="hidden sm:block text-white font-black text-sm tracking-wide">Portal de Padres y Tutores</span>
                </a>

                @auth
                    <form action="{{ route('tutor.logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button
                            type="submit"
                            class="flex items-center gap-1.5 text-sm text-white/80 hover:text-white
                                   bg-white/10 hover:bg-white/20 active:bg-white/30
                                   px-3 py-1.5 rounded-lg transition-all duration-150"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9"/>
                            </svg>
                            Salir
                        </button>
                    </form>
                @endauth
            </div>

            <div class="h-[3px] header-rainbow"></div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        @unless(request()->routeIs('tutor.login'))
            <footer class="border-t border-gray-200/70 bg-white/70 backdrop-blur-sm">
                <div class="px-4 py-3 flex items-center justify-center gap-2.5">
                    <img src="{{ asset('IMG/logo_juvenilia.png') }}" alt="Institución Juvenilia" class="h-8 w-auto object-contain opacity-80">
                    <span class="text-sm text-gray-500">Institución Juvenilia</span>
                </div>
            </footer>
        @endunless
    </div>

    @livewireScripts
</body>
</html>
