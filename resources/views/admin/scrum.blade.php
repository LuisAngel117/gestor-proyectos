@extends('layouts.app')

@section('title', 'Scrum global')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Scrum global</h2>
            <p class="text-sm text-gray-600 mt-1">Sprints activos de todos los proyectos.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
@php
    $palette = ['bg-emerald-100 text-emerald-800', 'bg-sky-100 text-sky-800', 'bg-amber-100 text-amber-800', 'bg-rose-100 text-rose-800', 'bg-violet-100 text-violet-800'];
    $teams = collect();
    $totalTasks = 0;
    $totalProjects = 0;
    foreach ($boards as $item) {
        $project = $item['project'];
        $board = $item['board'];
        $totalProjects++;
        $totalTasks += collect($board['status_counts'] ?? [])->sum();
        if ($project?->team) {
            $teams->put($project->team->id, $project->team);
        }
    }
    $teamColors = [];
    $index = 0;
    foreach ($teams as $teamId => $team) {
        $teamColors[$teamId] = $palette[$index % count($palette)];
        $index++;
    }
@endphp

<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-primary-600">Scrum global</p>
                    <h3 class="text-lg font-semibold text-gray-900 mt-1">Vista unificada</h3>
                    <p class="text-sm text-gray-600 mt-1">Sprints activos y tareas por estado en todos los proyectos.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.calendar') }}" class="btn-secondary text-xs">Calendario global</a>
                    <a href="{{ route('admin.tasks') }}" class="btn-secondary text-xs">Tareas globales</a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Sprints activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ count($boards) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Proyectos activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $totalProjects }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Equipos activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $teams->count() }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Tareas en sprint</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $totalTasks }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(empty($boards))
        <div class="card">
            <div class="card-body text-sm text-gray-500">
                No hay sprints activos en este momento.
            </div>
        </div>
    @else
        @foreach($boards as $item)
            @php($project = $item['project'])
            @php($board = $item['board'])
            @php($activeSprint = $item['active_sprint'])
            @php($team = $project?->team)
            @php($teamBadge = $team ? ($teamColors[$team->id] ?? 'bg-gray-100 text-gray-700') : 'bg-gray-100 text-gray-700')
            <div class="card">
                <div class="card-body space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $teamBadge }}">
                                    {{ $team?->name ?? 'Sin equipo' }}
                                </span>
                                <span class="text-sm font-semibold text-gray-900">{{ $project->name }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Sprint activo: {{ $activeSprint->name }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs">Ver proyecto</a>
                            <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Abrir tablero</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($board['statuses'] as $statusKey => $status)
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-700">{{ $status['label'] }}</span>
                                    <span class="text-xs text-gray-500">{{ $board['status_counts'][$statusKey] ?? 0 }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($board['lanes'] as $lane)
                                        @foreach($board['task_buckets'][$lane['key']][$statusKey] ?? [] as $bucket)
                                            @php($task = $bucket['task'])
                                            <div class="bg-white border border-gray-200 rounded-lg p-2 text-sm">
                                                <a href="{{ route('tasks.show', [$project, $task]) }}" class="font-semibold text-gray-800 hover:text-primary-600">
                                                    {{ $task->title }}
                                                </a>
                                                @if($bucket['extra_assignees'] > 0)
                                                    <span class="text-xs text-gray-500 block">+{{ $bucket['extra_assignees'] }} asignados</span>
                                                @endif
                                                <form method="POST" action="{{ route('tasks.scrum-board.move', [$project, $task]) }}" class="mt-2"
                                                      onsubmit="return confirm('Vas a cambiar el estado de esta tarea. Deseas continuar?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" class="form-input text-xs" onchange="this.form.submit()">
                                                        @foreach($board['statuses'] as $moveKey => $moveStatus)
                                                            <option value="{{ $moveKey }}" @selected($task->status === $moveKey)>{{ $moveStatus['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </div>
                                        @endforeach
                                    @endforeach
                                    @if(empty($board['status_counts'][$statusKey]))
                                        <p class="text-xs text-gray-400">Sin tareas</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
