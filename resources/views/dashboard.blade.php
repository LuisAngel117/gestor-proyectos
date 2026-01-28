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
    <div class="card">
        <div class="card-body">
            <div class="dashboard-hero rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-blue-50 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-sky-600 mb-2">Panel local</p>
                        <h3 class="text-2xl font-semibold text-gray-900">Hola, {{ Auth::user()->name }} 👋</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Resumen r&aacute;pido para que sigas el flujo sin perderte.
                        </p>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <span class="badge badge-info">Vista general</span>
                            <span class="badge badge-primary">Accesos r&aacute;pidos</span>
                            @if($isSuperadmin)
                                <span class="badge badge-warning">Superadmin</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="dashboard-hero-card rounded-xl bg-white/80 border border-white/60 px-4 py-3 shadow-sm">
                            <p class="text-xs text-gray-500">Equipos</p>
                            <p class="text-xl font-semibold text-gray-900">{{ $teamsCount }}</p>
                        </div>
                        <div class="dashboard-hero-card rounded-xl bg-white/80 border border-white/60 px-4 py-3 shadow-sm">
                            <p class="text-xs text-gray-500">Proyectos</p>
                            <p class="text-xl font-semibold text-gray-900">{{ $projectsCount }}</p>
                        </div>
                        <div class="dashboard-hero-card rounded-xl bg-white/80 border border-white/60 px-4 py-3 shadow-sm">
                            <p class="text-xs text-gray-500">Notificaciones</p>
                            <p class="text-xl font-semibold text-gray-900">{{ Auth::user()->unreadNotifications()->count() }}</p>
                        </div>
                        @if($isSuperadmin)
                            <div class="dashboard-hero-card rounded-xl bg-white/80 border border-white/60 px-4 py-3 shadow-sm">
                                <p class="text-xs text-gray-500">Usuarios activos</p>
                                <p class="text-xl font-semibold text-gray-900">{{ $usersCount }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="card relative overflow-hidden">
            <div class="absolute -top-8 -right-8 h-20 w-20 rounded-full bg-blue-100/60"></div>
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
        <div class="card relative overflow-hidden">
            <div class="absolute -top-8 -right-8 h-20 w-20 rounded-full bg-emerald-100/70"></div>
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
            <div class="card relative overflow-hidden">
                <div class="absolute -top-8 -right-8 h-20 w-20 rounded-full bg-sky-100/70"></div>
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                        <div class="flex items-center gap-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Usuarios</p>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Solo activos</span>
                        </div>
                        <p class="text-2xl font-semibold text-gray-900">{{ $usersCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">Activos</p>
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
        <div class="card relative overflow-hidden">
            <div class="absolute -top-8 -right-8 h-20 w-20 rounded-full bg-amber-100/70"></div>
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
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-900">Accesos rapidos</h4>
                        <span class="text-xs text-gray-500">Acciones frecuentes</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <a href="{{ route('projects.index') }}" class="btn-secondary text-sm w-full justify-between">
                            <span>Ver proyectos</span>
                            <span class="text-primary-600">→</span>
                        </a>
                        <a href="{{ route('teams.index') }}" class="btn-secondary text-sm w-full justify-between">
                            <span>Ver equipos</span>
                            <span class="text-primary-600">→</span>
                        </a>
                        <a href="{{ route('notifications.index') }}" class="btn-secondary text-sm w-full justify-between">
                            <span>Notificaciones</span>
                            <span class="text-primary-600">→</span>
                        </a>
                        <a href="{{ route('profile.show') }}" class="btn-secondary text-sm w-full justify-between">
                            <span>Mi perfil</span>
                            <span class="text-primary-600">→</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Estado general</h4>
                            <p class="text-xs text-gray-500">Indicadores r&aacute;pidos del sistema.</p>
                        </div>
                        <span class="badge badge-success">En l&iacute;nea</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Notificaciones</p>
                                <span class="text-xs text-amber-600 font-semibold">Sin leer</span>
                            </div>
                            <p class="text-xl font-semibold text-gray-900 mt-2">{{ Auth::user()->unreadNotifications()->count() }}</p>
                            <div class="mt-3 h-2 w-full rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-amber-500" style="width: 62%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Revisa mensajes clave.</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Equipos</p>
                                <span class="text-xs text-primary-600 font-semibold">Activos</span>
                            </div>
                            <p class="text-xl font-semibold text-gray-900 mt-2">{{ $teamsCount }}</p>
                            <div class="mt-3 flex items-end gap-1">
                                <span class="h-6 w-2 rounded bg-primary-200"></span>
                                <span class="h-8 w-2 rounded bg-primary-300"></span>
                                <span class="h-5 w-2 rounded bg-primary-200"></span>
                                <span class="h-9 w-2 rounded bg-primary-400"></span>
                                <span class="h-7 w-2 rounded bg-primary-300"></span>
                                <span class="h-10 w-2 rounded bg-primary-500"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Distribuci&oacute;n reciente.</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Proyectos</p>
                                <span class="text-xs text-emerald-600 font-semibold">En curso</span>
                            </div>
                            <p class="text-xl font-semibold text-gray-900 mt-2">{{ $projectsCount }}</p>
                            <div class="mt-3 h-2 w-full rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: 74%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Listos para seguimiento.</p>
                        </div>
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
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-12 w-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-lg font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Usuario activo</h4>
                            <p class="text-xs text-gray-500">Perfil actual</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><span class="font-semibold">Nombre:</span> {{ Auth::user()->name }}</p>
                        <p><span class="font-semibold">Email:</span> {{ Auth::user()->email }}</p>
                        <p><span class="font-semibold">Rol del sistema:</span> {{ Auth::user()->role }}</p>
                    </div>
                </div>
            </div>

            @if($isSuperadmin)
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900">Accesos globales</h4>
                                <p class="text-xs text-gray-500">Vistas completas del sistema.</p>
                            </div>
                            <span class="badge badge-warning">Superadmin</span>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('admin.calendar') }}" class="btn-secondary text-sm w-full justify-between">
                                <span>Calendario global</span>
                                <span class="text-primary-600">→</span>
                            </a>
                            <a href="{{ route('admin.scrum') }}" class="btn-secondary text-sm w-full justify-between">
                                <span>Scrum global</span>
                                <span class="text-primary-600">→</span>
                            </a>
                            <a href="{{ route('admin.tasks') }}" class="btn-secondary text-sm w-full justify-between">
                                <span>Tareas globales</span>
                                <span class="text-primary-600">→</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm w-full justify-between">
                                <span>Usuarios</span>
                                <span class="text-primary-600">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-900">Estado del sistema</h4>
                        <span class="text-xs text-gray-500">Local</span>
                    </div>
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Sesiones activas
                            </span>
                            <span class="text-gray-900 font-semibold">OK</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                Notificaciones
                            </span>
                            <span class="text-gray-900 font-semibold">Lista</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Mensajes
                            </span>
                            <span class="text-gray-900 font-semibold">Activos</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
