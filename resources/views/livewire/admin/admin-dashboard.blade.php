<div class="px-4 py-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-semibold text-gray-900 mb-1">Panel de inicio</h1>
    <p class="text-sm text-gray-600 mb-6">Resumen de información relevante.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('admin.treasury') }}" class="block bg-white rounded-xl shadow border border-gray-100 p-4 hover:border-blue-200 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pagos a revisar</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $pendingReviewCount }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">Comprobantes en revisión</p>
                </div>
                @if($pendingReviewCount > 0)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">
                        {{ $pendingReviewCount }}
                    </span>
                @endif
            </div>
        </a>

        @if($lastPeriodStats)
            <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Última cuota generada</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $lastPeriodStats->period)->locale('es')->isoFormat('MMMM YYYY')) }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-semibold text-green-600">{{ $lastPeriodStats->paid }}</span> pagadas
                    de <span class="font-semibold">{{ $lastPeriodStats->total }}</span> cuotas
                </p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Última cuota generada</p>
                <p class="text-sm text-gray-500 mt-1">Aún no hay cuotas generadas.</p>
            </div>
        @endif

        <a href="{{ route('admin.announcements') }}" class="block bg-white rounded-xl shadow border border-gray-100 p-4 hover:border-blue-200 transition">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Novedades visibles</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $recentAnnouncements->count() }}</p>
            <p class="text-xs text-gray-600 mt-0.5">Últimas publicadas (máx. 15)</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Novedades que ven los padres</h2>
                <a href="{{ route('admin.announcements') }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition cursor-pointer">Gestionar</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($recentAnnouncements as $announcement)
                    <div class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $announcement->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $announcement->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ Str::limit(strip_tags($announcement->content), 80) }}</p>
                    </div>
                @empty
                    <div class="px-4 py-6 text-sm text-gray-500">No hay novedades publicadas.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Última cuota por grupo</h2>
                @if($lastPeriodStats)
                    <span class="text-xs text-gray-500">{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $lastPeriodStats->period)->locale('es')->isoFormat('MMMM YYYY')) }}</span>
                @endif
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @if($lastPeriodByGroup->isEmpty())
                    <div class="px-4 py-6 text-sm text-gray-500">No hay cuotas por grupo para mostrar.</div>
                @else
                    @foreach($lastPeriodByGroup as $row)
                        <div class="px-4 py-3 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900">{{ $row->group_name }}</span>
                            <span class="text-sm text-gray-600">
                                <span class="text-green-600 font-medium">{{ $row->paid }}</span> / {{ $row->total }} pagadas
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
