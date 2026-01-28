@extends('layouts.app')

@section('header')
<div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('teams.index') }}" class="hover:text-primary-600">Equipos</a>
                <span class="mx-1">/</span>
                <span>Crear</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear Nuevo Equipo') }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Define el nombre y prop&oacute;sito del equipo antes de agregar miembros.</p>
        </div>
        <a href="{{ route('teams.index') }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection
@section('title', 'Crear Equipo')

@section('content')


<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
        <div class="card">
            <div class="card-body">
            <form method="POST" action="{{ route('teams.store') }}">
                @csrf

                <!-- Nombre del equipo -->
                <div class="mb-6">
                    <label for="name" class="form-label">Nombre del Equipo *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
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
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Máximo 1000 caracteres</p>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('teams.index') }}" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Equipo
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
                        <li>Define el nombre del equipo.</li>
                        <li>Agrega una descripci&oacute;n clara.</li>
                        <li>Guarda y agrega miembros.</li>
                    </ol>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Lo que pasa al crear</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>Ser&aacute;s el propietario (Owner).</li>
                        <li>Podr&aacute;s gestionar miembros y roles.</li>
                        <li>Podr&aacute;s crear proyectos en este equipo.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

