@extends('layouts.app')

@section('header')
<div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sprints — {{ $project->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Planifica el sprint backlog para este proyecto.</p>
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
    @if($sprints->isEmpty())
        <div class="card">
            <div class="card-body text-center py-10">
                <p class="text-gray-600">Todavía no hay sprints para este proyecto.</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($sprints as $sprint)
                <div class="card relative">
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
                        <div class="flex items-start justify-between mb-2">
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
                            <span class="badge badge-secondary">{{ ucfirst($sprint->status) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Secuencia #{{ $sprint->sequence }}</span>
                            <span>{{ $sprint->backlog_items_count }} ítems</span>
                        </div>

                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('sprints.show', [$project, $sprint]) }}" class="btn-secondary text-xs py-1 px-3">
                                Ver detalles
                            </a>
                            @can('plan', $sprint)
                                @if($sprint->isPlanning())
                                    <a href="{{ route('sprints.plan', [$project, $sprint]) }}" class="btn-primary text-xs py-1 px-3">
                                        Planificar
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

