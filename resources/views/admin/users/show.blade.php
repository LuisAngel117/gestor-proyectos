@extends('layouts.app')

@section('title', 'Detalles de Usuario')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalles de usuario</h2>
            <p class="text-sm text-gray-600 mt-1">Vista completa del perfil del usuario.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">Editar usuario</a>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    @if($user->avatar_path)
                        <img class="h-24 w-24 rounded-full object-cover" src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->full_name }}">
                    @else
                        <div class="h-24 w-24 rounded-full bg-primary-500 flex items-center justify-center">
                            <span class="text-3xl font-bold text-white">
                                {{ substr($user->name, 0, 1) }}{{ substr($user->apellido, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h3>
                    <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                    <div class="mt-3 flex items-center space-x-4">
                        <span class="badge badge-{{ $user->role === 'superadmin' ? 'danger' : ($user->role === 'admin' ? 'warning' : 'primary') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="badge badge-{{ $user->estado === 'activo' ? 'success' : ($user->estado === 'inactivo' ? 'warning' : 'danger') }}">
                            {{ ucfirst($user->estado) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($user->profile)
    <div class="card">
        <div class="card-body">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Informacion profesional</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                    <p class="text-gray-900">{{ $user->profile->cargo ?? 'No especificado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                    <p class="text-gray-900">{{ $user->profile->departamento ?? 'No especificado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID universitario / matricula</label>
                    <p class="text-gray-900">{{ $user->profile->id_universitario ?? 'No especificado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <p class="text-gray-900">{{ $user->profile->telefono ?? 'No especificado' }}</p>
                </div>
            </div>
            @if($user->profile->bio)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Biografia</label>
                <p class="text-gray-900 leading-relaxed">{{ $user->profile->bio }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Informacion de la cuenta</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de registro</label>
                    <p class="text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($user->last_login_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ultimo acceso</label>
                    <p class="text-gray-900">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($user->email_verified_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email verificado</label>
                    <p class="text-gray-900">
                        <span class="inline-flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verificado el {{ $user->email_verified_at->format('d/m/Y') }}
                        </span>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Equipos del usuario</h4>
            @if($teams->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($teams as $team)
                        @php
                            $role = $team->pivot->role ?? 'member';
                            $roleBadge = match ($role) {
                                'owner' => 'danger',
                                'admin' => 'warning',
                                'observer' => 'info',
                                default => 'success',
                            };
                        @endphp
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $team->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                        {{ $team->description ?? 'Sin descripcion' }}
                                    </p>
                                    <div class="mt-3 flex items-center space-x-2">
                                        <span class="badge badge-{{ $roleBadge }}">{{ ucfirst($role) }}</span>
                                        <span class="text-sm text-gray-500">
                                            Owner: {{ $team->owner->name ?? 'No definido' }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ $team->users_count }} miembros
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
            @else
                <p class="text-sm text-gray-600">Este usuario no pertenece a ningun equipo.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Proyectos del usuario</h4>
            @if($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="card hover:shadow-lg transition">
                            <div class="card-body">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-lg text-gray-900 mb-1">
                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">
                                                {{ $project->name }}
                                            </a>
                                        </h3>
                                        <p class="text-xs text-gray-500">
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
                                            {{ $project->members->count() }}
                                        </span>
                                        @if($project->estimated_hours)
                                        <span class="flex items-center">
                                            {{ $project->estimated_hours }}h
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex space-x-2 pt-3 border-t">
                                    <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs py-1 px-3">
                                        Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600">Este usuario no pertenece a ningun proyecto.</p>
            @endif
        </div>
    </div>
</div>
@endsection
