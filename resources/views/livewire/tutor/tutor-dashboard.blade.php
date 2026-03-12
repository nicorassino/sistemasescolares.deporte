<div class="px-3 py-4 max-w-2xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-900 mb-2">Hola, {{ $tutor?->first_name }} {{ $tutor?->last_name }}</h1>

    <nav class="flex border-b border-gray-200 mb-4" role="tablist">
        <button
            type="button"
            wire:click="$set('activeSection', 'escuela')"
            class="flex-1 py-2.5 text-sm font-medium border-b-2 transition {{ $activeSection === 'escuela' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
        >
            Escuela
        </button>
        <button
            type="button"
            wire:click="$set('activeSection', 'novedades')"
            class="flex-1 py-2.5 text-sm font-medium border-b-2 transition {{ $activeSection === 'novedades' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
        >
            Novedades
        </button>
        <button
            type="button"
            wire:click="$set('activeSection', 'cuotas')"
            class="flex-1 py-2.5 text-sm font-medium border-b-2 transition {{ $activeSection === 'cuotas' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
        >
            Pago de cuotas
        </button>
    </nav>

    @if($activeSection === 'escuela')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Institucional</h2>
            <div class="flex items-center justify-center gap-4 mb-4">
                <img src="{{ asset('IMG/logo_juvenilia.jpeg') }}" alt="Logo Juvenilia" class="h-14 w-auto object-contain">
                <img src="{{ asset('IMG/logodepte.jpeg') }}" alt="Logo Deportivo" class="h-14 w-auto object-contain rounded-lg">
            </div>
            <p class="text-sm text-gray-700 mb-2">
                En la Escuela de Deportes Juvenilia, nos dedicamos a la formación integral de niños y jóvenes a través del deporte. Nuestro enfoque principal está en el desarrollo de habilidades físicas, el trabajo en equipo y la diversión sana. Formamos deportistas, pero sobre todo, formamos personas.
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-semibold">Filosofía:</span>
                Creemos que el deporte es una herramienta fundamental para forjar el carácter. Fomentamos el respeto, la disciplina y el compañerismo en cada entrenamiento y competencia. Nuestra meta es preparar a nuestros alumnos para los desafíos del futuro con una base sólida de valores.
            </p>
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
                                    src="{{ asset('storage/'.$announcement->image_path) }}"
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
            <div class="mb-4 px-3 py-2 rounded-md bg-green-50 text-green-800 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <h2 class="text-base font-semibold text-gray-900 mb-2">Cuotas pendientes</h2>

        @forelse($tutor?->students ?? [] as $student)
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 mb-2">
                    {{ $student->first_name }} {{ $student->last_name }}
                </h3>

                @php
                    $pendingFees = $student->fees->where('status', 'pending');
                    $paidFees = $student->fees->where('status', 'paid');
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

                @if($paidFees->isNotEmpty())
                    <div class="mt-3">
                        <button
                            type="button"
                            wire:click="openPaidModal({{ $student->id }})"
                            class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Ver cuotas pagadas
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-600">Aún no tenés alumnos asignados.</p>
        @endforelse
    @endif

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
                            <label for="transfer_sender_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Titular de la cuenta origen (Quien transfiere)</label>
                            <input
                                id="transfer_sender_name"
                                type="text"
                                wire:model.defer="transfer_sender_name"
                                placeholder="Ej: Juan Pérez"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('transfer_sender_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adjuntar comprobante</label>
                            <input
                                type="file"
                                wire:model="paymentProof"
                                accept="image/jpeg,image/jpg,image/png,application/pdf"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-base text-gray-700"
                            >
                            <p class="text-[11px] text-gray-500 mt-1">
                                Formatos permitidos: JPG, PNG o PDF. Tamaño máximo: 2MB.
                            </p>
                            @error('paymentProof') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
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
                            No hay cuotas pagadas para el año seleccionado.
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
