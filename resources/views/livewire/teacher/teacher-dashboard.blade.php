<div class="px-3 py-4 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-900 mb-1">Hola, {{ $teacher?->first_name }} {{ $teacher?->last_name }}</h1>
    <p class="text-sm text-gray-600 mb-4">Marcá asistencia y registrá cobros en efectivo.</p>

    @if(session('status'))
        <div class="mb-4 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
            {{ session('status') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        <div>
            <label for="group" class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
            <select
                id="group"
                wire:model.live="selectedGroupId"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base bg-white"
            >
                <option value="">Seleccionar grupo</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
            <input
                id="date"
                type="date"
                wire:model.live="selectedDate"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base"
            >
        </div>
    </div>

    @if($students->isNotEmpty())
        <div class="mt-6">
            <h2 class="text-base font-semibold text-gray-800 mb-3">Asistencia y cobros</h2>
            <ul class="space-y-3">
                @foreach($students as $student)
                    @php
                        $att = $attendancesByStudent[$student->id] ?? null;
                        $status = $att?->status ?? null;
                    @endphp
                    <li wire:key="student-att-{{ $student->id }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-3 py-3 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-gray-900">
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                </p>
                                <button
                                    type="button"
                                    wire:click="openCashModal({{ $student->id }})"
                                    class="text-xs px-2.5 py-1.5 rounded-lg bg-amber-500 text-white font-medium"
                                >
                                    Cobrar efectivo
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    wire:click="toggleAttendance({{ $student->id }}, 'P')"
                                    class="flex-1 py-3 rounded-xl text-sm font-semibold transition {{ $status === 'P' ? 'bg-green-600 text-white ring-2 ring-green-600' : 'bg-gray-100 text-gray-600 hover:bg-green-50' }}"
                                >
                                    Presente
                                </button>
                                <button
                                    type="button"
                                    wire:click="toggleAttendance({{ $student->id }}, 'A')"
                                    class="flex-1 py-3 rounded-xl text-sm font-semibold transition {{ $status === 'A' ? 'bg-red-600 text-white ring-2 ring-red-600' : 'bg-gray-100 text-gray-600 hover:bg-red-50' }}"
                                >
                                    Ausente
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @elseif($selectedGroupId)
        <p class="mt-6 text-sm text-gray-500">No hay alumnos en este grupo o no hay asistencia para cargar.</p>
    @endif

    @if($showCashModal && $cashStudentId)
        <div
            class="fixed inset-0 z-40 flex items-end sm:items-center justify-center bg-black/40"
            x-data="{ open: true }"
            x-show="open"
            x-transition
        >
            <div
                class="bg-white rounded-t-2xl sm:rounded-2xl shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto"
                @click.outside="open = false; $wire.closeCashModal()"
            >
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Cobrar efectivo</h2>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                        @click="open = false; $wire.closeCashModal()"
                    >
                        &times;
                    </button>
                </div>

                <div class="px-4 py-4 space-y-4">
                    @if($cashFee)
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Deuda actual</p>
                            <p class="text-xl font-bold text-gray-900">
                                $ {{ number_format((float) $cashFee->amount - (float) $cashFee->paid_amount, 2, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-600 mt-0.5">
                                Cuota total $ {{ number_format($cashFee->amount, 2, ',', '.') }}
                                @if((float) $cashFee->paid_amount > 0)
                                    (pagado $ {{ number_format($cashFee->paid_amount, 2, ',', '.') }})
                                @endif
                            </p>
                        </div>

                        <form wire:submit.prevent="processCashPayment" class="space-y-3">
                            <div>
                                <label for="cash_amount" class="block text-sm font-medium text-gray-700 mb-1">Monto recibido</label>
                                <input
                                    id="cash_amount"
                                    type="text"
                                    inputmode="decimal"
                                    wire:model="cashAmount"
                                    placeholder="0,00"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base"
                                >
                                @error('cashAmount')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button
                                    type="submit"
                                    class="flex-1 inline-flex justify-center px-3 py-2.5 rounded-lg bg-green-600 text-white text-sm font-medium"
                                >
                                    Registrar cobro
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-700"
                                    @click="open = false; $wire.closeCashModal()"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-gray-500">No hay cuota pendiente para este alumno.</p>
                        <button
                            type="button"
                            wire:click="closeCashModal"
                            class="w-full py-2 rounded-lg border border-gray-300 text-sm"
                        >
                            Cerrar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
