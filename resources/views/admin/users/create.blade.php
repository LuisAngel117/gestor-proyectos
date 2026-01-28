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
        @if($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                <p class="font-semibold mb-1">Hay errores en el formulario:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                <input name="name" type="text" class="form-input w-full" required>
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Apellido</label>
                <input name="apellido" type="text" class="form-input w-full" required>
                @error('apellido')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input name="email" type="email" class="form-input w-full" required>
                @error('email')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <input name="password" type="password" class="form-input w-full" minlength="6" required>
                <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres.</p>
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rol del sistema</label>
                <select name="role" class="form-input w-full" required>
                    <option value="user">user</option>
                    <option value="admin">admin</option>
                </select>
                @error('role')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select name="estado" class="form-input w-full" required>
                    <option value="activo">activo</option>
                    <option value="inactivo">inactivo</option>
                </select>
                @error('estado')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn-primary">Crear usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection
