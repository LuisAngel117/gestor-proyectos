<div class="flex flex-col h-full">
    <div class="px-6 h-[74px] flex items-center border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <span class="text-lg font-semibold text-white">{{ config('app.name', 'Gestor') }}</span>
        </a>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
        <div>
            <p class="text-[0.65rem] uppercase tracking-[0.2em] px-3 app-sidebar-title">General</p>
            <div class="mt-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="app-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                    </svg>
                    Panel de control
                </a>
                <a href="{{ route('teams.index') }}" class="app-sidebar-link {{ request()->routeIs('teams.*') ? 'active' : '' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m10-4.13a4 4 0 10-8 0 4 4 0 008 0z" />
                    </svg>
                    Equipos
                </a>
                <a href="{{ route('projects.index') }}" class="app-sidebar-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6a2 2 0 012 2v14a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6M9 13h6M9 17h6" />
                    </svg>
                    Proyectos
                </a>
                <a href="{{ route('notifications.index') }}" class="app-sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.17V11a6 6 0 10-12 0v3.17a2 2 0 01-.6 1.43L4 17h5m6 0a3 3 0 11-6 0h6z" />
                    </svg>
                    Notificaciones
                </a>
                <a href="{{ route('profile.show') }}" class="app-sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.12 17.74A7 7 0 0112 3a7 7 0 016.88 14.74M12 12a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    Mi perfil
                </a>
            </div>
        </div>

        @if(Auth::user()->isSuperadmin())
            <div>
                <p class="text-[0.65rem] uppercase tracking-[0.2em] px-3 app-sidebar-title">Superadmin</p>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('admin.index') }}" class="app-sidebar-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14" />
                        </svg>
                        Panel admin
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="app-sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10a2 2 0 002-2v-1a4 4 0 00-4-4H9a4 4 0 00-4 4v1a2 2 0 002 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                        Usuarios
                    </a>
                </div>
            </div>
        @endif
    </nav>
</div>
