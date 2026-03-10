<div class="px-3 py-4 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-900 mb-3">Hola, {{ $tutor?->first_name }} {{ $tutor?->last_name }}</h1>
    <p class="text-sm text-gray-600 mb-4">Desde aquí podés ver las cuotas pendientes y reportar tus pagos.</p>

    @if(session('status'))
        <div class="mb-4 px-3 py-2 rounded-md bg-green-50 text-green-800 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @forelse($tutor?->students ?? [] as $student)
        <div class="mb-4">
            <h2 class="text-base font-semibold text-gray-800 mb-2">
                {{ $student->first_name }} {{ $student->last_name }}
            </h2>

            @php
                $pendingFees = $student->fees->where('status', 'pending');
            @endphp

            @if($pendingFees->isEmpty())
                <div class="bg-white rounded-lg shadow-sm px-3 py-2 text-sm text-gray-500">
                    No hay cuotas pendientes para este alumno.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pendingFees as $fee)
                        <div class="bg-white rounded-xl shadow px-3 py-3 flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase text-gray-500">Cuota</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ \Illuminate\Support\Str::upper(\Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y')) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Monto</p>
                                    <p class="text-base font-bold text-gray-900">
                                        $ {{ number_format($fee->amount, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-600">
                                <span>Vencimiento: {{ $fee->due_date->format('d/m/Y') }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-800 text-[11px] font-medium">
                                    Pendiente
                                </span>
                            </div>
                            <button
                                type="button"
                                wire:click="openPaymentModal({{ $fee->id }})"
                                class="mt-1 w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium"
                            >
                                Informar pago
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-600">Aún no tenés alumnos asignados.</p>
    @endforelse

    @if($showPaymentModal && $selectedFeeId)
        <div
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
            x-data="{ open: true }"
            x-show="open"
            x-transition
        >
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-md mx-3 max-h-[90vh] overflow-y-auto"
                 @click.outside="open = false; $wire.closePaymentModal()">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Informar pago</h2>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                        @click="open = false; $wire.closePaymentModal()"
                    >
                        &times;
                    </button>
                </div>

                <div class="px-4 py-3 space-y-4">
                    <div
                        class="bg-gray-50 rounded-xl p-3 text-xs text-gray-800 space-y-1"
                        x-data="{
                            copy(text) {
                                navigator.clipboard.writeText(text);
                            }
                        }"
                    >
                        <p class="font-semibold text-gray-900 text-sm mb-1">Datos para transferencia</p>
                        <p><span class="font-medium">Titular:</span> Yacono Emanuel Rodrigo</p>
                        <p><span class="font-medium">CUIT/CUIL:</span> 23-30658273-9</p>
                        <p><span class="font-medium">Cuenta:</span> CA $ 925 0013909602</p>
                        <div class="flex items-center justify-between gap-2">
                            <p><span class="font-medium">CBU:</span> 0200925811000013909626</p>
                            <button
                                type="button"
                                class="text-[11px] px-2 py-1 rounded-full border border-gray-300 text-gray-700"
                                @click="copy('0200925811000013909626')"
                            >
                                Copiar
                            </button>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <p><span class="font-medium">Alias:</span> JUVENILIA.FUTBOL.EMA</p>
                            <button
                                type="button"
                                class="text-[11px] px-2 py-1 rounded-full border border-gray-300 text-gray-700"
                                @click="copy('JUVENILIA.FUTBOL.EMA')"
                            >
                                Copiar
                            </button>
                        </div>
                    </div>

                    <form wire:submit.prevent="submitPaymentProof" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adjuntar comprobante</label>
                            <input
                                type="file"
                                wire:model="paymentProof"
                                accept="image/jpeg,image/jpg,image/png,application/pdf"
                                class="w-full text-sm text-gray-700"
                            >
                            <p class="text-[11px] text-gray-500 mt-1">
                                Formatos permitidos: JPG, PNG o PDF. Tamaño máximo: 2MB.
                            </p>
                            @error('paymentProof') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror>
                        </div>

                        <div class="pt-1 flex gap-2">
                            <button
                                type="submit"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium"
                            >
                                Enviar comprobante
                            </button>
                            <button
                                type="button"
                                class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-700"
                                @click="open = false; $wire.closePaymentModal()"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

