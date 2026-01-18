<div class="card">
    <div class="card-body space-y-4">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">General</p>
            <div class="mt-2 space-y-1">
                <a href="{{ route('dashboard') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                    Panel de control
                </a>
                <a href="{{ route('teams.index') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                    Equipos
                </a>
                <a href="{{ route('projects.index') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                    Proyectos
                </a>
                <a href="{{ route('notifications.index') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                    Notificaciones
                </a>
                <a href="{{ route('profile.show') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                    Mi perfil
                </a>
            </div>
        </div>

        @if(Auth::user()->isSuperadmin())
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Superadmin</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.index') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                        Panel admin
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block text-sm text-gray-700 hover:text-primary-600">
                        Usuarios
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
