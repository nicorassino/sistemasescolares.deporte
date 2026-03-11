<div class="px-4 py-6 max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Tesorería</h1>
        <p class="text-sm text-gray-600 mt-1">Pagos en revisión. Aprobá o rechazá cada comprobante.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
            {{ session('status') }}
        </div>
    @endif

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
</div>
