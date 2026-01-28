@extends('layouts.app')

@section('header')
<div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('teams.index') }}" class="hover:text-primary-600">Equipos</a>
                <span class="mx-1">/</span>
                <a href="{{ route('teams.show', $team) }}" class="hover:text-primary-600">{{ $team->name }}</a>
                <span class="mx-1">/</span>
                <span>Editar</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Equipo') }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Actualiza los datos b&aacute;sicos del equipo sin cambiar miembros.</p>
        </div>
        <a href="{{ route('teams.show', $team) }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection
@section('title', 'Editar Equipo')

@section('content')


<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
        <div class="card">
            <div class="card-body">
            <form method="POST" action="{{ route('teams.update', $team) }}">
                @csrf
                @method('PUT')

                <!-- Nombre del equipo -->
                <div class="mb-6">
                    <label for="name" class="form-label">Nombre del Equipo *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $team->name) }}"
                        class="form-input w-full @error('name') border-red-500 @enderror"
                        placeholder="Ej: Equipo Desarrollo Web"
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
                        placeholder="Describe el propósito y objetivos del equipo..."
                    >{{ old('description', $team->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Máximo 1000 caracteres</p>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('teams.show', $team) }}" class="btn-secondary">
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
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Propietario actual</h4>
                    <div class="flex items-center space-x-3">
                        @if($team->owner->avatar_path)
                            <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $team->owner->avatar_path) }}" alt="{{ $team->owner->full_name }}">
                        @else
                            <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center">
                                <span class="text-sm font-bold text-white">
                                    {{ substr($team->owner->name, 0, 1) }}{{ substr($team->owner->apellido, 0, 1) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $team->owner->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $team->owner->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Consejos r&aacute;pidos</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>Actualiza el nombre si cambi&oacute; la identidad del equipo.</li>
                        <li>Usa una descripci&oacute;n breve y clara.</li>
                        <li>Los miembros se gestionan desde el detalle del equipo.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

