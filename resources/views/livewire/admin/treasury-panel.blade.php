<div class="px-4 py-6 max-w-7xl mx-auto">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900">Tesorería</h1>
        <p class="text-sm text-gray-600 mt-1">Pagos en revisión y historial por año, mes y grupo.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm">
            {{ session('warning') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Año</label>
            <select
                wire:model.live="filter_year"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
            >
                <option value="">Todos</option>
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Mes</label>
            <select
                wire:model.live="filter_month"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
            >
                <option value="">Todos</option>
                @foreach(['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $idx => $name)
                    <option value="{{ $idx + 1 }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Grupo</label>
            <select
                wire:model.live="filter_group_id"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
            >
                <option value="">Todos</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <button
            type="button"
            wire:click="clearFilters"
            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
            Limpiar filtros
        </button>
    </div>

    <div class="border-b border-gray-200 mb-4">
        <nav class="flex gap-1">
            <button
                type="button"
                wire:click="setTab('pending')"
                class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'pending' ? 'bg-white border border-b-0 border-gray-200 text-blue-600 -mb-px' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                En revisión ({{ $payments->count() }})
            </button>
            <button
                type="button"
                wire:click="setTab('history')"
                class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition {{ $activeTab === 'history' ? 'bg-white border border-b-0 border-gray-200 text-blue-600 -mb-px' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                Historial
            </button>
        </nav>
    </div>

    @if($activeTab === 'pending')
    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Alumno</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Tutor</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Titular de la transferencia</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Monto</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Comprobante</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        @php
                            $student = $payment->fee->student ?? null;
                            $tutor = $payment->tutor;
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-gray-900">
                                @if($student)
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $tutor->last_name ?? '' }}, {{ $tutor->first_name ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $payment->transfer_sender_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 font-medium">
                                $ {{ number_format($payment->amount_reported, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($payment->evidence_file_path)
                                    <a
                                        href="{{ route('admin.treasury.payment-file', $payment->id) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
                                    >
                                        Ver comprobante
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button
                                    type="button"
                                    wire:click="approvePayment({{ $payment->id }})"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-green-600 text-white font-medium hover:bg-green-700"
                                >
                                    Aprobar
                                </button>
                                <button
                                    type="button"
                                    wire:click="rejectPayment({{ $payment->id }})"
                                    wire:confirm="¿Rechazar este pago y eliminar el comprobante?"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-600 text-white font-medium hover:bg-red-700"
                                >
                                    Rechazar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay pagos en revisión.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeTab === 'history')
    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Historial de pagos revisados</h2>
            <p class="text-xs text-gray-500">Últimos 100 pagos aprobados o rechazados (según filtros).</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Alumno</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Tutor</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Monto</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">N. recibo</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Revisado por</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha revisión</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Comprobante</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($historyPayments as $payment)
                        @php
                            $student = $payment->fee->student ?? null;
                            $tutor = $payment->tutor;
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-gray-900">
                                @if($student)
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $tutor->last_name ?? '' }}, {{ $tutor->first_name ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 font-medium">
                                $ {{ number_format($payment->amount_reported, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($payment->status === 'approved')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Aprobado</span>
                                @elseif($payment->status === 'rejected')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Rechazado</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $payment->fee?->receipt_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $payment->reviewer?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $payment->reviewed_at ? $payment->reviewed_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($payment->evidence_file_path)
                                    <a
                                        href="{{ route('admin.treasury.payment-file', $payment->id) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
                                    >
                                        Ver comprobante
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
                                    onclick="if (confirm('¿Volver este pago al estado de revisión? Se ajustará el estado de la cuota si corresponde.')) { @this.call('resetToPending', {{ $payment->id }}) }"
                                >
                                    Volver a revisión
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                No hay pagos aprobados o rechazados aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
