<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Usuarios</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $usersCount }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Equipos</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $teamsCount }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Proyectos</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $projectsCount }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Acceso rapido</p>
            <h3 class="text-lg font-semibold text-gray-900 mt-1">Usuarios</h3>
            <p class="text-sm text-gray-600 mt-1">Gestion global de cuentas.</p>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-xs mt-4">Ver usuarios</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Acceso rapido</p>
            <h3 class="text-lg font-semibold text-gray-900 mt-1">Equipos</h3>
            <p class="text-sm text-gray-600 mt-1">Vista completa de equipos.</p>
            <a href="{{ route('teams.index') }}" class="btn-secondary text-xs mt-4">Ver equipos</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Acceso rapido</p>
            <h3 class="text-lg font-semibold text-gray-900 mt-1">Proyectos</h3>
            <p class="text-sm text-gray-600 mt-1">Explorar todos los proyectos.</p>
            <a href="{{ route('projects.index') }}" class="btn-secondary text-xs mt-4">Ver proyectos</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="text-xs text-gray-500 uppercase">Acceso rapido</p>
            <h3 class="text-lg font-semibold text-gray-900 mt-1">Notificaciones</h3>
            <p class="text-sm text-gray-600 mt-1">Alertas y actividad.</p>
            <a href="{{ route('notifications.index') }}" class="btn-secondary text-xs mt-4">Ver notificaciones</a>
        </div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-body">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Acceso por proyecto</h3>
            <a href="{{ route('projects.index') }}" class="text-sm text-primary-600">Ver todos</a>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($recentProjects as $project)
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">Equipo: {{ $project->team?->name }}</p>
                        </div>
                        <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-xs">Abrir</a>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('sprints.index', $project) }}" class="btn-secondary text-xs">Sprints</a>
                        <a href="{{ route('backlog.index', $project) }}" class="btn-secondary text-xs">Backlog</a>
                        <a href="{{ route('tasks.index', $project) }}" class="btn-secondary text-xs">Tareas</a>
                        <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Tablero</a>
                        <a href="{{ route('projects.calendar.index', $project) }}" class="btn-secondary text-xs">Calendario</a>
                        <a href="{{ route('projects.dashboard.index', $project) }}" class="btn-secondary text-xs">Dashboard</a>
                        <a href="{{ route('projects.exports.tasks', $project) }}" class="btn-secondary text-xs">CSV</a>
                        <a href="{{ route('projects.exports.sprint-summary', $project) }}" class="btn-secondary text-xs">PDF</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="card lg:col-span-2">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Ultimos usuarios</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-primary-600">Ver todos</a>
            </div>
            <div class="space-y-2 text-sm text-gray-600">
                @foreach($recentUsers as $user)
                    <div class="flex items-center justify-between">
                        <span>{{ $user->full_name }} ({{ $user->email }})</span>
                        <span class="badge badge-{{ $user->role === 'superadmin' ? 'danger' : 'info' }}">
                            {{ $user->role }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Ultimos equipos</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    @foreach($recentTeams as $team)
                        <div>
                            <p class="font-medium text-gray-900">{{ $team->name }}</p>
                            <p class="text-xs text-gray-500">Owner: {{ $team->owner?->full_name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Ultimos proyectos</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    @foreach($recentProjects as $project)
                        <div>
                            <p class="font-medium text-gray-900">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">Equipo: {{ $project->team?->name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
