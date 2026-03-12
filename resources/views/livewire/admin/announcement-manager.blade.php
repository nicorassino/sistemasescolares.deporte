<div class="px-4 py-6 max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Institucional y Novedades</h1>
            <p class="text-sm text-gray-600">Creá y administrá los comunicados que verán las familias.</p>
        </div>
        @if($editing)
            <button type="button" wire:click="create" class="text-sm text-blue-700 hover:underline">
                Nueva novedad
            </button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-xl shadow p-4 border border-gray-100">
            <h2 class="text-lg font-medium mb-3">
                {{ $editing ? 'Editar novedad' : 'Nueva novedad' }}
            </h2>

            <form wire:submit.prevent="save" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                    <input
                        type="text"
                        wire:model.defer="title"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                    >
                    @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenido</label>
                    <textarea
                        wire:model.defer="content"
                        rows="5"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                    ></textarea>
                    @error('content') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subir imagen (opcional)</label>
                    <input
                        type="file"
                        wire:model="image"
                        accept="image/jpeg,image/jpg,image/png"
                        class="w-full text-sm"
                    >
                    <p class="text-[11px] text-gray-500 mt-1">
                        Formatos permitidos: JPG o PNG. Tamaño máximo: 2MB.
                    </p>
                    @error('image') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2 pt-1">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                    >
                        Guardar
                    </button>
                    @if($editing)
                        <button
                            type="button"
                            wire:click="create"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg text-gray-700"
                        >
                            Cancelar edición
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">Novedades publicadas</h2>
                    <p class="text-xs text-gray-500">
                        Se muestran hasta 15 novedades (las más recientes).
                    </p>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($announcements as $announcement)
                        <div class="px-4 py-3 flex flex-col gap-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $announcement->title }}
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        Publicado el {{ $announcement->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <button
                                        type="button"
                                        wire:click="edit({{ $announcement->id }})"
                                        class="text-blue-600 hover:underline"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        class="text-red-600 hover:underline"
                                        onclick="if (confirm('¿Eliminar esta novedad? Esta acción no se puede deshacer.')) { @this.call('delete', {{ $announcement->id }}) }"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                            @if($announcement->image_path)
                                <div class="mt-1">
                                    <img
                                        src="{{ asset('storage/'.$announcement->image_path) }}"
                                        alt="Imagen de la novedad"
                                        class="w-full max-h-48 object-cover rounded-lg"
                                    >
                                </div>
                            @endif
                            <p class="text-sm text-gray-700 whitespace-pre-line">
                                {{ $announcement->content }}
                            </p>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-sm text-gray-500">
                            No hay novedades cargadas todavía.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

