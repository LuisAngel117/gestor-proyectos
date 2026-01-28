@extends('layouts.app')

@section('title', 'Calendario global')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendario global</h2>
            <p class="text-sm text-gray-600 mt-1">Tareas con fecha objetivo en todos los proyectos.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
@php
    $palette = [
        'bg-blue-100 text-blue-800',
        'bg-emerald-100 text-emerald-800',
        'bg-amber-100 text-amber-800',
        'bg-rose-100 text-rose-800',
        'bg-indigo-100 text-indigo-800',
        'bg-teal-100 text-teal-800',
        'bg-violet-100 text-violet-800',
        'bg-sky-100 text-sky-800',
    ];
    $teams = collect();
    foreach ($days as $day) {
        foreach ($day['tasks'] as $task) {
            if ($task->project && $task->project->team) {
                $teams->put($task->project->team->id, $task->project->team);
            }
        }
    }
    foreach ($undated_tasks as $task) {
        if ($task->project && $task->project->team) {
            $teams->put($task->project->team->id, $task->project->team);
        }
    }
    $teamColors = [];
    $index = 0;
    foreach ($teams as $teamId => $team) {
        $teamColors[$teamId] = $palette[$index % count($palette)];
        $index++;
    }
    $totalTasksMonth = collect($days)->sum(fn ($day) => $day['tasks']->count());
    $undatedCount = $undated_tasks->count();
    $teamCount = $teams->count();
    $projectCount = $teams
        ->flatMap(fn ($team) => $team->projects ?? collect())
        ->unique('id')
        ->count();
@endphp
<div class="space-y-6">
    <div class="card overflow-hidden">
        <div class="card-body">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-primary-600">Calendario global</p>
                    <h3 class="text-lg font-semibold text-gray-900 mt-1">{{ $month_label }}</h3>
                    <p class="text-sm text-gray-600 mt-1">Vista completa de tareas con fecha objetivo en todos los equipos.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.tasks') }}" class="btn-primary text-xs">Ir a tareas globales</a>
                    <a href="{{ route('admin.scrum') }}" class="btn-secondary text-xs">Scrum global</a>
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
                    <p class="text-xs text-gray-500">Equipos activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $teamCount }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Proyectos activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $projectCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ $month_label }}</h3>
                    <p class="text-xs text-gray-500">Hoy: {{ now()->format('d M') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.calendar', ['month' => $prev_month]) }}" class="btn-secondary text-xs">Mes anterior</a>
                    <a href="{{ route('admin.calendar', ['month' => $next_month]) }}" class="btn-secondary text-xs">Mes siguiente</a>
                </div>
            </div>
            <div class="grid grid-cols-7 gap-2 text-xs text-gray-500 mb-2">
                <div>Lun</div><div>Mar</div><div>Mi&eacute;</div><div>Jue</div><div>Vie</div><div>S&aacute;b</div><div>Dom</div>
            </div>
            <div class="grid grid-cols-7 gap-2">
                @foreach($days as $day)
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
                                    $team = $task->project?->team;
                                    $teamClass = $team ? ($teamColors[$team->id] ?? 'bg-gray-100 text-gray-700') : 'bg-gray-100 text-gray-700';
                                @endphp
                                @if($task->project_id)
                                    <a href="{{ route('tasks.show', [$task->project_id, $task]) }}" class="block text-xs">
                                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded {{ $teamClass }}">
                                            {{ $task->title }}
                                        </span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-2 px-2 py-1 rounded {{ $teamClass }} text-xs">
                                        {{ $task->title }}
                                    </span>
                                @endif
                                <div class="text-[10px] text-gray-500">
                                    {{ $team?->name ?? 'Sin equipo' }} - {{ $task->project?->name ?? 'Sin proyecto' }}
                                </div>
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
            @if($undated_tasks->isEmpty())
                <p class="text-sm text-gray-500">Sin tareas sin fecha.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($undated_tasks as $task)
                        @php
                            $team = $task->project?->team;
                            $teamClass = $team ? ($teamColors[$team->id] ?? 'bg-gray-100 text-gray-700') : 'bg-gray-100 text-gray-700';
                        @endphp
                        <a href="{{ $task->project_id ? route('tasks.show', [$task->project_id, $task]) : '#' }}" class="block border border-gray-200 rounded p-3 hover:border-primary-300">
                            <div class="text-sm font-semibold text-gray-800">{{ $task->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $task->project?->name - 'Sin proyecto' }}</div>
                            <span class="inline-flex items-center gap-2 px-2 py-1 rounded text-xs mt-2 {{ $teamClass }}">
                                {{ $team?->name ?? 'Sin equipo' }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
