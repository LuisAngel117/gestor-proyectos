@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Perfil') }}
        </h2>
        <a href="{{ route('profile.show') }}" class="btn-secondary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
</x-slot>

<div class="space-y-6">
    <form method="POST" action="{{ route('profile.update.extended') }}">
        @csrf
        @method('PUT')

        <!-- Información Personal -->
        <div class="card mb-6">
            <div class="card-body">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Información Personal</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="form-label">Nombre *</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="form-input w-full @error('name') border-red-500 @enderror"
                            required
                        >
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label for="apellido" class="form-label">Apellido *</label>
                        <input
                            type="text"
                            id="apellido"
                            name="apellido"
                            value="{{ old('apellido', $user->apellido) }}"
                            class="form-input w-full @error('apellido') border-red-500 @enderror"
                            required
                        >
                        @error('apellido')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="form-label">Correo Electrónico *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="form-input w-full @error('email') border-red-500 @enderror"
                            required
                        >
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Profesional -->
        <div class="card mb-6">
            <div class="card-body">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Información Profesional</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cargo -->
                    <div>
                        <label for="cargo" class="form-label">Cargo</label>
                        <input
                            type="text"
                            id="cargo"
                            name="cargo"
                            value="{{ old('cargo', $user->profile->cargo ?? '') }}"
                            class="form-input w-full @error('cargo') border-red-500 @enderror"
                            placeholder="Ej: Desarrollador Full Stack"
                        >
                        @error('cargo')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Departamento -->
                    <div>
                        <label for="departamento" class="form-label">Departamento</label>
                        <input
                            type="text"
                            id="departamento"
                            name="departamento"
                            value="{{ old('departamento', $user->profile->departamento ?? '') }}"
                            class="form-input w-full @error('departamento') border-red-500 @enderror"
                            placeholder="Ej: Ingeniería de Software"
                        >
                        @error('departamento')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID Universitario -->
                    <div>
                        <label for="id_universitario" class="form-label">ID Universitario / Matrícula</label>
                        <input
                            type="text"
                            id="id_universitario"
                            name="id_universitario"
                            value="{{ old('id_universitario', $user->profile->id_universitario ?? '') }}"
                            class="form-input w-full @error('id_universitario') border-red-500 @enderror"
                            placeholder="Ej: EST-2024-0001"
                        >
                        @error('id_universitario')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            value="{{ old('telefono', $user->profile->telefono ?? '') }}"
                            class="form-input w-full @error('telefono') border-red-500 @enderror"
                            placeholder="Ej: +593-999888777"
                        >
                        @error('telefono')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Biografía -->
                    <div class="md:col-span-2">
                        <label for="bio" class="form-label">Biografía</label>
                        <textarea
                            id="bio"
                            name="bio"
                            rows="4"
                            class="form-input w-full @error('bio') border-red-500 @enderror"
                            placeholder="Cuéntanos algo sobre ti..."
                        >{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                        @error('bio')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Máximo 1000 caracteres</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('profile.show') }}" class="btn-secondary">
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
@endsection
