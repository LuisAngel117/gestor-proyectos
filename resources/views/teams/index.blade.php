@extends('layouts.app')

@section('title', 'Mis Equipos')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $isSuperadmin ? __('Todos los Equipos') : __('Mis Equipos') }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">
            {{ $isSuperadmin ? 'Vista global para superadmin.' : 'Equipos donde participas o administras.' }}
        </p>
    </div>
    <a href="{{ route('teams.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Crear Equipo
    </a>
</div>

<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('teams.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar equipo (nombre u owner)</label>
                <input type="text" name="q" value="{{ $search }}" class="form-input w-full" placeholder="Ej: Equipo Alpha, Maria Perez">
            </div>
            <button type="submit" class="btn-secondary">Buscar</button>
            @if($search)
                <a href="{{ route('teams.index') }}" class="btn-secondary">Limpiar</a>
            @endif
        </form>
    </div>
</div>

<div class="space-y-6">
    @if(!$isSuperadmin && $ownedTeams->count() > 0)
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Equipos que administro</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ownedTeams as $team)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">{{ $team->name }}</h4>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                {{ $team->description ?? 'Sin descripcion' }}
                            </p>
                            <div class="mt-3 flex items-center space-x-2">
                                <span class="badge badge-primary">Owner</span>
                                <span class="text-sm text-gray-500">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    {{ $team->users->count() }} miembros
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('teams.show', $team) }}" class="btn-secondary text-xs py-1 px-3">
                            Seleccionar
                        </a>
                        <a href="{{ route('teams.edit', $team) }}" class="btn-secondary text-xs py-1 px-3">
                            Editar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($teams->count() > 0)
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                {{ $isSuperadmin ? 'Equipos registrados' : 'Equipos donde participo' }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($teams as $team)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">{{ $team->name }}</h4>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                {{ $team->description ?? 'Sin descripcion' }}
                            </p>
                            <div class="mt-3 flex items-center space-x-2">
                                @if(!$isSuperadmin)
                                    @php
                                        $role = $team->pivot->role ?? 'member';
                                        $roleBadge = match ($role) {
                                            'owner' => 'danger',
                                            'admin' => 'warning',
                                            'observer' => 'info',
                                            default => 'success',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $roleBadge }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                @endif
                                <span class="text-sm text-gray-500">
                                    Owner: {{ $team->owner->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('teams.show', $team) }}" class="btn-secondary text-xs py-1 px-3">
                            Seleccionar Equipo
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $teams->links() }}
            </div>
        </div>
    </div>
    @endif

    @if($teams->count() === 0 && (!$ownedTeams || $ownedTeams->count() === 0))
    <div class="card">
        <div class="card-body text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="mt-6 text-lg font-semibold text-gray-900">No tienes equipos aun</h3>
            <p class="mt-2 text-sm text-gray-600">
                Comienza creando tu primer equipo para colaborar con otros usuarios.
            </p>
            <div class="mt-6">
                <a href="{{ route('teams.create') }}" class="btn-primary">
                    Crear mi primer equipo
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
