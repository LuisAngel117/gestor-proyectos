@extends('layouts.app')

@section('title', 'Crear Sprint')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.index') }}" class="hover:text-primary-600">Proyectos</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">{{ $project->name }}</a>
                <span class="mx-1">/</span>
                <a href="{{ route('sprints.index', $project) }}" class="hover:text-primary-600">Sprints</a>
                <span class="mx-1">/</span>
                <span>Crear</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Sprint</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
@php
    $minDate = now()->toDateString();
    $maxDate = $project->due_date ? $project->due_date->format('Y-m-d') : null;
@endphp
<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
        <div class="card">
            <div class="card-body">
            <form method="POST" action="{{ route('sprints.store', $project) }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="form-label">Nombre del Sprint * <span class="ml-1 text-gray-400 cursor-help" title="Ej: Sprint 1 o Entrega inicial">?</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', 'Sprint ' . $nextSequence) }}"
                        class="form-input w-full @error('name') border-red-500 @enderror"
                        required
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="goal" class="form-label">Objetivo <span class="ml-1 text-gray-400 cursor-help" title="Resume el foco principal del sprint">?</span></label>
                    <textarea
                        id="goal"
                        name="goal"
                        rows="3"
                        class="form-input w-full @error('goal') border-red-500 @enderror"
                        placeholder="Describe el objetivo principal del sprint..."
                    >{{ old('goal') }}</textarea>
                    @error('goal')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="form-label">Fecha de inicio * <span class="ml-1 text-gray-400 cursor-help" title="Día en que empieza el sprint">?</span></label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date') }}"
                            min="{{ $minDate }}"
                            @if($maxDate) max="{{ $maxDate }}" @endif
                            class="form-input w-full @error('start_date') border-red-500 @enderror"
                            required
                        >
                        @error('start_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">No disponible antes de hoy.</p>
                        @if($maxDate)
                            <p class="text-xs text-gray-500">No disponible después de {{ $project->due_date->format('d/m/Y') }}.</p>
                        @endif
                    </div>
                    <div>
                        <label for="end_date" class="form-label">Fecha de fin * <span class="ml-1 text-gray-400 cursor-help" title="Día en que termina el sprint">?</span></label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            min="{{ $minDate }}"
                            @if($maxDate) max="{{ $maxDate }}" @endif
                            class="form-input w-full @error('end_date') border-red-500 @enderror"
                            required
                        >
                        @error('end_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">No disponible antes de hoy.</p>
                        @if($maxDate)
                            <p class="text-xs text-gray-500">No disponible después de {{ $project->due_date->format('d/m/Y') }}.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label for="status" class="form-label">Estado * <span class="ml-1 text-gray-400 cursor-help" title="Usa Planificación para definir el trabajo antes de iniciar">?</span></label>
                    <select
                        id="status"
                        name="status"
                        class="form-input w-full @error('status') border-red-500 @enderror"
                        required
                    >
                        <option value="planificacion" @selected(old('status', 'planificacion') === 'planificacion')>Planificación</option>
                        <option value="activo" @selected(old('status') === 'activo')>Activo</option>
                        <option value="cerrado" @selected(old('status') === 'cerrado')>Cerrado</option>
                    </select>
                    @error('status')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700">
                        Recomendación: inicia en “Planificación” y luego usa la vista de planificación para definir el trabajo del sprint.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Crear Sprint</button>
                </div>
            </form>
            </div>
        </div>
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Guía rápida</h3>
                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                        <li>Define nombre y objetivo.</li>
                        <li>Selecciona fechas válidas.</li>
                        <li>Deja el estado en planificación.</li>
                    </ol>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Sugerencia</h3>
                    <p class="text-sm text-gray-600">
                        Crea el sprint en estado planificación y luego asigna ítems desde la vista de planificación.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
