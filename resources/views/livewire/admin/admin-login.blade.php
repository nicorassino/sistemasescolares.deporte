<div class="px-4 py-2">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
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
                class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg bg-blue-600 text-white text-base font-medium hover:bg-blue-700"
            >
                Entrar
            </button>
        </form>
    </div>
</div>

