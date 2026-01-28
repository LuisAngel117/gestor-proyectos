@extends('layouts.app')

@section('title', 'Completar Perfil')

@section('header')
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Completar perfil y cambiar contrase&ntilde;a</h2>
        <p class="text-sm text-gray-600 mt-1">Necesitamos estos datos antes de continuar.</p>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <p class="font-semibold">Revisa los errores antes de guardar:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('first-login.update') }}">
        @csrf
        @method('PUT')

        <div class="card mb-6">
            <div class="card-body">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Informaci&oacute;n personal</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                    <div class="md:col-span-2">
                        <label for="email" class="form-label">Correo electr&oacute;nico *</label>
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

        <div class="card mb-6">
            <div class="card-body">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Informaci&oacute;n profesional</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="cargo" class="form-label">Cargo *</label>
                        <input
                            type="text"
                            id="cargo"
                            name="cargo"
                            value="{{ old('cargo', $user->profile->cargo ?? '') }}"
                            class="form-input w-full @error('cargo') border-red-500 @enderror"
                            placeholder="Ej: Desarrollador Full Stack"
                            required
                        >
                        @error('cargo')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="departamento" class="form-label">Departamento *</label>
                        <input
                            type="text"
                            id="departamento"
                            name="departamento"
                            value="{{ old('departamento', $user->profile->departamento ?? '') }}"
                            class="form-input w-full @error('departamento') border-red-500 @enderror"
                            placeholder="Ej: Ingenier&iacute;a de Software"
                            required
                        >
                        @error('departamento')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="id_universitario" class="form-label">ID universitario / matr&iacute;cula *</label>
                        <input
                            type="text"
                            id="id_universitario"
                            name="id_universitario"
                            value="{{ old('id_universitario', $user->profile->id_universitario ?? '') }}"
                            class="form-input w-full @error('id_universitario') border-red-500 @enderror"
                            placeholder="Ej: EST-2024-0001"
                            required
                        >
                        @error('id_universitario')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telefono" class="form-label">Tel&eacute;fono *</label>
                        <input
                            type="text"
                            id="telefono"
                            name="telefono"
                            value="{{ old('telefono', $telefonoLocal ?? '') }}"
                            class="form-input w-full @error('telefono') border-red-500 @enderror"
                            placeholder="Ej: 0968840065"
                            required
                        >
                        @error('telefono')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">
                            Ingresa 10 d&iacute;gitos (0XXXXXXXXX). Guardamos como +593.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="bio" class="form-label">Biograf&iacute;a</label>
                        <textarea
                            id="bio"
                            name="bio"
                            rows="4"
                            class="form-input w-full @error('bio') border-red-500 @enderror"
                            placeholder="Cu&eacute;ntanos algo sobre ti..."
                        >{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                        @error('bio')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">M&aacute;ximo 1000 caracteres</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-body">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Cambiar contrase&ntilde;a</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="form-label">Nueva contrase&ntilde;a *</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input w-full @error('password') border-red-500 @enderror"
                            required
                        >
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">M&iacute;nimo 6 caracteres.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirmar contrase&ntilde;a *</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-input w-full @error('password_confirmation') border-red-500 @enderror"
                            required
                        >
                        @error('password_confirmation')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                Guardar y continuar
            </button>
        </div>
    </form>
</div>
@endsection
