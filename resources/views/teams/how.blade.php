@extends('layouts.app')

@section('header')
<div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $team->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Propietario: {{ $team->owner->full_name }}
            </p>
        </div>
        <div class="flex space-x-2">
            @if($team->isOwner(Auth::user()) || Auth::user()->isSuperadmin())
            <a href="{{ route('teams.edit', $team) }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endif
            <a href="{{ route('teams.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection
@section('title', $team->name)

@section('content')


<div class="space-y-6">
    <!-- Información del equipo -->
    <div class="card">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Equipo</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <p class="text-gray-900">{{ $team->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <p class="text-gray-900">{{ $team->description ?? 'Sin descripción' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de creación</label>
                        <p class="text-gray-900">{{ $team->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total de miembros</label>
                        <p class="text-gray-900">{{ $team->users->count() }} miembros</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Miembros del equipo -->
    <div class="card">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Miembros del Equipo</h3>
                @if($team->isOwner(Auth::user()) || Auth::user()->isSuperadmin())
                <button class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Miembro
                </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha de ingreso
                            </th>
                            @if($team->isOwner(Auth::user()) || Auth::user()->isSuperadmin())
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($team->users as $member)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($member->avatar_path)
                                            <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $member->avatar_path) }}" alt="{{ $member->full_name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center">
                                                <span class="text-sm font-bold text-white">
                                                    {{ substr($member->name, 0, 1) }}{{ substr($member->apellido, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $member->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $member->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-{{ $member->pivot->role === 'owner' ? 'danger' : ($member->pivot->role === 'admin' ? 'warning' : ($member->pivot->role === 'observer' ? 'info' : 'success')) }}">
                                    {{ ucfirst($member->pivot->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') }}
                            </td>
                            @if($team->isOwner(Auth::user()) || Auth::user()->isSuperadmin())
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($member->id !== $team->owner_id)
                                <button class="text-red-600 hover:text-red-900">Eliminar</button>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Zona de peligro (solo para owner) -->
    @if($team->isOwner(Auth::user()) || Auth::user()->isSuperadmin())
    <div class="card border-red-200">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-red-900 mb-4">Zona de Peligro</h3>
            <p class="text-sm text-gray-600 mb-4">
                Una vez eliminado el equipo, toda su información será borrada permanentemente. Esta acción no se puede deshacer.
            </p>
            <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este equipo? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Eliminar Equipo
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

