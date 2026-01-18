@extends('layouts.app')

@section('header')
<div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Proyecto') }}
        </h2>
        <a href="{{ route('projects.index') }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection
@section('title', 'Crear Proyecto')

@section('content')


<div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf

                <!-- Equipo -->
                <div class="mb-6">
                    <label for="team_id" class="form-label">Equipo *</label>
                    @php
                        $selectedTeam = $teamId ? $teams->firstWhere('id', (int) $teamId) : null;
                    @endphp
                    @if($selectedTeam)
                        <input type="hidden" name="team_id" value="{{ $selectedTeam->id }}">
                        <input
                            type="text"
                            class="form-input w-full bg-gray-100"
                            value="{{ $selectedTeam->name }}"
                            disabled
                        >
                    @else
                        <select
                            id="team_id"
                            name="team_id"
                            class="form-input w-full @error('team_id') border-red-500 @enderror"
                            required
                        >
                            <option value="">Selecciona un equipo</option>
                            @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id', $teamId) == $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                            @endforeach
                        </select>
                    @endif
                    @error('team_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $selectedTeam ? 'Equipo seleccionado desde el contexto actual.' : 'Selecciona el equipo al que pertenecerá este proyecto' }}
                    </p>
                </div>

                <!-- Nombre del proyecto -->
                <div class="mb-6">
                    <label for="name" class="form-label">Nombre del Proyecto *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-input w-full @error('name') border-red-500 @enderror"
                        placeholder="Ej: Sistema de Gestión Universitaria"
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
                        placeholder="Describe los objetivos y alcance del proyecto..."
                    >{{ old('description') }}</textarea>
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
                            <option value="planificacion" {{ old('status', 'planificacion') == 'planificacion' ? 'selected' : '' }}>Planificación</option>
                            <option value="en_progreso" {{ old('status') == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                            <option value="en_espera" {{ old('status') == 'en_espera' ? 'selected' : '' }}>En Espera</option>
                            <option value="completado" {{ old('status') == 'completado' ? 'selected' : '' }}>Completado</option>
                            <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            <option value="archivado" {{ old('status') == 'archivado' ? 'selected' : '' }}>Archivado</option>
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
                            <option value="baja" {{ old('priority') == 'baja' ? 'selected' : '' }}>Baja</option>
                            <option value="media" {{ old('priority', 'media') == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="alta" {{ old('priority') == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="urgente" {{ old('priority') == 'urgente' ? 'selected' : '' }}>Urgente</option>
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
                            value="{{ old('start_date') }}"
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
                            value="{{ old('due_date') }}"
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
                        value="{{ old('estimated_hours') }}"
                        class="form-input w-full @error('estimated_hours') border-red-500 @enderror"
                        step="0.01"
                        min="0"
                        max="9999.99"
                        placeholder="Ej: 320"
                    >
                    @error('estimated_hours')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Total de horas estimadas para completar el proyecto</p>
                </div>

                <!-- Nota informativa -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Nota importante</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Serás el propietario (owner) del proyecto</li>
                                    <li>Podrás agregar miembros del equipo al proyecto</li>
                                    <li>Podrás gestionar sprints y tareas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('projects.index') }}" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Proyecto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

