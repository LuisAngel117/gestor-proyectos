@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
        <a href="{{ route('profile.edit.extended') }}" class="btn-primary">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar Perfil
        </a>
    </div>
</x-slot>

<div class="space-y-6">
    <!-- Información del Usuario -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center space-x-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($user->avatar_path)
                        <img class="h-24 w-24 rounded-full object-cover" src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->full_name }}">
                    @else
                        <div class="h-24 w-24 rounded-full bg-primary-500 flex items-center justify-center">
                            <span class="text-3xl font-bold text-white">
                                {{ substr($user->name, 0, 1) }}{{ substr($user->apellido, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Información básica -->
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h3>
                    <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                    <div class="mt-3 flex items-center space-x-4">
                        <span class="badge badge-{{ $user->role === 'superadmin' ? 'danger' : ($user->role === 'admin' ? 'warning' : 'primary') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="badge badge-{{ $user->estado === 'activo' ? 'success' : ($user->estado === 'inactivo' ? 'warning' : 'danger') }}">
                            {{ ucfirst($user->estado) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Perfil -->
    @if($user->profile)
    <div class="card">
        <div class="card-body">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Información Profesional</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                    <p class="text-gray-900">{{ $user->profile->cargo ?? 'No especificado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                    <p class="text-gray-900">{{ $user->profile->departamento ?? 'No especificado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Universitario / Matrícula</label>
                    <p class="text-gray-900">{{ $user->profile->id_universitario ?? 'No especificado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <p class="text-gray-900">{{ $user->profile->telefono ?? 'No especificado' }}</p>
                </div>
            </div>

            @if($user->profile->bio)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Biografía</label>
                <p class="text-gray-900 leading-relaxed">{{ $user->profile->bio }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Información de la Cuenta -->
    <div class="card">
        <div class="card-body">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Información de la Cuenta</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de registro</label>
                    <p class="text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($user->last_login_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Último acceso</label>
                    <p class="text-gray-900">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($user->email_verified_at)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email verificado</label>
                    <p class="text-gray-900">
                        <span class="inline-flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verificado el {{ $user->email_verified_at->format('d/m/Y') }}
                        </span>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
