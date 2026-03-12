<div class="px-4 py-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Gestión de deudas</h1>
        <p class="text-sm text-gray-600 mt-1">Filtrá cuotas y enviá recordatorios por correo. Descargá recibos de pagos acreditados.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            {{ session('status') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow border border-gray-100 p-4 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Filtros</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Alumno / Tutor</label>
                <input
                    type="text"
                    wire:model.live="studentSearch"
                    placeholder="Buscar por alumno (apellido, nombre, DNI) o tutor (DNI/email)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
                <p class="text-[11px] text-gray-500 mt-1">
                    Si el alumno comparte tutor, también se cargarán las deudas de los demás alumnos de ese tutor (indicando el alumno en cada fila).
                </p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Mes</label>
                <select wire:model.live="filterMonth" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Año</label>
                <input type="number" wire:model.live="filterYear" placeholder="Ej. 2026" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" min="2020" max="2030">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Grupo</label>
                <select wire:model.live="filterGroupId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select wire:model.live="filterStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="partial">Parcial</option>
                    <option value="paid">Pagado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-100 p-4 mb-4 flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-gray-700">Exportar a PDF:</span>
        <div class="flex flex-wrap items-center gap-2">
            <label class="text-xs text-gray-600">Por grupo</label>
            <select
                wire:model.live="pdfGroupId"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
            >
                <option value="">Seleccionar grupo...</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
            @if($pdfGroupId)
                <a
                    href="{{ route('admin.debt-pdf.group', $pdfGroupId) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-700 text-white text-sm font-medium hover:bg-gray-800"
                >
                    Descargar PDF del grupo
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Alumno</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Tutor</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Mes / Año</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Monto</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($fees as $fee)
                        @php
                            $student = $fee->student;
                            $tutor = $student->tutors->where('pivot.is_primary', true)->first() ?? $student->tutors->first();
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-gray-900">
                                {{ $student->last_name }}, {{ $student->first_name }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                @if($tutor)
                                    {{ $tutor->last_name }}, {{ $tutor->first_name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-900">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 font-medium">
                                $ {{ number_format($fee->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($fee->status === 'paid')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Pagado</span>
                                @elseif($fee->status === 'partial')
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Parcial</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-col items-end gap-1">
                                    <a
                                        href="{{ route('admin.debt-pdf.student', $student) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50"
                                    >
                                        PDF deuda
                                    </a>
                                    @if(in_array($fee->status, ['paid', 'partial'], true))
                                        @php
                                            $receiptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('receipt.download.signed', now()->addMinutes(10), ['fee' => $fee]);
                                        @endphp
                                        <a
                                            href="{{ $receiptUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-600 text-white text-xs font-medium hover:bg-blue-700"
                                        >
                                            Ver recibo
                                        </a>
                                    @endif
                                    @if(in_array($fee->status, ['pending', 'partial'], true))
                                        <button
                                            type="button"
                                            wire:click="sendReminder({{ $fee->id }})"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-amber-600 text-white text-xs font-medium hover:bg-amber-700"
                                        >
                                            Enviar recordatorio
                                        </button>
                                        @if($fee->last_reminder_sent_at)
                                            <span class="text-xs text-gray-500">Últ. recordatorio: {{ $fee->last_reminder_sent_at->format('d/m/Y H:i') }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay cuotas que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
