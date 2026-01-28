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
                <span>Sprints</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sprints — {{ $project->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Gestiona los sprints de este proyecto.</p>
        </div>
        <div class="flex gap-2">
            @can('update', $project)
                <a href="{{ route('sprints.create', $project) }}" class="btn-primary">Crear sprint</a>
            @endcan
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Volver al proyecto</a>
        </div>
    </div>
@endsection
@section('title', 'Sprints - ' . $project->name)

@section('content')


<div class="space-y-6">
    @php
        $totalSprints = $sprints->count();
        $planningCount = $sprints->where('status', 'planificacion')->count();
        $activeCount = $sprints->where('status', 'activo')->count();
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card">
            <div class="card-body">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total sprints</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $totalSprints }}</p>
                <p class="text-xs text-gray-500 mt-1">Registrados en el proyecto</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-xs uppercase tracking-wide text-gray-500">Planificación</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $planningCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Listos para crear tareas</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-xs uppercase tracking-wide text-gray-500">Activos</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $activeCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Sprints en ejecución</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Gu&iacute;a r&aacute;pida</h3>
            <p class="text-sm text-gray-600 mb-3">Sigue este flujo para activar el sprint sin vueltas.</p>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                <li>Crea un sprint en <span class="font-semibold">planificaci&oacute;n</span>.</li>
                <li>Agrega las tareas del sprint.</li>
                <li>Inicia el sprint y ejecuta en el tablero.</li>
            </ol>
            <div class="flex flex-wrap gap-2 mt-4">
                @can('update', $project)
                    <a href="{{ route('sprints.create', $project) }}" class="btn-primary text-xs">Crear sprint</a>
                @endcan
                <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Abrir tablero</a>
            </div>
        </div>
    </div>
    @if($sprints->isEmpty())
        <div class="card">
            <div class="card-body text-center py-10">
                <p class="text-gray-600">Todavía no hay sprints para este proyecto.</p>
                @can('update', $project)
                    <a href="{{ route('sprints.create', $project) }}" class="btn-primary mt-4">Crear primer sprint</a>
                @endcan
                <p class="text-xs text-gray-500 mt-3">Luego agrega tareas y activa el sprint.</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($sprints as $sprint)
                @php
                    $statusBadge = match ($sprint->status) {
                        'activo' => 'success',
                        'cerrado' => 'danger',
                        default => 'warning',
                    };
                @endphp
                <div class="card relative hover:shadow-lg transition">
                    <div class="card-body">
                        @can('delete', $sprint)
                            <form method="POST" action="{{ route('sprints.destroy', [$project, $sprint]) }}" class="absolute top-3 right-3" onsubmit="return confirm('Eliminar este sprint? Esta accion no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" aria-label="Eliminar sprint">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        @endcan
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($sprint->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        <a href="{{ route('sprints.show', [$project, $sprint]) }}" class="hover:text-primary-600">
                                            {{ $sprint->name }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        {{ $sprint->start_date->format('d/m/Y') }} — {{ $sprint->end_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                            <span class="badge badge-{{ $statusBadge }}">{{ ucfirst($sprint->status) }}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 border border-gray-200">
                                Secuencia #{{ $sprint->sequence }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 border border-gray-200">
                                {{ $sprint->tasks_count }} tareas
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('sprints.show', [$project, $sprint]) }}" class="btn-secondary text-xs py-1 px-3">
                                Ver detalles
                            </a>
                            @can('plan', $sprint)
                                @if($sprint->isPlanning())
                                    <a href="{{ route('tasks.index', $project) }}?sprint={{ $sprint->id }}#create-task" class="btn-primary text-xs py-1 px-3">
                                        Crear tareas
                                    </a>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

