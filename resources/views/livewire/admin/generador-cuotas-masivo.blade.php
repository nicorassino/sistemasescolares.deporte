<div class="px-4 py-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Generación masiva de cuotas</h1>
        <p class="text-sm text-gray-600 mt-1">Generá cuotas mensuales para uno o más grupos. No se crean duplicados por alumno y período.</p>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <form wire:submit.prevent="generar" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                    <select id="mes" wire:model.defer="mes" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @php $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']; @endphp
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ $meses[$m] }}</option>
                        @endfor
                    </select>
                    @error('mes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <select id="anio" wire:model.defer="anio" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @for ($a = now()->year - 2; $a <= now()->year + 2; $a++)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endfor
                    </select>
                    @error('anio') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="monto_base" class="block text-sm font-medium text-gray-700 mb-1">Monto base</label>
                    <input id="monto_base" type="number" step="0.01" min="0" wire:model.defer="monto_base" placeholder="0,00" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('monto_base') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
                    <input id="fecha_vencimiento" type="date" wire:model.defer="fecha_vencimiento" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('fecha_vencimiento') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="max-w-md">
                <label for="grupo_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                <select id="grupo_id" wire:model.defer="grupo_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los alumnos activos</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->name }}</option>
                    @endforeach
                </select>
                @error('grupo_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                    Generar cuotas
                </button>
            </div>
        </form>

        @if($cuotas_generadas !== null)
            <div class="mt-4 p-4 rounded-md border border-green-200 bg-green-50 text-green-800 text-sm">
                Se generaron {{ $cuotas_generadas }} cuotas exitosamente. Se omitieron {{ $cuotas_omitidas }} alumnos que ya tenían cuota.
            </div>
        @endif
    </div>
</div>
