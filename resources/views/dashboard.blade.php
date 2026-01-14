@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
    </h2>
</x-slot>

<div class="card">
    <div class="card-body">
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            <h3 class="mt-6 text-2xl font-bold text-gray-900">¡Bienvenido al Gestor de Proyectos!</h3>
            <p class="mt-2 text-base text-gray-600">
                Has iniciado sesión correctamente. Este es tu panel de control.
            </p>
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-blue-900 mb-2">Información del Usuario</h4>
                <div class="text-left space-y-2">
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Nombre:</span> {{ Auth::user()->name }}
                    </p>
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Email:</span> {{ Auth::user()->email }}
                    </p>
                    <p class="text-sm text-blue-800">
                        <span class="font-semibold">Fecha de registro:</span> {{ Auth::user()->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
            <div class="mt-6">
                <p class="text-sm text-gray-500">
                    Las funcionalidades completas (proyectos, tareas, equipos) se implementarán en los próximos módulos.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
