<div class="px-4 py-8 max-w-sm mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Ingresar</h2>
        <p class="text-sm text-gray-500 mb-6">Ingresá con tu correo y contraseña de tutor.</p>

        <form wire:submit.prevent="authenticate" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input
                    id="email"
                    type="email"
                    wire:model.defer="email"
                    autocomplete="email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                    placeholder="tu@correo.com"
                >
                @error('email')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input
                    id="password"
                    type="password"
                    wire:model.defer="password"
                    autocomplete="current-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg bg-blue-600 text-white text-base font-medium hover:bg-blue-700"
            >
                Ingresar
            </button>
        </form>
    </div>
</div>
