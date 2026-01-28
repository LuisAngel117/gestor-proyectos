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
                <a href="{{ route('sprints.show', [$project, $sprint]) }}" class="hover:text-primary-600">{{ $sprint->name }}</a>
                <span class="mx-1">/</span>
                <span>Planificar</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Planificar — {{ $sprint->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
        </div>
        <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Volver a sprints</a>
    </div>
@endsection
@section('title', 'Planificar Sprint')

@section('content')


@if(!$sprint->isPlanning())
    <div class="mb-6 rounded-md bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-800">
        La planificación está bloqueada porque el sprint está en estado <strong>{{ $sprint->status }}</strong>.
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tareas sin sprint</h3>
            @if($availableItems->isEmpty())
                <p class="text-sm text-gray-600">No hay ítems disponibles para asignar.</p>
            @else
                @can('plan', $sprint)
                    <form method="POST" action="{{ route('sprints.plan.assign', [$project, $sprint]) }}">
                        @csrf
                        <div class="space-y-3">
                            @foreach($availableItems as $item)
                                <label class="flex items-start gap-3">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" class="mt-1">
                                    <span>
                                        <span class="font-medium text-gray-900">{{ $item->name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $item->description ?? 'Sin descripción' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn-primary">Asignar al sprint</button>
                        </div>
                    </form>
                @else
                    <div class="space-y-3">
                        @foreach($availableItems as $item)
                            <div class="text-sm text-gray-700">
                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->description ?? 'Sin descripción' }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 text-sm text-gray-600">No tienes permisos para asignar ítems.</p>
                @endcan
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Trabajo del sprint</h3>
            @if($sprintItems->isEmpty())
                <p class="text-sm text-gray-600">No hay ítems asignados todavía.</p>
            @else
                @can('reorderBacklog', $sprint)
                <form method="POST" action="{{ route('sprints.plan.reorder', [$project, $sprint]) }}">
                    @csrf
                    <div class="space-y-3">
                        @foreach($sprintItems as $item)
                            <div class="flex items-start gap-3">
                                <input type="number" name="positions[{{ $item->id }}]" value="{{ $item->sprint_position }}" class="form-input w-20">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->description ?? 'Sin descripción' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn-primary">Guardar orden</button>
                    </div>
                </form>
                @else
                <div class="space-y-3">
                    @foreach($sprintItems as $item)
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->description ?? 'Sin descripción' }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-gray-600">No tienes permisos para reordenar.</p>
                @endcan

                @can('plan', $sprint)
                    <p class="mt-4 text-sm text-gray-600">Para quitar ítems del sprint, elimina o ajusta sus tareas directamente.</p>
                @endcan
            @endif
        </div>
    </div>
</div>
@endsection

