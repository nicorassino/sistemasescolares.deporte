<div class="px-4 py-6 max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Alumnos</h1>
            <p class="text-sm text-gray-600">Alta y edición rápida con asignación de tutor y grupo.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-600">PDF listado:</label>
                <select
                    wire:model.live="pdf_group_id"
                    class="border border-gray-300 rounded-md px-2 py-1.5 text-sm"
                >
                    <option value="">Todos los grupos</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
                <a
                    href="{{ route('admin.students-pdf.by-group') }}{{ (int) $pdf_group_id > 0 ? '?group=' . (int) $pdf_group_id : '' }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-50"
                >
                    Ver PDF
                </a>
            </div>
            @if($editing)
                <button wire:click="create" class="text-sm text-blue-700 hover:underline">
                    Nuevo alumno
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-5">
            <h2 class="text-lg font-medium mb-4">
                {{ $editing ? 'Editar alumno' : 'Nuevo alumno' }}
            </h2>

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" wire:model.defer="first_name" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                        <input type="text" wire:model.defer="last_name" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                        <input type="text" wire:model.defer="dni" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('dni') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nacimiento</label>
                        <input type="date" wire:model.defer="birth_date" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('birth_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                        <select wire:model.defer="gender" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-</option>
                            <option value="male">Masculino</option>
                            <option value="female">Femenino</option>
                            <option value="other">Otro</option>
                        </select>
                        @error('gender') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                        <select wire:model.defer="group_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccionar grupo...</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('group_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Beca (%)</label>
                        <input type="number" min="0" max="100" wire:model.defer="scholarship_percentage" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-[11px] text-gray-500 mt-1">0 = sin beca, 100 = gratis.</p>
                        @error('scholarship_percentage') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2 mt-6 md:col-span-2">
                        <input id="is_active" type="checkbox" wire:model.defer="is_active" class="rounded border border-gray-300">
                        <label for="is_active" class="text-sm text-gray-700">Alumno activo</label>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                        Guardar alumno
                    </button>
                    @error('selected_tutor_ids') <p class="text-red-600 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-medium">Tutor</h2>
                <button type="button" wire:click="toggleNewTutor" class="text-sm text-blue-700 hover:underline">
                    {{ $show_new_tutor ? 'Buscar tutor existente' : 'Crear nuevo tutor' }}
                </button>
            </div>

            @if (session()->has('tutor_message'))
                <div class="mb-3 p-2 rounded-md text-xs bg-green-50 text-green-800 border border-green-200">
                    {{ session('tutor_message') }}
                </div>
            @endif

            @if (session()->has('tutor_error'))
                <div class="mb-3 p-2 rounded-md text-xs bg-red-50 text-red-700 border border-red-200">
                    {{ session('tutor_error') }}
                </div>
            @endif

            @if(!$show_new_tutor)
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar (DNI, Email o Apellido)</label>
                <input type="text" wire:model.live.debounce.300ms="tutor_search" placeholder="Ej: 30123456 / padre@mail.com / Pérez"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">

                @if($selectedTutors->count())
                    <div class="mt-3 p-3 rounded-md border bg-blue-50 border-blue-200">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-xs font-semibold text-blue-900 mb-1">Tutores asignados al alumno</div>
                                <ul class="text-xs text-blue-800 space-y-0.5">
                                    @foreach($selectedTutors as $tutor)
                                        <li>
                                            {{ $tutor->last_name }}, {{ $tutor->first_name }}
                                            <span class="text-[11px] text-blue-700">
                                                (DNI: {{ $tutor->dni ?? '—' }} • {{ $tutor->user?->email ?? '—' }})
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" wire:click="clearTutor" class="text-[11px] text-blue-700 hover:underline">
                                Quitar todos
                            </button>
                        </div>
                    </div>
                @endif

                @if(count($tutorResults))
                    <div class="mt-3 border rounded-md overflow-hidden text-xs">
                        <div class="bg-gray-50 px-3 py-2 flex items-center justify-between">
                            <span class="font-medium text-gray-700">Resultados</span>
                            <span class="text-[11px] text-gray-500">Usar / Editar / Eliminar</span>
                        </div>
                        <div class="divide-y">
                            @foreach($tutorResults as $tutor)
                                <div class="px-3 py-2 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $tutor->last_name }}, {{ $tutor->first_name }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            DNI: {{ $tutor->dni ?? '—' }} • Email: {{ $tutor->user?->email ?? '—' }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">
                                            Tel: {{ $tutor->phone_main ?? '—' }} • Alumnos a cargo: {{ $tutor->students_count ?? 0 }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <button
                                            type="button"
                                            wire:click="selectTutor({{ $tutor->id }})"
                                            class="text-blue-700 hover:underline"
                                        >
                                            {{ in_array($tutor->id, $selected_tutor_ids ?? []) ? 'Quitar' : 'Agregar' }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="startEditingTutor({{ $tutor->id }})"
                                            class="text-gray-700 hover:underline"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            type="button"
                                            class="text-red-600 hover:underline"
                                            onclick="if (confirm('¿Eliminar este tutor? Solo se puede si no tiene alumnos asignados.')) { @this.call('deleteTutor', {{ $tutor->id }}) }"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif(trim($tutor_search) !== '')
                    <p class="text-xs text-gray-500 mt-2">Sin resultados. Podés crear un nuevo tutor.</p>
                @endif

                @if($editingTutor)
                    <div class="mt-4 border-t pt-3">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Editar tutor</h3>
                        <div class="space-y-3 text-sm">
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nombre</label>
                                    <input type="text" wire:model.defer="editing_tutor_first_name"
                                           class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:border-blue-500 focus:ring-blue-500">
                                    @error('editing_tutor_first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Apellido</label>
                                    <input type="text" wire:model.defer="editing_tutor_last_name"
                                           class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:border-blue-500 focus:ring-blue-500">
                                    @error('editing_tutor_last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" wire:model.defer="editing_tutor_email"
                                       class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:border-blue-500 focus:ring-blue-500">
                                @error('editing_tutor_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Teléfono</label>
                                    <input type="text" wire:model.defer="editing_tutor_phone"
                                           class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:border-blue-500 focus:ring-blue-500">
                                    @error('editing_tutor_phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">DNI</label>
                                    <input type="text" wire:model.defer="editing_tutor_dni"
                                           class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:border-blue-500 focus:ring-blue-500">
                                    @error('editing_tutor_dni') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 mt-2">
                                <button type="button" wire:click="cancelEditingTutor" class="px-3 py-1.5 text-xs border rounded-md">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="saveEditingTutor" class="px-3 py-1.5 text-xs rounded-md bg-blue-600 text-white">
                                    Guardar cambios
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="space-y-3">
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input type="text" wire:model.defer="new_tutor_first_name"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('new_tutor_first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                            <input type="text" wire:model.defer="new_tutor_last_name"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('new_tutor_last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model.defer="new_tutor_email"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('new_tutor_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" wire:model.defer="new_tutor_phone"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('new_tutor_phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">DNI (opcional)</label>
                            <input type="text" wire:model.defer="new_tutor_dni"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('new_tutor_dni') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-2">
                        <button
                            type="button"
                            wire:click="addNewTutor"
                            class="px-3 py-1.5 text-xs rounded-md bg-blue-600 text-white hover:bg-blue-700"
                        >
                            Crear tutor y agregar a la lista
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <div class="px-3 py-2 border-b flex items-center justify-between gap-3">
            <div class="text-sm font-medium text-gray-700">
                Listado de alumnos
            </div>
            <div class="w-full max-w-xs">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Filtrar por apellido, nombre o DNI..."
                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Alumno</th>
                    <th class="px-3 py-2 text-left hidden sm:table-cell">DNI</th>
                    <th class="px-3 py-2 text-left hidden md:table-cell">Tutor</th>
                    <th class="px-3 py-2 text-left hidden md:table-cell">Grupo</th>
                    <th class="px-3 py-2 text-left">Activo</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $tutor = $student->tutors->first();
                        $group = $student->groups->first();
                    @endphp
                    <tr class="border-t">
                        <td class="px-3 py-2">
                            {{ $student->last_name }}, {{ $student->first_name }}
                            <div class="sm:hidden text-xs text-gray-500">
                                DNI {{ $student->dni }}
                                @if($group)
                                    • {{ $group->name }}
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 hidden sm:table-cell">{{ $student->dni }}</td>
                        <td class="px-3 py-2 hidden md:table-cell">
                            @if($tutor)
                                {{ $tutor->last_name }}, {{ $tutor->first_name }}
                            @else
                                <span class="text-gray-400 text-xs">Sin tutor</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 hidden md:table-cell">
                            {{ $group->name ?? '—' }}
                        </td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded {{ $student->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $student->is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right space-x-2">
                            <button
                                type="button"
                                wire:click="edit({{ $student->id }})"
                                class="text-blue-600 text-xs"
                            >
                                Editar
                            </button>
                            <button
                                type="button"
                                class="text-red-600 text-xs"
                                onclick="if (confirm('¿Eliminar este alumno? Esta acción no se puede deshacer.')) { @this.call('delete', {{ $student->id }}) }"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                            No hay alumnos cargados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

