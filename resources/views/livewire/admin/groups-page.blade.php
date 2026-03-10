<div class="px-4 py-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Grupos</h1>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-lg font-medium mb-3">
            {{ $editing ? 'Editar grupo' : 'Nuevo grupo' }}
        </h2>

        <form wire:submit.prevent="save" class="space-y-3">
            <div>
                <label class="block text-sm font-medium mb-1">Nombre</label>
                <input type="text" wire:model.defer="name" class="w-full border rounded px-3 py-2 text-sm">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Descripción</label>
                <textarea wire:model.defer="description" class="w-full border rounded px-3 py-2 text-sm" rows="2"></textarea>
                @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Año</label>
                    <input type="number" wire:model.defer="year" class="w-full border rounded px-3 py-2 text-sm">
                    @error('year') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nivel</label>
                    <input type="text" wire:model.defer="level" class="w-full border rounded px-3 py-2 text-sm">
                    @error('level') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Capacidad máx.</label>
                    <input type="number" wire:model.defer="max_capacity" class="w-full border rounded px-3 py-2 text-sm">
                    @error('max_capacity') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" type="checkbox" wire:model.defer="is_active" class="rounded">
                <label for="is_active" class="text-sm">Activo</label>
            </div>

            <div class="flex gap-2 mt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded">
                    Guardar
                </button>
                @if($editing)
                    <button type="button" wire:click="create" class="px-3 py-2 text-sm border rounded">
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
                    <th class="px-3 py-2 text-left">Nombre</th>
                    <th class="px-3 py-2 text-left hidden sm:table-cell">Descripción</th>
                    <th class="px-3 py-2 text-left">Año</th>
                    <th class="px-3 py-2 text-left hidden sm:table-cell">Nivel</th>
                    <th class="px-3 py-2 text-left">Activo</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $group->name }}</td>
                        <td class="px-3 py-2 hidden sm:table-cell truncate max-w-xs">{{ $group->description }}</td>
                        <td class="px-3 py-2">{{ $group->year }}</td>
                        <td class="px-3 py-2 hidden sm:table-cell">{{ $group->level }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded {{ $group->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $group->is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right space-x-2">
                            <button wire:click="edit({{ $group->id }})" class="text-blue-600 text-xs">Editar</button>
                            <button wire:click="delete({{ $group->id }})" class="text-red-600 text-xs">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                            No hay grupos cargados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

