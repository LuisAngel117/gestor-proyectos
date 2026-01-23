@extends('layouts.app')

@section('title', 'Proyectos')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Proyectos') }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ $isSuperadmin ? 'Vista global de proyectos.' : 'Proyectos donde participas.' }}
            </p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn-primary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Proyecto
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Buscar proyecto (nombre, team o creador)</label>
                    <input type="text" name="q" value="{{ $search }}" class="form-input w-full" placeholder="Ej: Proyecto Alpha, Equipo Dev">
                </div>
                @if($teamId)
                    <input type="hidden" name="team" value="{{ $teamId }}">
                @endif
                <button type="submit" class="btn-secondary">Buscar</button>
                @if($search)
                    <a href="{{ route('projects.index', $teamId ? ['team' => $teamId] : []) }}" class="btn-secondary">Limpiar</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Filtrar por equipo</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('projects.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !$teamId ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Todos
                </a>
                @foreach($userTeams as $team)
                <a href="{{ route('projects.index', ['team' => $team->id]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $teamId == $team->id ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $team->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($projects->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($projects as $project)
        <div class="card hover:shadow-lg transition relative">
            <div class="card-body">
                @can('delete', $project)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="absolute top-3 right-3" onsubmit="return confirm('Eliminar este proyecto? Esta accion no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700" aria-label="Eliminar proyecto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                @endcan
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg text-gray-900 mb-1">
                            <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">
                                {{ $project->name }}
                            </a>
                        </h3>
                        <p class="text-xs text-gray-500">
                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            {{ $project->team->name }}
                        </p>
                    </div>
                    <span class="badge badge-{{ $project->priority_color }}">
                        {{ $project->priority_label }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                    {{ $project->description ?? 'Sin descripcion' }}
                </p>

                <div class="flex items-center justify-between mb-4">
                    <span class="badge badge-{{ $project->status_color }}">
                        {{ $project->status_label }}
                    </span>
                    <div class="flex items-center space-x-3 text-xs text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            {{ $project->members->count() }}
                        </span>
                        @if($project->estimated_hours)
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $project->estimated_hours }}h
                        </span>
                        @endif
                    </div>
                </div>

                @if($project->start_date || $project->due_date)
                <div class="text-xs text-gray-500 mb-4">
                    @if($project->start_date)
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Inicio: {{ $project->start_date->format('d/m/Y') }}
                    </div>
                    @endif
                    @if($project->due_date)
                    <div class="flex items-center {{ $project->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Entrega: {{ $project->due_date->format('d/m/Y') }}
                        @if($project->isOverdue())
                        <span class="ml-1">(Vencido)</span>
                        @endif
                    </div>
                    @endif
                </div>
                @endif

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
    <div class="card">
        <div class="card-body text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-6 text-lg font-semibold text-gray-900">No hay proyectos disponibles</h3>
            <p class="mt-2 text-sm text-gray-600">
                {{ $teamId ? 'Este equipo no tiene proyectos aun.' : 'No tienes proyectos en ninguno de tus equipos.' }}
            </p>
            <div class="mt-6">
                <a href="{{ route('projects.create') }}" class="btn-primary">
                    Crear primer proyecto
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
