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
<div class="space-y-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Equipos</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $teamsCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $isSuperadmin ? 'Total registrados' : 'Tus equipos' }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m10-4.13a4 4 0 10-8 0 4 4 0 008 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Proyectos</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $projectsCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $isSuperadmin ? 'Activos y en progreso' : 'Tus proyectos' }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2M9 7h6m-6 4h6m-8-8h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        @if($isSuperadmin)
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Usuarios</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $usersCount }}</p>
                            <p class="text-xs text-gray-500 mt-1">Registrados</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.12 17.74A7 7 0 0112 3a7 7 0 016.88 14.74M12 12a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Notificaciones</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ Auth::user()->unreadNotifications()->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Sin leer</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.17V11a6 6 0 10-12 0v3.17a2 2 0 01-.6 1.43L4 17h5m6 0a3 3 0 11-6 0h6z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8 space-y-6">
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
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-900">Accesos rapidos</h4>
                        <span class="text-xs text-gray-500">Acciones frecuentes</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <a href="{{ route('projects.index') }}" class="btn-secondary text-sm">Ver proyectos</a>
                        <a href="{{ route('teams.index') }}" class="btn-secondary text-sm">Ver equipos</a>
                        <a href="{{ route('notifications.index') }}" class="btn-secondary text-sm">Notificaciones</a>
                        <a href="{{ route('profile.show') }}" class="btn-secondary text-sm">Mi perfil</a>
                    </div>
                </div>
            </div>

            @if(Auth::user()->isSuperadmin())
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">Actividad reciente</h4>
                            <span class="text-xs text-gray-500">Ultimas altas</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                            <div>
                                <h5 class="text-xs font-semibold text-gray-800 mb-2 uppercase tracking-wide">Usuarios</h5>
                                <div class="space-y-2">
                                    @foreach($recentUsers as $recentUser)
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $recentUser->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $recentUser->email }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-semibold text-gray-800 mb-2 uppercase tracking-wide">Equipos</h5>
                                <div class="space-y-2">
                                    @foreach($recentTeams as $recentTeam)
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $recentTeam->name }}</p>
                                            <p class="text-xs text-gray-500">Owner: {{ optional($recentTeam->owner)->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-semibold text-gray-800 mb-2 uppercase tracking-wide">Proyectos</h5>
                                <div class="space-y-2">
                                    @foreach($recentProjects as $recentProject)
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $recentProject->name }}</p>
                                            <p class="text-xs text-gray-500">Equipo: {{ optional($recentProject->team)->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="xl:col-span-4 space-y-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Usuario activo</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><span class="font-semibold">Nombre:</span> {{ Auth::user()->name }}</p>
                        <p><span class="font-semibold">Email:</span> {{ Auth::user()->email }}</p>
                        <p><span class="font-semibold">Rol del sistema:</span> {{ Auth::user()->role }}</p>
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
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Resumen superadmin</h3>
                <a href="{{ route('admin.index') }}" class="btn-secondary text-xs">Abrir panel admin</a>
            </div>
            @include('admin.partials.overview')
        </div>
    @endif
</div>
@endsection
