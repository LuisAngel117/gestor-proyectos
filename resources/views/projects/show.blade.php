@extends('layouts.app')

@section('title', $project->name)

@section('content')
<x-slot name="header">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Equipo: {{ $project->team->name }} • Creado por: {{ $project->creator->full_name }}
            </p>
        </div>
        <div class="flex space-x-2">
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            @can('viewAny', [\App\Models\BacklogItem::class, $project])
            <a href="{{ route('backlog.index', $project) }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6M9 9h6M9 13h6M5 5h.01M5 9h.01M5 13h.01M5 17h.01M9 17h6"></path>
                </svg>
                Backlog
            </a>
            @endcan
            <a href="{{ route('projects.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
</x-slot>

<div class="space-y-6">
    <!-- Información del proyecto -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Detalles principales -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Proyecto</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <p class="text-gray-900">{{ $project->description ?? 'Sin descripción' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <span class="badge badge-{{ $project->status_color }}">
                                    {{ $project->status_label }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                                <span class="badge badge-{{ $project->priority_color }}">
                                    {{ $project->priority_label }}
                                </span>
                            </div>
                        </div>

                        @if($project->start_date || $project->due_date)
                        <div class="grid grid-cols-2 gap-4">
                            @if($project->start_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio</label>
                                <p class="text-gray-900">{{ $project->start_date->format('d/m/Y') }}</p>
                            </div>
                            @endif
                            @if($project->due_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de entrega</label>
                                <p class="text-gray-900 {{ $project->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $project->due_date->format('d/m/Y') }}
                                    @if($project->isOverdue())
                                    <span class="text-xs">(Vencido)</span>
                                    @endif
                                </p>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($project->estimated_hours)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horas estimadas</label>
                            <p class="text-gray-900">{{ number_format($project->estimated_hours, 2) }} horas</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de creación</label>
                                <p class="text-gray-900">{{ $project->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Última actualización</label>
                                <p class="text-gray-900">{{ $project->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar con métricas -->
        <div class="space-y-6">
            <!-- Estadísticas rápidas -->
            <div class="card">
                <div class="card-body">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Estadísticas</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Miembros</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $project->members->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Sprints</span>
                            <span class="text-lg font-semibold text-gray-900">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Tareas</span>
                            <span class="text-lg font-semibold text-gray-900">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipo -->
            <div class="card">
                <div class="card-body">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Equipo Asignado</h4>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">{{ $project->team->name }}</p>
                            <p class="text-xs text-gray-500">{{ $project->team->users->count() }} miembros totales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Miembros del proyecto -->
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Miembros del Proyecto</h3>
                @can('manageMembers', $project)
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('projects.transfer-owner', $project) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <label for="new_owner" class="text-sm text-gray-600">Transferir owner</label>
                        <select
                            id="new_owner"
                            name="user_id"
                            class="form-input text-sm"
                            @disabled($project->members->where('pivot.role', '!=', 'owner')->isEmpty())
                        >
                            @foreach($project->members as $member)
                                @if($member->pivot->role !== 'owner')
                                    <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="btn-secondary text-sm" @disabled($project->members->where('pivot.role', '!=', 'owner')->isEmpty())>Transferir</button>
                    </form>
                    <button class="btn-primary text-sm">
                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Agregar Miembro
                    </button>
                </div>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol en Proyecto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha de ingreso
                            </th>
                            @can('manageMembers', $project)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($project->members as $member)
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
                            @can('manageMembers', $project)
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($member->pivot->role !== 'owner')
                                <button class="text-red-600 hover:text-red-900">Eliminar</button>
                                @endif
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                No hay miembros asignados a este proyecto
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Zona de peligro (solo para owner) -->
    @can('delete', $project)
    <div class="card border-red-200">
        <div class="card-body">
            <h3 class="text-lg font-semibold text-red-900 mb-4">Zona de Peligro</h3>
            <p class="text-sm text-gray-600 mb-4">
                Una vez eliminado el proyecto, toda su información será borrada permanentemente, incluyendo sprints, tareas y archivos adjuntos. Esta acción no se puede deshacer.
            </p>
            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este proyecto? Esta acción no se puede deshacer.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Eliminar Proyecto
                </button>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
