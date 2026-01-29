@extends('layouts.app')

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
                <a href="{{ route('sprints.index', $project) }}" class="hover:text-primary-600">Sprints</a>
                <span class="mx-1">/</span>
                <span>{{ $sprint->name }}</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $sprint->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($sprint->isPlanning())
                <a href="{{ route('tasks.index', $project) }}?sprint={{ $sprint->id }}#create-task" class="btn-primary">
                    Crear tareas
                </a>
            @endif
            <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection
@section('title', 'Sprint - ' . $sprint->name)

@section('content')

@if($autoStarted && $sprint->isActive())
    <div class="card border border-emerald-200 bg-emerald-50">
        <div class="card-body">
            <p class="text-sm text-emerald-700 font-medium">
                Sprint iniciado automáticamente al iniciar la primera tarea.
            </p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-body space-y-4">
                @php
                    $statusBadge = match ($sprint->status) {
                        'activo' => 'success',
                        'cerrado' => 'danger',
                        default => 'warning',
                    };
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-{{ $statusBadge }}">{{ ucfirst($sprint->status) }}</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 border border-gray-200 text-xs text-gray-600">
                            Secuencia #{{ $sprint->sequence }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-500">Proyecto: {{ $project->name }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Inicio</p>
                        <p class="text-gray-900">{{ $sprint->start_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Fin</p>
                        <p class="text-gray-900">{{ $sprint->end_date->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Tareas vinculadas</p>
                        <p class="text-gray-900">{{ $sprint->tasks_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estado operativo</p>
                        <p class="text-gray-900">
                            @if($sprint->isClosed() && $sprint->closed_at)
                                Cerrado el {{ $sprint->closed_at->format('d/m/Y H:i') }}
                            @elseif($sprint->started_at)
                                Iniciado el {{ $sprint->started_at->format('d/m/Y H:i') }}
                            @else
                                Sin iniciar
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @can('startSprint', $sprint)
            @if($sprint->isPlanning())
                <form method="POST" action="{{ route('sprints.start', [$project, $sprint]) }}" class="card">
                    @csrf
                    <div class="card-body">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Iniciar sprint</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Verifica que el trabajo del sprint está listo antes de iniciar.
                        </p>
                        <button type="submit" class="btn-primary w-full">Iniciar sprint</button>
                    </div>
                </form>
            @endif
        @endcan

        @can('closeSprint', $sprint)
            @if($sprint->isActive())
                <form method="POST" action="{{ route('sprints.close', [$project, $sprint]) }}" class="card">
                    @csrf
                    <div class="card-body">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Cerrar sprint</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Esta acción marca el sprint como cerrado.
                        </p>
                        <button type="submit" class="btn-secondary w-full">Cerrar sprint</button>
                    </div>
                </form>
            @endif
        @endcan
        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Atajos</h3>
                <div class="flex flex-col gap-2">
                    @if($sprint->isPlanning())
                        <a href="{{ route('tasks.index', $project) }}?sprint={{ $sprint->id }}#create-task" class="btn-secondary text-xs">Crear tareas</a>
                    @endif
                    <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Abrir tablero</a>
                    <a href="{{ route('tasks.index', $project) }}" class="btn-secondary text-xs">Ir a tareas</a>
                    <a href="{{ route('projects.calendar.index', $project) }}" class="btn-secondary text-xs">Calendario</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

