<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal del Profesor - Juvenilia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
    <style>
        /* Scrollbar fino para el selector de grupos */
        .pills-scroll::-webkit-scrollbar { height: 3px; }
        .pills-scroll::-webkit-scrollbar-track { background: transparent; }
        .pills-scroll::-webkit-scrollbar-thumb { background: #1B5299; border-radius: 9999px; }
        /* Gradiente arcoíris del header */
        .header-rainbow {
            background: linear-gradient(
                to right,
                #F37021,
                #f59e0b,
                #eab308,
                #22c55e,
                #0ea5e9,
                #8b5cf6,
                #ec4899,
                #008345
            );
        }
    </style>
</head>
<body class="h-full bg-juvenilia-light">

    <div class="min-h-full flex flex-col">

        {{-- ===== HEADER ===== --}}
        <header class="bg-blue-900 bg-juvenilia-blue sticky top-0 z-30 shadow-lg">
            <div class="px-4 py-3 flex items-center justify-between gap-4">

                {{-- Logos --}}
                <div class="flex items-center gap-3 shrink-0">
                    <img
                        src="{{ asset('IMG/logo_juvenilia.jpeg') }}"
                        alt="Juvenilia"
                        class="h-10 w-auto object-contain rounded"
                    >
                    <div class="w-px h-8 bg-white/20"></div>
                    <img
                        src="{{ asset('IMG/logodepte.jpeg') }}"
                        alt="Deporte"
                        class="h-10 w-auto object-contain rounded"
                    >
                </div>

                {{-- Título centrado --}}
                <span class="hidden sm:block text-white font-semibold text-sm tracking-wide flex-1 text-center">
                    Portal del Profesor
                </span>

                {{-- Logout --}}
                @auth
                    <form action="{{ route('profesor.logout') }}" method="POST" class="inline shrink-0">
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

            {{-- Borde arcoíris --}}
            <div class="header-rainbow h-[3px]"></div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

    </div>

    @livewireScripts
</body>
</html>
