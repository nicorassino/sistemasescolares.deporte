<div class="px-4 py-6 max-w-md mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Cambiar clave</h1>

    <div class="bg-white rounded-lg shadow p-4">
        @if(session('status'))
            <div class="mb-4 px-4 py-3 rounded-2xl bg-green-50 border-l-4 border-green-600 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="updatePassword" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Contraseña actual</label>
                <input
                    type="password"
                    wire:model.defer="current_password"
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    autocomplete="current-password"
                >
                @error('current_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Nueva contraseña</label>
                <input
                    type="password"
                    wire:model.defer="new_password"
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    autocomplete="new-password"
                >
                @error('new_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirmar nueva contraseña</label>
                <input
                    type="password"
                    wire:model.defer="new_password_confirmation"
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    autocomplete="new-password"
                >
                @error('new_password_confirmation')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">
                    Actualizar
                </button>
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg inline-flex items-center"
                >
                    Volver
                </a>
            </div>
        </form>
    </div>
</div>

