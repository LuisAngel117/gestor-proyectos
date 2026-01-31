@extends('layouts.app')

@section('header')
<div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.index') }}" class="hover:text-primary-600">Proyectos</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">{{ $project->name }}</a>
                <span class="mx-1">/</span>
                <span>Editar</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Proyecto') }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Actualiza los datos b&aacute;sicos del proyecto.</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection
@section('title', 'Editar Proyecto')

@section('content')


<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
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
                            <option value="archivado" {{ old('status', $project->status) == 'archivado' ? 'selected' : '' }}>Archivado</option>
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
                            value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}"
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
                        value="{{ old('estimated_hours', $project->estimated_hours) }}"
                        inputmode="decimal"
                        autocomplete="off"
                        data-hour-input
                        class="form-input w-full @error('estimated_hours') border-red-500 @enderror"
                        step="0.01"
                        min="0"
                        max="9999.99"
                    >
                    @error('estimated_hours')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-rose-600 mt-1 hidden" data-hour-message>Solo horas.</p>
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
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Estado actual</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-{{ $project->status_color }}">{{ $project->status_label }}</span>
                        <span class="badge badge-{{ $project->priority_color }}">{{ $project->priority_label }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Actualiza estado y prioridad seg&uacute;n el avance real.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Consejos r&aacute;pidos</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>Verifica fechas de inicio y entrega.</li>
                        <li>Mant&eacute;n una descripci&oacute;n clara.</li>
                        <li>Los miembros se gestionan en el detalle del proyecto.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

