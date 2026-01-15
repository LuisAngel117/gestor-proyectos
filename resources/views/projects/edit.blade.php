@extends('layouts.app')

@section('title', 'Editar Proyecto')

@section('content')
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Proyecto') }}
        </h2>
        <a href="{{ route('projects.show', $project) }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
</x-slot>

<div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PUT')

                <!-- Información del equipo -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Equipo</h4>
                    @if($teams->isNotEmpty())
                        <label for="team_id" class="form-label">Equipo asignado</label>
                        <select
                            id="team_id"
                            name="team_id"
                            class="form-input w-full @error('team_id') border-red-500 @enderror"
                        >
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id', $project->team_id) == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Solo puedes mover el proyecto a equipos donde tengas permisos de administración.</p>
                    @else
                        <p class="text-sm text-gray-700">{{ $project->team->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">No tienes permisos para mover este proyecto a otro equipo.</p>
                    @endif
                </div>

                <!-- Nombre del proyecto -->
                <div class="mb-6">
                    <label for="name" class="form-label">Nombre del Proyecto *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $project->name) }}"
                        class="form-input w-full @error('name') border-red-500 @enderror"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="mb-6">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-input w-full @error('description') border-red-500 @enderror"
                    >{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Máximo 2000 caracteres</p>
                </div>

                <!-- Estado y Prioridad -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="status" class="form-label">Estado *</label>
                        <select
                            id="status"
                            name="status"
                            class="form-input w-full @error('status') border-red-500 @enderror"
                            required
                        >
                            <option value="planificacion" {{ old('status', $project->status) == 'planificacion' ? 'selected' : '' }}>Planificación</option>
                            <option value="en_progreso" {{ old('status', $project->status) == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                            <option value="en_espera" {{ old('status', $project->status) == 'en_espera' ? 'selected' : '' }}>En Espera</option>
                            <option value="completado" {{ old('status', $project->status) == 'completado' ? 'selected' : '' }}>Completado</option>
                            <option value="cancelado" {{ old('status', $project->status) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('status')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="priority" class="form-label">Prioridad *</label>
                        <select
                            id="priority"
                            name="priority"
                            class="form-input w-full @error('priority') border-red-500 @enderror"
                            required
                        >
                            <option value="baja" {{ old('priority', $project->priority) == 'baja' ? 'selected' : '' }}>Baja</option>
                            <option value="media" {{ old('priority', $project->priority) == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="alta" {{ old('priority', $project->priority) == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="urgente" {{ old('priority', $project->priority) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                        @error('priority')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="start_date" class="form-label">Fecha de Inicio</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                            class="form-input w-full @error('start_date') border-red-500 @enderror"
                        >
                        @error('start_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" class="form-label">Fecha de Entrega</label>
                        <input
                            type="date"
                            id="due_date"
                            name="due_date"
                            value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}"
                            class="form-input w-full @error('due_date') border-red-500 @enderror"
                        >
                        @error('due_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Horas estimadas -->
                <div class="mb-6">
                    <label for="estimated_hours" class="form-label">Horas Estimadas</label>
                    <input
                        type="number"
                        id="estimated_hours"
                        name="estimated_hours"
                        value="{{ old('estimated_hours', $project->estimated_hours) }}"
                        class="form-input w-full @error('estimated_hours') border-red-500 @enderror"
                        step="0.01"
                        min="0"
                        max="9999.99"
                    >
                    @error('estimated_hours')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('projects.show', $project) }}" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
