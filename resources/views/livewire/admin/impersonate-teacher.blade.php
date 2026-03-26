<div class="px-4 py-6 max-w-2xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Ingresar como profesor</h1>

    <div class="bg-white rounded-lg shadow p-4">
        @if(session('status'))
            <div class="mb-4 px-4 py-3 rounded-2xl bg-green-50 border-l-4 border-green-600 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit.prevent="enterTeacher" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Elegir profesor</label>
                <select
                    wire:model.defer="teacherId"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
                    <option value="">-- Seleccionar --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->last_name }}, {{ $t->first_name }}
                            @if($t->email)
                                ({{ $t->email }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('teacherId')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg"
                    @if(empty($teachers)) disabled @endif
                >
                    Entrar
                </button>
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg inline-flex items-center"
                >
                    Cancelar
                </a>
            </div>
        </form>

        @if($teachers->isEmpty())
            <p class="text-sm text-gray-500 mt-4">No hay profesores con acceso de portal configurado.</p>
        @endif
    </div>
</div>

