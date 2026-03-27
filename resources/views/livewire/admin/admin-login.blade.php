<div class="px-4 py-8 max-w-sm mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex flex-col items-center gap-2 py-8 px-6 bg-gradient-to-b from-blue-900 to-blue-700">
            <img
                src="{{ asset('IMG/logodepte.png') }}"
                alt="Juvenilia Fútbol"
                class="h-28 w-auto object-contain drop-shadow-xl"
            >
        </div>

        <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Ingresar</h2>
        <p class="text-sm text-gray-500 mb-6">Acceso seguro al panel de administración.</p>

        <form wire:submit.prevent="authenticate" class="space-y-4">
            <div>
                <label for="login_email" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input
                    id="login_email"
                    type="text"
                    wire:model.defer="login_email"
                    autocomplete="username"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                    placeholder="ej: admin"
                >
                @error('login_email')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="login_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input
                    id="login_password"
                    type="password"
                    wire:model.defer="login_password"
                    autocomplete="current-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                    placeholder="••••••••"
                >
                @error('login_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl bg-blue-700 bg-juvenilia-blue text-white text-base font-semibold hover:brightness-110 active:scale-95 transition-all duration-150"
            >
                Entrar
            </button>
        </form>
        </div>
    </div>
</div>

