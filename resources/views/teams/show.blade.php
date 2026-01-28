@extends('layouts.app')

@section('title', $team->name)

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $team->name }}</h2>
            <p class="text-sm text-gray-600 mt-1">Owner: {{ $team->owner?->full_name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teams.index') }}" class="btn-secondary">Volver</a>
            @can('update', $team)
                <a href="{{ route('teams.edit', $team) }}" class="btn-secondary">Editar</a>
            @endcan
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @php
        $hasTeamMembers = $team->users->count() > 1;
        $hasProjects = $projects->total() > 0;
        $userOptions = $availableUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'label' => $user->full_name . ' (' . $user->email . ')',
                'name' => $user->full_name,
                'email' => $user->email,
            ];
        })->values();
        $nextStepLabel = null;
        $nextStepUrl = null;
        if (!$hasTeamMembers) {
            $nextStepLabel = 'Agrega tu primer miembro';
            $nextStepUrl = '#team-add-member';
        } elseif (!$hasProjects) {
            $nextStepLabel = 'Crea el primer proyecto';
            $nextStepUrl = route('projects.create', ['team' => $team->id]);
        } else {
            $nextStepLabel = 'Abre el proyecto y continua';
            $nextStepUrl = route('projects.show', $projects->first());
        }
    @endphp
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Asistente de equipo</h3>
            <p class="text-sm text-gray-600 mb-4">Sigue estos pasos para crear tu flujo de trabajo.</p>
            <ol class="space-y-2 text-sm">
                <li class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>1. Agregar miembros al equipo</span>
                        @if($hasTeamMembers)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                <svg class="w-4 h-4 animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414L8.5 12.086l6.793-6.793a1 1 0 011.411 0z" clip-rule="evenodd"/>
                                </svg>
                                Listo
                            </span>
                        @else
                            <span class="text-xs text-gray-500">Pendiente</span>
                        @endif
                    </div>
                    <a href="#team-add-member" class="btn-secondary text-xs">Ir</a>
                </li>
                <li class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>2. Crear un proyecto en este equipo</span>
                        @if($hasProjects)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                <svg class="w-4 h-4 animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414L8.5 12.086l6.793-6.793a1 1 0 011.411 0z" clip-rule="evenodd"/>
                                </svg>
                                Listo
                            </span>
                        @else
                            <span class="text-xs text-gray-500">Pendiente</span>
                        @endif
                    </div>
                    <a href="{{ route('projects.create', ['team' => $team->id]) }}" class="btn-secondary text-xs">Crear proyecto</a>
                </li>
                <li class="flex items-center justify-between">
                    <span>3. Abrir un proyecto y continuar el flujo</span>
                    @if($hasProjects)
                        <a href="{{ route('projects.show', $projects->first()) }}" class="btn-secondary text-xs">Abrir proyecto</a>
                    @else
                        <span class="text-xs text-gray-500">Crea un proyecto primero</span>
                    @endif
                </li>
            </ol>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border border-dashed border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
                <span>Siguiente paso recomendado:</span>
                <a href="{{ $nextStepUrl }}" class="btn-primary text-xs">{{ $nextStepLabel }}</a>
            </div>
        </div>
    </div>

    <div class="card" id="team-add-member">
        <div class="card-body" x-data="teamMemberPicker(window.teamMemberUsers)">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Agregar miembro</h3>
            @can('manageMembers', $team)
                <form method="POST" action="{{ route('teams.members.store', $team) }}" class="space-y-3" x-ref="form">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Usuario</label>
                        <input type="text" x-model="query" class="form-input w-full" placeholder="Busca por nombre o correo" autocomplete="off">
                        <input type="hidden" name="user_id" x-model="selectedId" required>
                        <input type="hidden" name="role" value="member">
                    </div>
                </form>
                <div class="mt-4">
                    <p class="text-xs text-gray-500 mb-2">Usuarios disponibles ({{ $availableUsers->count() }})</p>
                    <div class="overflow-x-auto border border-gray-200 rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide">Nombre</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide">Email</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wide">Acci&oacute;n</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <tr>
                                        <td class="px-3 py-2" x-text="user.name"></td>
                                        <td class="px-3 py-2" x-text="user.email"></td>
                                        <td class="px-3 py-2">
                                            <button type="button" class="text-primary-600 text-xs font-medium" @click="submitSelection(user)">Agregar</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredUsers.length === 0">
                                    <td colspan="3" class="px-3 py-3 text-center text-xs text-gray-500">No hay usuarios disponibles.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">No tienes permisos para gestionar miembros.</p>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr] gap-6 items-start">
        <div>
            <div class="card mt-4 h-full">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Descripci&oacute;n</h3>
                    <p class="text-sm text-gray-600">{{ $team->description ?? 'Sin descripción' }}</p>
                </div>
            </div>
        </div>
        <div>
            <div class="card mt-4 h-full" id="team-members">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Miembros</h3>
                    <div class="space-y-3 text-sm">
                        @foreach($team->users as $member)
                            @php
                                $role = $member->pivot->role;
                                $roleLabel = match ($role) {
                                    'owner' => 'Owner',
                                    'admin' => 'Administrador',
                                    'observer' => 'Observador',
                                    default => 'Miembro',
                                };
                                $roleBadge = match ($role) {
                                    'owner' => 'danger',
                                    'admin' => 'warning',
                                    'observer' => 'info',
                                    default => 'success',
                                };
                            @endphp
                            <div class="flex items-center justify-between border border-gray-200 rounded-lg px-4 py-3 bg-white">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $member->full_name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $member->email }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-{{ $roleBadge }}" title="Rol en equipo">{{ $roleLabel }}</span>
                                    @can('manageMembers', $team)
                                        <form method="POST" action="{{ route('teams.members.update', [$team, $member]) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-input text-xs min-w-[9.5rem]">
                                                <option value="owner" @selected($role === 'owner')>Owner</option>
                                                <option value="admin" @selected($role === 'admin')>Administrador</option>
                                                <option value="member" @selected($role === 'member')>Miembro</option>
                                                <option value="observer" @selected($role === 'observer')>Observador</option>
                                            </select>
                                            <button type="submit" class="btn-secondary text-xs">Actualizar</button>
                                        </form>
                                        <form method="POST" action="{{ route('teams.members.destroy', [$team, $member]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600">Quitar</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="mt-8"></div>

    <div class="card" id="team-projects">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Proyectos</h3>
                @can('create', [\App\Models\Project::class, $team])
                    <a href="{{ route('projects.create', ['team' => $team->id]) }}" class="btn-secondary text-xs">Nuevo proyecto</a>
                @endcan
            </div>
            @if($projects->count() === 0)
                <p class="text-sm text-gray-500">Sin proyectos para este equipo.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($projects as $project)
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">{{ $project->status_label ?? $project->status }}</p>
                            <a href="{{ route('projects.show', $project) }}" class="text-xs text-primary-600">Ver</a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@once
    <script>
        window.teamMemberUsers = @json($userOptions);
        window.teamMemberPicker = function(users) {
        return {
            query: '',
            selectedId: '',
            selectedLabel: '',
            users: Array.isArray(users) ? users : [],
            get filteredUsers() {
                const list = this.query ? this.users.filter(user => user.label.toLowerCase().includes(this.query.toLowerCase())) : this.users;
                return list;
            },
            selectUser(user) {
                this.selectedId = user.id;
                this.selectedLabel = user.label;
                this.query = user.label;
            },
            submitSelection(user) {
                this.selectUser(user);
                this.$nextTick(() => this.$refs.form?.submit());
            },
        };
    };
    </script>
@endonce
@endsection
