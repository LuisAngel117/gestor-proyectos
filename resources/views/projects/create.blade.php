@extends('layouts.app')

@section('header')
<div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.index') }}" class="hover:text-primary-600">Proyectos</a>
                <span class="mx-1">/</span>
                <span>Crear</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear Nuevo Proyecto') }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Define el alcance y fechas antes de agregar miembros y tareas.</p>
        </div>
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


<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
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
                        <input type="hidden" name="status" value="planificacion">
                        <input
                            type="text"
                            id="status"
                            class="form-input w-full bg-gray-100"
                            value="Planificación"
                            disabled
                        >
                        @error('status')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Estado inicial fijo al crear.</p>
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
                            min="{{ now()->toDateString() }}"
                            class="form-input w-full @error('start_date') border-red-500 @enderror"
                        >
                        @error('start_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">No disponible antes de hoy.</p>
                    </div>

                    <div>
                        <label for="due_date" class="form-label">Fecha de Entrega</label>
                        <input
                            type="date"
                            id="due_date"
                            name="due_date"
                            value="{{ old('due_date') }}"
                            min="{{ now()->toDateString() }}"
                            class="form-input w-full @error('due_date') border-red-500 @enderror"
                        >
                        @error('due_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">No disponible antes de hoy.</p>
                    </div>
                </div>

                <!-- Horas estimadas -->
                <div class="mb-6">
                    <label for="estimated_hours" class="form-label">Horas Estimadas</label>
                    <input
                        type="text"
                        id="estimated_hours"
                        name="estimated_hours"
                        value="{{ old('estimated_hours') }}"
                        inputmode="decimal"
                        autocomplete="off"
                        data-hour-input
                        class="form-input w-full @error('estimated_hours') border-red-500 @enderror"
                        step="0.01"
                        min="0"
                        max="9999.99"
                        placeholder="Ej: 320"
                    >
                    @error('estimated_hours')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-rose-600 mt-1 hidden" data-hour-message>Solo horas.</p>
                    <p class="text-xs text-gray-500 mt-1">Total de horas estimadas para completar el proyecto</p>
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
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Gu&iacute;a r&aacute;pida</h3>
                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                        <li>Selecciona el equipo destino.</li>
                        <li>Define nombre y prioridad.</li>
                        <li>Agrega fechas y horas estimadas.</li>
                    </ol>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Lo que obtienes</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>Ser&aacute;s el propietario (Owner).</li>
                        <li>Podr&aacute;s agregar miembros del equipo.</li>
                        <li>Podr&aacute;s gestionar sprints y tareas.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

