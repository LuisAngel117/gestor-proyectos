@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Usuario</h2>
            <p class="text-sm text-gray-600 mt-1">Registro manual para pruebas locales.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                <input name="name" type="text" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Apellido</label>
                <input name="apellido" type="text" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input name="email" type="email" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <input name="password" type="password" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rol</label>
                <select name="role" class="form-input w-full" required>
                    <option value="user">user</option>
                    <option value="admin">admin</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select name="estado" class="form-input w-full" required>
                    <option value="activo">activo</option>
                    <option value="inactivo">inactivo</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn-primary">Crear usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection
