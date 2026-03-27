<div class="px-3 py-5 max-w-2xl mx-auto pb-20">

    {{-- ===== SALUDO ===== --}}
    <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-juvenilia-blue/60 mb-0.5">Bienvenido</p>
        <h1 class="text-2xl font-black text-juvenilia-blue leading-tight">
            {{ $teacher?->first_name }} {{ $teacher?->last_name }}
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Marcá asistencia y registrá cobros en efectivo.</p>
    </div>

    {{-- ===== FLASH MESSAGES ===== --}}
    @if(session('status'))
        <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-2xl bg-green-50 border-l-4 border-juvenilia-green shadow-sm">
            <svg class="w-5 h-5 text-juvenilia-green mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-2xl bg-red-50 border-l-4 border-red-500 shadow-sm">
            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    {{-- ===== FECHA ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 mb-4 flex items-center gap-3">
        <div class="p-2 bg-juvenilia-blue/10 rounded-xl">
            <svg class="w-5 h-5 text-juvenilia-blue" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
            </svg>
        </div>
        <div class="flex-1">
            <label for="date" class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Fecha</label>
            <input
                id="date"
                type="date"
                wire:model.live="selectedDate"
                class="w-full text-base font-semibold text-gray-900 bg-transparent border-0 p-0 focus:ring-0 focus:outline-none"
            >
        </div>
    </div>

    {{-- ===== SELECTOR DE GRUPO — Píldoras ===== --}}
    <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Grupo</p>
        @if($groups->isEmpty())
            <p class="text-sm text-gray-400 italic">Sin grupos asignados.</p>
        @else
            <div class="flex gap-2 overflow-x-auto pb-2 pills-scroll snap-x -mx-3 px-3">
                @foreach($groups as $g)
                    @if($selectedGroupId == $g->id)
                        <button
                            type="button"
                            wire:click="$set('selectedGroupId', {{ $g->id }})"
                            class="snap-start shrink-0 px-4 py-2 rounded-full text-sm font-semibold
                                   transition-all duration-150 active:scale-95
                                   bg-blue-700 bg-juvenilia-blue text-white shadow-md ring-2 ring-blue-300"
                        >
                            {{ $g->name }}
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="$set('selectedGroupId', {{ $g->id }})"
                            class="snap-start shrink-0 px-4 py-2 rounded-full text-sm font-semibold
                                   transition-all duration-150 active:scale-95
                                   bg-white text-gray-600 border border-gray-200
                                   hover:border-blue-700 hover:text-blue-700"
                        >
                            {{ $g->name }}
                        </button>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- ===== REPORTE ASISTENCIA ===== --}}
    @if($selectedGroupId)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3 mb-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Reporte de asistencia</p>
            <div class="flex flex-wrap items-center gap-2">
                <input
                    type="month"
                    wire:model.live="reportMonth"
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                >
                <a
                    href="{{ route('profesor.attendance-pdf.by-group-month', ['group' => $selectedGroupId, 'month' => $reportMonth]) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-xs text-gray-700 hover:bg-gray-100"
                >
                    Descargar PDF asistencia
                </a>
            </div>
        </div>
    @endif

    {{-- ===== LISTA DE ALUMNOS ===== --}}
    @if($students->isNotEmpty())
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">
                Asistencia y cobros
                <span class="ml-1 text-juvenilia-blue">({{ $students->count() }} alumnos)</span>
            </p>
            <ul class="space-y-3">
                @foreach($students as $student)
                    @php
                        $att    = $attendancesByStudent[$student->id] ?? null;
                        $status = $att?->status ?? null;
                    @endphp
                    <li wire:key="student-att-{{ $student->id }}"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden
                               transition-all duration-150 hover:shadow-md">

                        <div class="px-4 py-4">

                            {{-- Nombre + botón cobrar --}}
                            <div class="flex items-center justify-between mb-3 gap-2">
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900 text-base leading-tight truncate">
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    wire:click="openCashModal({{ $student->id }})"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl
                                           bg-orange-500 bg-juvenilia-orange text-white text-xs font-bold
                                           shadow-sm hover:brightness-110 active:scale-95
                                           transition-all duration-150"
                                >
                                    {{-- Ícono billete --}}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                    </svg>
                                    Cobrar
                                </button>
                            </div>

                            {{-- Botones P / A circulares --}}
                            <div class="flex items-center gap-3">

                                {{-- PRESENTE --}}
                                <button
                                    type="button"
                                    wire:click="toggleAttendance({{ $student->id }}, 'P')"
                                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 rounded-2xl
                                           transition-all duration-150 active:scale-95
                                           {{ $status === 'P'
                                              ? 'bg-green-600 bg-juvenilia-green text-white shadow-md ring-4 ring-green-300'
                                              : 'bg-gray-100 text-gray-400 hover:bg-green-50 hover:text-juvenilia-green' }}"
                                >
                                    <span class="text-lg leading-none">
                                        @if($status === 'P')
                                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        @else
                                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="text-xs font-bold tracking-wide">Presente</span>
                                </button>

                                {{-- AUSENTE --}}
                                <button
                                    type="button"
                                    wire:click="toggleAttendance({{ $student->id }}, 'A')"
                                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 rounded-2xl
                                           transition-all duration-150 active:scale-95
                                           {{ $status === 'A'
                                              ? 'bg-red-500 text-white shadow-md ring-4 ring-red-300'
                                              : 'bg-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500' }}"
                                >
                                    <span class="text-lg leading-none">
                                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </span>
                                    <span class="text-xs font-bold tracking-wide">Ausente</span>
                                </button>

                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

    @elseif($selectedGroupId)
        <div class="mt-4 flex flex-col items-center gap-2 py-12 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            <p class="text-sm text-gray-400 font-medium">Sin alumnos en este grupo.</p>
        </div>

    @elseif($groups->isNotEmpty())
        <div class="mt-2 flex flex-col items-center gap-2 py-12 bg-white/60 rounded-2xl border border-dashed border-gray-200">
            <svg class="w-10 h-10 text-juvenilia-blue/30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
            <p class="text-sm text-juvenilia-blue/50 font-medium">Seleccioná un grupo para comenzar.</p>
        </div>
    @endif

    {{-- ===== MODAL DE COBRO EN EFECTIVO ===== --}}
    @if($showCashModal && $cashStudentId)
        <div
            class="fixed inset-0 z-40 flex items-end sm:items-center justify-center"
            x-data="{ open: true }"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{-- Overlay --}}
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="open = false; $wire.closeCashModal()"
            ></div>

            {{-- Panel --}}
            <div
                class="relative bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-md
                       max-h-[92vh] overflow-y-auto"
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-8"
                @click.stop
            >

                {{-- Handle (mobile) --}}
                <div class="flex justify-center pt-3 pb-0 sm:hidden">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>

                {{-- Header del modal --}}
                <div class="bg-blue-700 bg-juvenilia-blue px-5 py-4 sm:rounded-t-3xl flex items-center justify-between mt-2 sm:mt-0">
                    <div class="flex items-center gap-2.5">
                        <div class="p-1.5 bg-white/20 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                        </div>
                        <h2 class="text-white font-bold text-base">Cobro en efectivo</h2>
                    </div>
                    <button
                        type="button"
                        class="text-white/70 hover:text-white transition-colors p-1"
                        @click="open = false; $wire.closeCashModal()"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Cuerpo del modal --}}
                <div class="px-5 py-5 space-y-4">

                    @if($cashFee)

                        {{-- Card de deuda --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-widest text-juvenilia-blue/60 mb-1">Deuda pendiente</p>
                            <p class="text-4xl font-black text-juvenilia-blue tracking-tight">
                                $ {{ number_format((float) $cashFee->amount - (float) $cashFee->paid_amount, 2, ',', '.') }}
                            </p>
                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                <span class="bg-gray-100 rounded-full px-2 py-0.5">
                                    Cuota total: $ {{ number_format($cashFee->amount, 2, ',', '.') }}
                                </span>
                                @if((float) $cashFee->paid_amount > 0)
                                    <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5">
                                        Pagado: $ {{ number_format($cashFee->paid_amount, 2, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Formulario --}}
                        <form wire:submit.prevent="processCashPayment" class="space-y-4">
                            <div>
                                <label for="cash_amount" class="block text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">
                                    Monto recibido
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-black text-gray-300">$</span>
                                    <input
                                        id="cash_amount"
                                        type="text"
                                        inputmode="decimal"
                                        wire:model="cashAmount"
                                        placeholder="0,00"
                                        class="w-full border-2 border-gray-200 focus:border-juvenilia-blue rounded-2xl
                                               pl-10 pr-4 py-4 text-2xl font-bold text-center text-gray-900
                                               bg-gray-50 focus:bg-white
                                               outline-none transition-all duration-150"
                                    >
                                </div>
                                @error('cashAmount')
                                    <p class="flex items-center gap-1 text-red-500 text-xs font-medium mt-1.5">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button
                                    type="button"
                                    class="px-4 py-4 rounded-2xl border-2 border-gray-200 text-gray-500 font-semibold text-sm
                                           hover:border-gray-300 active:scale-95 transition-all duration-150"
                                    @click="open = false; $wire.closeCashModal()"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="flex-1 flex items-center justify-center gap-2 py-4 rounded-2xl
                                           bg-green-600 bg-juvenilia-green text-white font-bold text-base
                                           shadow-lg shadow-green-200
                                           hover:brightness-110 active:scale-95
                                           transition-all duration-150"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                    </svg>
                                    Registrar cobro
                                </button>
                            </div>
                        </form>

                    @else

                        {{-- Sin cuota pendiente --}}
                        <div class="flex flex-col items-center gap-3 py-6">
                            <div class="p-4 bg-green-50 rounded-full">
                                <svg class="w-8 h-8 text-juvenilia-green" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-gray-700">Sin deuda pendiente</p>
                            <p class="text-sm text-gray-400 text-center">Este alumno no tiene cuotas pendientes en este grupo.</p>
                        </div>
                        <button
                            type="button"
                            wire:click="closeCashModal"
                            class="w-full py-4 rounded-2xl border-2 border-gray-200 text-gray-600 font-semibold
                                   hover:bg-gray-50 active:scale-95 transition-all duration-150"
                        >
                            Cerrar
                        </button>

                    @endif

                </div>
            </div>
        </div>
    @endif

</div>
