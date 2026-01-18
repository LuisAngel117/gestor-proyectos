@extends('layouts.app')

@section('title', 'Dashboard')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
    </h2>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="card-body">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Bienvenido</h3>
                <p class="text-sm text-gray-600">
                    Este es tu panel local de pruebas. Usa el menu lateral para navegar por equipos,
                    proyectos y notificaciones.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Accesos rapidos</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <a href="{{ route('projects.index') }}" class="btn-secondary text-sm">Ver proyectos</a>
                    <a href="{{ route('teams.index') }}" class="btn-secondary text-sm">Ver equipos</a>
                    <a href="{{ route('notifications.index') }}" class="btn-secondary text-sm">Notificaciones</a>
                    <a href="{{ route('profile.show') }}" class="btn-secondary text-sm">Mi perfil</a>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Usuario activo</h4>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><span class="font-semibold">Nombre:</span> {{ Auth::user()->name }}</p>
                    <p><span class="font-semibold">Email:</span> {{ Auth::user()->email }}</p>
                    <p><span class="font-semibold">Rol:</span> {{ Auth::user()->role }}</p>
                </div>
            </div>
        </div>

        @if(Auth::user()->isSuperadmin())
            <div class="card">
                <div class="card-body">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Superadmin</h4>
                    <p class="text-sm text-gray-600 mb-3">Acceso total al sistema local.</p>
                    <a href="{{ route('admin.index') }}" class="btn-primary w-full text-sm">Panel admin</a>
                </div>
            </div>
        @endif
    </div>
</div>

@if(Auth::user()->isSuperadmin())
    <div class="mt-10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Resumen superadmin</h3>
            <a href="{{ route('admin.index') }}" class="btn-secondary text-xs">Abrir panel admin</a>
        </div>
        @include('admin.partials.overview')
    </div>
@endif
@endsection
