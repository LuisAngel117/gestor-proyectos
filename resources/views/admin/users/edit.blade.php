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
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                <input name="name" type="text" class="form-input w-full" value="{{ old('name', $user->name) }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Apellido</label>
                <input name="apellido" type="text" class="form-input w-full" value="{{ old('apellido', $user->apellido) }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input name="email" type="email" class="form-input w-full" value="{{ old('email', $user->email) }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rol</label>
                @if($user->role === 'superadmin')
                    <input type="text" class="form-input w-full bg-gray-100" value="superadmin" disabled>
                    <input type="hidden" name="role" value="superadmin">
                @else
                    <select name="role" class="form-input w-full" required>
                        <option value="user" @selected(old('role', $user->role) === 'user')>user</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>admin</option>
                    </select>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select name="estado" class="form-input w-full" required>
                    <option value="activo" @selected(old('estado', $user->estado) === 'activo')>activo</option>
                    <option value="inactivo" @selected(old('estado', $user->estado) === 'inactivo')>inactivo</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nueva contraseña</label>
                <input name="password" type="password" class="form-input w-full" placeholder="Opcional">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Confirmar contraseña</label>
                <input name="password_confirmation" type="password" class="form-input w-full" placeholder="Opcional">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
