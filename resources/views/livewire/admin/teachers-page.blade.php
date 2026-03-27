<div class="px-4 py-6 max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Profesores</h1>
            <p class="text-sm text-gray-600">ABM de profesores y asignación de grupos a cargo.</p>
        </div>
        <div class="flex items-center gap-2">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por apellido, nombre o email..."
                class="w-full sm:w-64 border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
            <button
                type="button"
                wire:click="create"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg"
            >
                Nuevo profesor
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-medium mb-3">
                {{ $editing ? 'Editar profesor' : 'Nuevo profesor' }}
            </h2>

            <form wire:submit.prevent="save" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre</label>
                        <input type="text" wire:model.defer="first_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Apellido</label>
                        <input type="text" wire:model.defer="last_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" wire:model.defer="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Teléfono</label>
                        <input type="text" wire:model.defer="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Notas</label>
                    <textarea wire:model.defer="notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                    @error('notes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="is_active" type="checkbox" wire:model.defer="is_active" class="rounded">
                    <label for="is_active" class="text-sm">Activo</label>
                </div>

                <div class="border-t border-gray-200 pt-3 mt-3">
                    <p class="text-sm font-medium text-gray-700 mb-2">Acceso al panel del profesor</p>
                    <p class="text-xs text-gray-500 mb-2">Si completás email y contraseña, el profesor podrá ingresar a <strong>/profesor/login</strong> con esas credenciales.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-1">Email para ingresar</label>
                            <input
                                type="email"
                                wire:model.defer="login_email"
                                placeholder="ej: profesor@escuela.com"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            >
                            @error('login_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-1">Contraseña</label>
                            <input
                                type="password"
                                wire:model.defer="login_password"
                                placeholder="{{ $editing && $editing->user_id ? 'Dejar en blanco para no cambiar' : 'Mín. 6 caracteres' }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                autocomplete="new-password"
                            >
                            @error('login_password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Grupos a cargo</label>
                    <div class="border border-gray-200 rounded-lg max-h-48 overflow-y-auto p-2 space-y-1">
                        @forelse($groups as $group)
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    value="{{ $group->id }}"
                                    wire:model="selected_group_ids"
                                    class="rounded"
                                >
                                <span>
                                    {{ $group->name }}
                                    @if($group->year)
                                        <span class="text-xs text-gray-500">({{ $group->year }})</span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="text-xs text-gray-500 px-1 py-1">No hay grupos cargados.</p>
                        @endforelse
                    </div>
                    @error('selected_group_ids.*') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2 mt-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">
                        Guardar
                    </button>
                    @if($editing)
                        <button type="button" wire:click="create" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            Nuevo
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Profesor</th>
                        <th class="px-3 py-2 text-left hidden md:table-cell">Email</th>
                        <th class="px-3 py-2 text-left hidden lg:table-cell">Grupos a cargo</th>
                        <th class="px-3 py-2 text-left">Acceso panel</th>
                        <th class="px-3 py-2 text-left">Activo</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($teachers as $teacher)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $teacher->last_name }}, {{ $teacher->first_name }}</span>
                                    @if($teacher->phone)
                                        <span class="text-xs text-gray-500">{{ $teacher->phone }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 hidden md:table-cell">
                                {{ $teacher->email }}
                            </td>
                            <td class="px-3 py-2 hidden lg:table-cell">
                                @if($teacher->groups->isEmpty())
                                    <span class="text-xs text-gray-400">Sin grupos</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($teacher->groups as $group)
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs">
                                                {{ $group->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @if($teacher->user_id)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700" title="{{ $teacher->user->email }}">Sí</span>
                                @else
                                    <span class="text-xs text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 text-xs rounded {{ $teacher->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $teacher->is_active ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right space-x-2">
                                <button
                                    type="button"
                                    wire:click="edit({{ $teacher->id }})"
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition cursor-pointer"
                                >
                                    Editar
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 transition cursor-pointer"
                                    onclick="if (confirm('¿Eliminar este profesor? Esta acción no se puede deshacer.')) { @this.call('delete', {{ $teacher->id }}) }"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                No hay profesores cargados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

