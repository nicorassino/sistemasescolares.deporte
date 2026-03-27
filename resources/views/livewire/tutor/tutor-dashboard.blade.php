<div class="px-3 py-5 max-w-2xl mx-auto pb-20">
    <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-juvenilia-blue/70 mb-0.5">Bienvenido</p>
        <h1 class="text-2xl font-black text-juvenilia-blue leading-tight">
            {{ $tutor?->first_name }} {{ $tutor?->last_name }}
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Consultá novedades y gestioná el pago de cuotas.</p>
    </div>

    <nav class="mb-4">
        <div class="inline-flex bg-white rounded-full shadow-sm border border-gray-100 p-1.5 w-full">
            <button
                type="button"
                wire:click="$set('activeSection', 'escuela')"
                class="flex-1 px-3 py-2 rounded-full text-xs sm:text-sm font-semibold text-center
                       transition-all duration-150 active:scale-95
                       {{ $activeSection === 'escuela'
                          ? 'bg-blue-700 bg-juvenilia-blue text-white shadow'
                          : 'text-gray-500 hover:text-juvenilia-blue hover:bg-gray-50' }}"
            >
                Escuela
            </button>
            <button
                type="button"
                wire:click="$set('activeSection', 'novedades')"
                class="flex-1 px-3 py-2 rounded-full text-xs sm:text-sm font-semibold text-center
                       transition-all duration-150 active:scale-95
                       {{ $activeSection === 'novedades'
                          ? 'bg-blue-700 bg-juvenilia-blue text-white shadow'
                          : 'text-gray-500 hover:text-juvenilia-blue hover:bg-gray-50' }}"
            >
                Novedades
            </button>
            <button
                type="button"
                wire:click="$set('activeSection', 'cuotas')"
                class="flex-1 px-3 py-2 rounded-full text-xs sm:text-sm font-semibold text-center
                       transition-all duration-150 active:scale-95
                       {{ $activeSection === 'cuotas'
                          ? 'bg-blue-700 bg-juvenilia-blue text-white shadow'
                          : 'text-gray-500 hover:text-juvenilia-blue hover:bg-gray-50' }}"
            >
                Pago de cuotas
            </button>
        </div>
    </nav>

    @if($activeSection === 'escuela')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Hero de la escuela de fútbol --}}
            <div class="flex flex-col items-center gap-3 py-8 px-6 bg-gradient-to-b from-blue-900 to-blue-700">
                <img
                    src="{{ asset('IMG/logodepte.png') }}"
                    alt="Escuela de Fútbol Juvenilia"
                    class="h-32 w-auto object-contain drop-shadow-xl"
                >
                <p class="text-white/80 text-sm font-medium text-center">Escuela de Fútbol</p>
            </div>

            <div class="p-5 space-y-3">
                <p class="text-sm text-gray-700 leading-relaxed">
                    En la Escuela de Deportes Juvenilia, nos dedicamos a la formación integral de niños y jóvenes a través del deporte. Nuestro enfoque principal está en el desarrollo de habilidades físicas, el trabajo en equipo y la diversión sana. Formamos deportistas, pero sobre todo, formamos personas.
                </p>
                <p class="text-sm text-gray-700 leading-relaxed">
                    <span class="font-semibold">Filosofía:</span>
                    Creemos que el deporte es una herramienta fundamental para forjar el carácter. Fomentamos el respeto, la disciplina y el compañerismo en cada entrenamiento y competencia. Nuestra meta es preparar a nuestros alumnos para los desafíos del futuro con una base sólida de valores.
                </p>
                <p class="text-sm text-gray-700 leading-relaxed">
                    <span class="font-semibold">Consultas:</span>
                    Para comunicación administrativa escribinos a
                    <strong>juvefutbol@institutojuvenilia.edu.ar</strong>.
                </p>

            </div>
        </div>
    @endif

    @if($activeSection === 'novedades')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Novedades</h2>
            @if(($announcements ?? collect())->isEmpty())
                <p class="text-sm text-gray-500">No hay comunicados recientes.</p>
            @else
                <div class="space-y-3">
                    @foreach($announcements as $announcement)
                        <article class="border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                            @if($announcement->image_path)
                                <img
                                    src="{{ route('announcements.image', $announcement) }}"
                                    alt="Imagen de la novedad"
                                    class="w-full h-40 object-cover"
                                >
                            @endif
                            <div class="px-3 py-3 space-y-1.5">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    {{ $announcement->title }}
                                </h3>
                                <p class="text-[11px] text-gray-500">
                                    {{ $announcement->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-sm text-gray-700 whitespace-pre-line">
                                    {{ $announcement->content }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($activeSection === 'cuotas')
        @if(session('status'))
            <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-2xl bg-green-50 border-l-4 border-green-600 border-juvenilia-green shadow-sm text-sm">
                <svg class="w-5 h-5 text-green-600 text-juvenilia-green mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        <h2 class="text-base font-semibold text-gray-900 mb-2">Cuotas pendientes</h2>
        <p class="text-xs text-gray-600 mb-3">
            Si necesitás ayuda con pagos, escribinos a
            <strong>juvefutbol@institutojuvenilia.edu.ar</strong>.
        </p>

        @forelse($tutor?->students ?? [] as $student)
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-2">
                    {{ $student->first_name }} {{ $student->last_name }}
                </h3>

                @php
                    $pendingFees = $student->fees->whereIn('status', ['pending', 'partial']);
                    $paidFees = $student->fees->where('status', 'paid')->sortByDesc('period');
                @endphp

                @if($pendingFees->isEmpty())
                    <div class="bg-white rounded-2xl shadow-sm px-3 py-2 text-sm text-gray-500">
                        No hay cuotas pendientes para este alumno.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($pendingFees as $fee)
                            @php
                                $existingPayment = $fee->payments->first();
                            @endphp
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-3 py-3 flex flex-col gap-2">
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
                                    class="mt-1 w-full inline-flex items-center justify-center px-3 py-2 rounded-xl bg-orange-500 bg-juvenilia-orange text-white text-sm font-semibold
                                           shadow-sm hover:brightness-110 active:scale-95 transition-all duration-150"
                                >
                                    {{ $existingPayment ? 'Agregar comprobante de pago' : 'Informar pago' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3">
                    <button
                        type="button"
                        wire:click="openPaidModal({{ $student->id }})"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50 active:scale-95 transition-all duration-150"
                    >
                        Ver cuotas pagadas
                        <span class="inline-flex items-center justify-center min-w-5 h-5 rounded-full bg-gray-100 text-[11px] font-semibold text-gray-700">
                            {{ $paidFees->count() }}
                        </span>
                    </button>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-600">Aún no tenés alumnos asignados.</p>
        @endforelse
    @endif

    @if($showPaymentModal && $selectedFeeId)
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
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="open = false; $wire.closePaymentModal()"
            ></div>

            <div class="relative bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-md mx-3 max-h-[92vh] overflow-y-auto"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-8"
                 @click.stop>
                <div class="flex justify-center pt-3 pb-0 sm:hidden">
                    <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
                </div>
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
                        @php
                            $selectedFee = optional($tutor?->students)->flatMap(fn($s) => $s->fees)->firstWhere('id', $selectedFeeId);
                            $remainingDebt = $selectedFee ? max((float) $selectedFee->amount - (float) $selectedFee->paid_amount, 0) : 0;
                            $paymentHistory = $selectedFee
                                ? $selectedFee->payments
                                    ->where('tutor_id', $tutor?->id)
                                    ->sortByDesc('id')
                                : collect();
                        @endphp

                        @if($selectedFee)
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700 space-y-1">
                                <p><span class="font-semibold">Deuda actual:</span> $ {{ number_format($remainingDebt, 2, ',', '.') }}</p>
                                <p><span class="font-semibold">Sugerencia:</span> ingresá el saldo pendiente para cerrar la cuota.</p>
                            </div>
                        @endif

                        <div>
                            <label for="transfer_sender_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Titular de la cuenta origen (Quien transfiere)</label>
                            <input
                                id="transfer_sender_name"
                                type="text"
                                wire:model.defer="transfer_sender_name"
                                placeholder="Ej: Juan Pérez"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-base focus:border-juvenilia-blue focus:ring-juvenilia-blue"
                            >
                            @error('transfer_sender_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="transfer_amount" class="block text-sm font-medium text-gray-700 mb-1">Monto transferido</label>
                            <input
                                id="transfer_amount"
                                type="text"
                                inputmode="decimal"
                                wire:model.defer="transfer_amount"
                                placeholder="Ej: 12000,50"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-base focus:border-juvenilia-blue focus:ring-juvenilia-blue"
                            >
                            @error('transfer_amount') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adjuntar comprobante</label>
                            <input
                                type="file"
                                wire:model="paymentProof"
                                accept="image/jpeg,image/jpg,image/png,application/pdf"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-base text-gray-700 bg-white
                                       file:mr-3 file:rounded-lg file:border file:border-gray-300 file:bg-gray-200 file:px-4 file:py-2.5
                                       file:text-sm file:font-medium file:text-gray-800 hover:file:bg-gray-300
                                       cursor-pointer"
                            >
                            <p class="text-[11px] text-gray-500 mt-1">
                                Formatos permitidos: JPG, PNG o PDF. Tamaño máximo: 2MB.
                            </p>
                            @error('paymentProof') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($paymentHistory->isNotEmpty())
                            <div class="rounded-xl border border-gray-200 p-3">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Transferencias enviadas para esta cuota</p>
                                <div class="space-y-1">
                                    @foreach($paymentHistory->take(5) as $p)
                                        <div class="text-[11px] text-gray-600 flex items-center justify-between gap-2">
                                            <span>
                                                {{ $p->created_at?->format('d/m/Y H:i') }} - $ {{ number_format((float) $p->amount_reported, 2, ',', '.') }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                {{ $p->status === 'pending_review' ? 'En revisión' : ($p->status === 'approved' ? 'Aprobado' : 'Rechazado') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-1 flex gap-2">
                            <button
                                type="submit"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-xl bg-green-600 bg-juvenilia-green text-white text-sm font-semibold
                                       shadow-sm hover:brightness-110 active:scale-95 transition-all duration-150"
                            >
                                Enviar comprobante
                            </button>
                            <button
                                type="button"
                                class="px-3 py-2 rounded-xl border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 active:scale-95 transition-all duration-150"
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

    @if($showPaidModal && $paidStudentId)
        @php
            $paidStudent = optional($tutor?->students)->firstWhere('id', $paidStudentId);
            $allPaidFees = $paidStudent ? $paidStudent->fees->where('status', 'paid') : collect();
            $years = $allPaidFees
                ->map(fn($fee) => substr($fee->period, 0, 4))
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();
            $filteredPaidFees = $allPaidFees;
            if ($paidFilterYear) {
                $filteredPaidFees = $filteredPaidFees->filter(fn($fee) => substr($fee->period, 0, 4) == (string) $paidFilterYear);
            }
            $filteredPaidFees = $filteredPaidFees->sortByDesc('period');
        @endphp
        <div
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
            x-data="{ openPaid: true }"
            x-show="openPaid"
            x-transition
        >
            <div class="bg-white rounded-2xl shadow-lg w-full max-w-md mx-3 max-h-[90vh] overflow-y-auto"
                 @click.outside="openPaid = false; $wire.closePaidModal()">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">
                        Cuotas pagadas
                        @if($paidStudent)
                            — {{ $paidStudent->first_name }} {{ $paidStudent->last_name }}
                        @endif
                    </h2>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                        @click="openPaid = false; $wire.closePaidModal()"
                    >
                        &times;
                    </button>
                </div>

                <div class="px-4 py-3 space-y-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-700">Filtrar por año</label>
                        <select
                            wire:model.live="paidFilterYear"
                            class="border border-gray-300 rounded-md px-2 py-1.5 text-xs"
                        >
                            <option value="">Todos</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($filteredPaidFees->isEmpty())
                        <p class="text-xs text-gray-500">
                            No hay cuotas pagadas registradas para este alumno en el año seleccionado.
                        </p>
                    @else
                        <div class="space-y-2">
                            @foreach($filteredPaidFees as $fee)
                                <div class="bg-gray-50 rounded-lg px-3 py-2 flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ \Illuminate\Support\Str::upper(\Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y')) }}
                                        </p>
                                        @if($fee->paid_at)
                                            <p class="text-[11px] text-gray-500">
                                                Pagada el {{ $fee->paid_at->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px] font-medium">
                                            Pagado
                                        </span>
                                        <a
                                            href="{{ route('receipt.download', $fee) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-md bg-blue-600 text-white text-[11px] font-medium hover:bg-blue-700"
                                        >
                                            Ver comprobante
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
