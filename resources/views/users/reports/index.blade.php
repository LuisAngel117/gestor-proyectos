@extends('layouts.app')

@section('title', 'Mis reportes')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis reportes</h2>
            <p class="text-sm text-gray-600 mt-1">Genera tu historial y tu perfil en PDF.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                    <select id="report-type" class="form-input text-sm w-48">
                        @php($selectedType = $filters['type'] ?? '')
                        <option value="">Todos</option>
                        <option value="auth" @selected($selectedType === 'auth')>Auth</option>
                        <option value="team" @selected($selectedType === 'team')>Equipo</option>
                        <option value="project" @selected($selectedType === 'project')>Proyecto</option>
                        <option value="sprint" @selected($selectedType === 'sprint')>Sprint</option>
                        <option value="task" @selected($selectedType === 'task')>Tarea</option>
                        <option value="checklist" @selected($selectedType === 'checklist')>Checklist</option>
                        <option value="timer" @selected($selectedType === 'timer')>Timer</option>
                        <option value="time" @selected($selectedType === 'time')>Tiempo manual</option>
                        <option value="message" @selected($selectedType === 'message')>Mensaje</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Acción</label>
                    <input id="report-action" type="text" class="form-input text-sm w-60"
                           placeholder="Ej: task.create"
                           value="{{ $filters['action'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rango</label>
                    @php($selectedRange = $filters['range'] ?? '')
                    <select id="report-range" class="form-input text-sm w-40">
                        <option value="">Todo</option>
                        <option value="today" @selected($selectedRange === 'today')>Hoy</option>
                        <option value="7d" @selected($selectedRange === '7d')>Últimos 7 días</option>
                        <option value="30d" @selected($selectedRange === '30d')>Últimos 30 días</option>
                    </select>
                </div>
                <button type="button" class="btn-secondary text-sm" onclick="applyReportFilters()">
                    Filtrar
                </button>
                <button type="button" class="btn-secondary text-sm" onclick="clearReportFilters()">
                    Limpiar
                </button>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-50 text-slate-700 border border-slate-200 text-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 11h8M8 15h6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ $totalCount }} eventos encontrados
                </span>
                <span class="text-xs text-gray-500">Los filtros se aplican al PDF de historial.</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <a href="{{ route('reports.history', request()->query()) }}"
           class="group card hover:shadow-lg transition-shadow">
            <div class="card-body flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Historial de actividades</h3>
                    <p class="text-sm text-gray-600 mt-1">Descarga tus acciones recientes en el sistema.</p>
                </div>
                <span class="text-sm font-medium text-indigo-600 group-hover:translate-x-1 transition-transform">PDF</span>
            </div>
        </a>

        <a href="{{ route('reports.profile') }}"
           class="group card hover:shadow-lg transition-shadow">
            <div class="card-body flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 20a6 6 0 0112 0" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Perfil completo</h3>
                    <p class="text-sm text-gray-600 mt-1">Resumen de tu información, equipos y proyectos.</p>
                </div>
                <span class="text-sm font-medium text-emerald-600 group-hover:translate-x-1 transition-transform">PDF</span>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Vista previa (últimos 5)</h3>
            @if($previewLogs->isEmpty())
                <p class="text-sm text-gray-500">No hay eventos con estos filtros.</p>
            @else
                <div class="space-y-2 text-sm">
                    @foreach($previewLogs as $log)
                        <div class="flex flex-wrap items-start justify-between gap-2 border border-gray-200 rounded-lg px-3 py-2">
                            <div>
                                <p class="text-gray-900 font-medium">{{ $log->action }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : 'N/A' }}
                                </p>
                            </div>
                            <span class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function applyReportFilters() {
        const type = document.getElementById('report-type')?.value || '';
        const action = document.getElementById('report-action')?.value || '';
        const range = document.getElementById('report-range')?.value || '';
        const params = new URLSearchParams();
        if (type) params.set('type', type);
        if (action) params.set('action', action);
        if (range) params.set('range', range);
        window.location.href = `{{ route('reports.index') }}${params.toString() ? `?${params.toString()}` : ''}`;
    }
    function clearReportFilters() {
        window.location.href = `{{ route('reports.index') }}`;
    }
</script>
@endsection
