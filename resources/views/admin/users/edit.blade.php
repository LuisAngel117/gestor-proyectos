@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Usuario</h2>
            <p class="text-sm text-gray-600 mt-1">Actualiza rol y estado del usuario.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <p class="font-semibold">Revisa los errores antes de guardar:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                <input name="name" type="text" class="form-input w-full" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Apellido</label>
                <input name="apellido" type="text" class="form-input w-full" value="{{ old('apellido', $user->apellido) }}" required>
                @error('apellido')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input name="email" type="email" class="form-input w-full" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rol del sistema</label>
                @if($user->role === 'superadmin')
                    <input type="text" class="form-input w-full bg-gray-100" value="superadmin" disabled>
                    <input type="hidden" name="role" value="superadmin">
                @else
                    <select name="role" class="form-input w-full" required>
                        <option value="user" @selected(old('role', $user->role) === 'user')>user</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>admin</option>
                    </select>
                @endif
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select name="estado" class="form-input w-full" required>
                    <option value="activo" @selected(old('estado', $user->estado) === 'activo')>activo</option>
                    <option value="inactivo" @selected(old('estado', $user->estado) === 'inactivo')>inactivo</option>
                </select>
                @error('estado')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nueva contrase&ntilde;a</label>
                <input name="password" type="password" class="form-input w-full" placeholder="Opcional" minlength="6">
                <p class="mt-1 text-xs text-gray-500">M&iacute;nimo 6 caracteres si deseas cambiarla.</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Confirmar contrase&ntilde;a</label>
                <input name="password_confirmation" type="password" class="form-input w-full" placeholder="Opcional" minlength="6">
                @error('password_confirmation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
