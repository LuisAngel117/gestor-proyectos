@extends('layouts.app')

@section('title', $team->name)

@section('content')
<x-slot name="header">
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $team->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Owner: {{ $team->owner->full_name }} • {{ $team->users->count() }} miembros
            </p>
        </div>
        <div class="flex gap-2">
            @can('create', [\App\Models\Project::class, $team])
            <a href="{{ route('projects.create', ['team' => $team->id]) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Proyecto
            </a>
            @endcan
            <a href="{{ route('teams.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
</x-slot>

<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Proyectos del equipo</h3>
            @if($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="card hover:shadow-lg transition">
                            <div class="card-body">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-lg text-gray-900 mb-1">
                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">
                                                {{ $project->name }}
                                            </a>
                                        </h4>
                                        <p class="text-xs text-gray-500">
                                            Creado por {{ $project->creator->full_name }}
                                        </p>
                                    </div>
                                    <span class="badge badge-{{ $project->priority_color }}">
                                        {{ $project->priority_label }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                    {{ $project->description ?? 'Sin descripción' }}
                                </p>

                                <div class="flex items-center justify-between mb-4">
                                    <span class="badge badge-{{ $project->status_color }}">
                                        {{ $project->status_label }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $project->members->count() }} miembros
                                    </span>
                                </div>

                                <div class="flex space-x-2 pt-3 border-t">
                                    <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs py-1 px-3">
                                        Ver
                                    </a>
                                    @can('update', $project)
                                        <a href="{{ route('projects.edit', $project) }}" class="btn-secondary text-xs py-1 px-3">
                                            Editar
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-sm text-gray-600">Este equipo no tiene proyectos aún.</p>
                    @can('create', [\App\Models\Project::class, $team])
                        <div class="mt-4">
                            <a href="{{ route('projects.create', ['team' => $team->id]) }}" class="btn-primary">
                                Crear primer proyecto
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
