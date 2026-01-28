@extends('layouts.app')

@section('title', 'Proyectos')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            @php
                $selectedTeam = $teamId ? $userTeams->firstWhere('id', (int) $teamId) : null;
            @endphp
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <span>Proyectos</span>
                @if($selectedTeam)
                    <span class="mx-1">/</span>
                    <span>{{ $selectedTeam->name }}</span>
                @endif
            </nav>
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
    @php
        $totalProjects = $totalProjects ?? (method_exists($projects, 'total') ? $projects->total() : $projects->count());
        $visibleProjects = $visibleProjects ?? $projects->count();
        $teamCount = $teamCount ?? $userTeams->count();
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card">
            <div class="card-body">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total visibles</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $totalProjects }}</p>
                <p class="text-xs text-gray-500 mt-1">Proyectos del sistema</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-xs uppercase tracking-wide text-gray-500">Mostrando</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $visibleProjects }}</p>
                <p class="text-xs text-gray-500 mt-1">En esta p&aacute;gina</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-xs uppercase tracking-wide text-gray-500">Equipos</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $teamCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Equipos disponibles</p>
            </div>
        </div>
    </div>
    <div class="card">
            <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Gu&iacute;a r&aacute;pida</h3>
            <p class="text-sm text-gray-600 mb-3">Cada proyecto contiene sprints y tareas.</p>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                <li>Selecciona un equipo o crea uno nuevo.</li>
                <li>Crea el proyecto y agrega miembros.</li>
                <li>Define sprints y organiza las tareas.</li>
            </ol>
            <div class="flex flex-wrap gap-2 mt-4">
                <a href="{{ route('projects.create') }}" class="btn-primary text-xs">Nuevo proyecto</a>
                <a href="{{ route('teams.index') }}" class="btn-secondary text-xs">Ver equipos</a>
            </div>
        </div>
    </div>
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
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr($project->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 mb-1">
                                <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">
                                    {{ $project->name }}
                                </a>
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ $project->team->name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="badge badge-{{ $project->priority_color }}">
                            {{ $project->priority_label }}
                        </span>
                        <span class="badge badge-{{ $project->status_color }}">
                            {{ $project->status_label }}
                        </span>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                    {{ $project->description ?? 'Sin descripción' }}
                </p>

                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 mb-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 border border-gray-200">
                        {{ $project->members->count() }} miembros
                    </span>
                    @if($project->estimated_hours)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 border border-gray-200">
                            {{ $project->estimated_hours }}h estimadas
                        </span>
                    @endif
                    @if($project->due_date)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-50 border border-gray-200 {{ $project->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                            Entrega: {{ $project->due_date->format('d/m/Y') }}
                        </span>
                    @endif
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
