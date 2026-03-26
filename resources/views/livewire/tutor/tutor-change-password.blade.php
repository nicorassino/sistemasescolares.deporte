<div class="px-4 py-8 max-w-sm mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Cambiar contraseña</h2>
        <p class="text-sm text-gray-500 mb-6">Por seguridad, debés cambiar la contraseña en tu primer ingreso.</p>

        @if(session('status'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 border-l-4 border-juvenilia-green text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="updatePassword" class="space-y-4">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
                <input
                    id="current_password"
                    type="password"
                    wire:model.defer="current_password"
                    autocomplete="current-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                >
                @error('current_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                <input
                    id="new_password"
                    type="password"
                    wire:model.defer="new_password"
                    autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                >
                @error('new_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                <input
                    id="new_password_confirmation"
                    type="password"
                    wire:model.defer="new_password_confirmation"
                    autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <button
                type="submit"
                class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg bg-blue-600 text-white text-base font-medium hover:bg-blue-700"
            >
                Actualizar contraseña
            </button>
        </form>
    </div>
</div>

