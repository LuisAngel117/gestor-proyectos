@extends('layouts.app')

@section('title', 'Calendario - ' . $project->name)

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.index') }}" class="hover:text-primary-600">Proyectos</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">{{ $project->name }}</a>
                <span class="mx-1">/</span>
                <span>Calendario</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendario</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
@php
    $totalTasksMonth = collect($calendar['days'])->sum(fn ($day) => $day['tasks']->count());
    $undatedCount = $calendar['undated_tasks']->count();
    $activeSprint = $calendar['active_sprint'];
    $colorMap = [
        'secondary' => 'bg-slate-100 text-slate-700',
        'warning' => 'bg-amber-100 text-amber-800',
        'success' => 'bg-emerald-100 text-emerald-800',
    ];
@endphp

<div class="space-y-6">
    <div class="card overflow-hidden">
        <div class="card-body">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-primary-600">Calendario de proyecto</p>
                    <h3 class="text-lg font-semibold text-gray-900 mt-1">{{ $calendar['month_label'] }}</h3>
                    <p class="text-sm text-gray-600 mt-1">Revisa fechas objetivo y sprints activos sin perder el flujo.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('tasks.index', $project) }}" class="btn-primary text-xs">Ir a tareas</a>
                    <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs">Resumen del proyecto</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Tareas del mes</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $totalTasksMonth }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Sin fecha</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $undatedCount }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Sprint activo</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $activeSprint?->name ?? 'Sin sprint activo' }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Rango visible</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $calendar['range_start']->format('d M') }} - {{ $calendar['range_end']->format('d M') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ $calendar['month_label'] }}</h3>
                    <p class="text-xs text-gray-500">Hoy: {{ now()->format('d M') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('projects.calendar.index', [$project, 'month' => $calendar['prev_month']] + request()->except('month')) }}" class="btn-secondary text-xs">Mes anterior</a>
                    <a href="{{ route('projects.calendar.index', [$project, 'month' => $calendar['next_month']] + request()->except('month')) }}" class="btn-secondary text-xs">Mes siguiente</a>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-2 text-xs text-gray-500 mb-2">
                <div>Lun</div><div>Mar</div><div>Mie</div><div>Jue</div><div>Vie</div><div>Sab</div><div>Dom</div>
            </div>
            <div class="grid grid-cols-7 gap-2">
                @foreach($calendar['days'] as $day)
                    <div class="border border-gray-200 rounded-lg p-2 min-h-[120px] {{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-50' }} {{ $day['date']->isToday() ? 'ring-1 ring-primary-500' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold {{ $day['date']->isToday() ? 'text-primary-700' : 'text-gray-700' }}">{{ $day['date']->format('d') }}</span>
                            @if($day['tasks']->isNotEmpty())
                                <span class="text-[0.65rem] text-gray-500">{{ $day['tasks']->count() }} tareas</span>
                            @endif
                        </div>
                        <div class="mt-2 space-y-1">
                            @foreach($day['tasks'] as $task)
                                @php
                                    $status = $calendar['statuses'][$task->status] ?? null;
                                    $pillClass = $colorMap[$status['color'] ?? 'secondary'] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <a href="{{ route('tasks.show', [$project, $task]) }}" class="flex items-center gap-2 text-xs rounded-lg px-2 py-1 border border-gray-200 hover:border-primary-300 hover:text-primary-700">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[0.65rem] {{ $pillClass }}">{{ $status['label'] ?? 'Tarea' }}</span>
                                    <span class="truncate">{{ $task->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Tareas sin fecha</h3>
            @if($calendar['undated_tasks']->isEmpty())
                <p class="text-sm text-gray-500">Sin tareas sin fecha.</p>
                <p class="text-xs text-gray-500 mt-2">Asigna una fecha objetivo desde la vista de tareas para verlas en el calendario.</p>
            @else
                <div class="space-y-2">
                    @foreach($calendar['undated_tasks'] as $task)
                        <a href="{{ route('tasks.show', [$project, $task]) }}" class="block text-sm text-gray-700 hover:text-primary-600">
                            {{ $task->title }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
