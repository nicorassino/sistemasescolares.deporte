<div class="px-4 py-6 max-w-5xl mx-auto" wire:key="groups-page">
    <h1 class="text-2xl font-semibold mb-4">Grupos</h1>

    @if (session()->has('message'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 mb-6" wire:key="group-form-{{ $editing?->id ?? 'new' }}">
        <h2 class="text-lg font-medium mb-3">
            {{ $editing ? 'Editar grupo' : 'Nuevo grupo' }}
        </h2>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700 text-sm">
                <p class="font-medium mb-1">Corrija los siguientes errores:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-3">
            <div>
                <label class="block text-sm font-medium mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Descripción <span class="text-gray-400">(opcional)</span></label>
                <textarea wire:model.defer="description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Año <span class="text-red-500">*</span></label>
                    <input type="number" wire:model.blur="year" min="2000" max="2100" placeholder="ej. 2026" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('year') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nivel <span class="text-gray-400">(opcional)</span></label>
                    <input type="text" wire:model.defer="level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('level') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Capacidad máx. <span class="text-gray-400">(opcional)</span></label>
                    <input type="number" wire:model.defer="max_capacity" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('max_capacity') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" type="checkbox" wire:model.defer="is_active" class="rounded">
                <label for="is_active" class="text-sm">Activo <span class="text-red-500">*</span></label>
            </div>

            <div class="flex gap-2 mt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded">
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

    <div class="bg-white rounded-lg shadow overflow-x-auto" wire:key="groups-table-{{ $groups->pluck('id')->sort()->values()->implode('-') }}">
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
            <tbody class="divide-y divide-gray-50">
                @forelse($groups as $group)
                    <tr wire:key="group-row-{{ $group->id }}">
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
                            <button
                                type="button"
                                wire:click.prevent="edit({{ $group->id }})"
                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition cursor-pointer"
                            >
                                Editar
                            </button>
                            @if($group->students_count > 0 || $group->teacher_id)
                                <button
                                    type="button"
                                    disabled
                                    title="No se puede eliminar: tiene alumnos o profesor asignado."
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-gray-400 bg-gray-100 cursor-not-allowed"
                                >
                                    Eliminar
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 transition cursor-pointer"
                                    onclick="if(confirm('¿Eliminar este grupo?')) @this.call('delete', {{ $group->id }})">
                                    Eliminar
                                </button>
                            @endif
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

