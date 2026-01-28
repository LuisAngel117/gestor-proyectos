@extends('layouts.app')

@section('title', 'Tareas - ' . $project->name)

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <nav class="text-xs text-gray-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary-600">Inicio</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.index') }}" class="hover:text-primary-600">Proyectos</a>
                <span class="mx-1">/</span>
                <a href="{{ route('projects.show', $project) }}" class="hover:text-primary-600">{{ $project->name }}</a>
                <span class="mx-1">/</span>
                <span>Tareas</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tareas</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Volver al proyecto</a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @php
        $user = Auth::user();
        $projectRole = $user->roleInProject($project->id);
        $teamRole = $user->roleInTeam($project->team_id);
        $canCreateTasks = $user->can('create', [\App\Models\Task::class, $project]);
        $canManageBacklog = $user->can('create', [\App\Models\BacklogItem::class, $project]);
        $isObserver = $projectRole === 'observer' || $teamRole === 'observer';
    @endphp
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Gu&iacute;a r&aacute;pida</h3>
            @if($canCreateTasks)
                <p class="text-sm text-gray-600 mb-3">Crea tareas en backlog o as&iacute;gnalas a un sprint.</p>
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                    <li>Define t&iacute;tulo y estado.</li>
                    <li>Elige sprint o deja en backlog.</li>
                    <li>Luego asigna responsables y registra tiempo.</li>
                </ol>
                <div class="flex flex-wrap gap-2 mt-4">
                    @if($canManageBacklog)
                        <a href="{{ route('backlog.index', $project) }}" class="btn-secondary text-xs">Ver backlog</a>
                    @endif
                    <a href="{{ route('sprints.index', $project) }}" class="btn-secondary text-xs">Ver sprints</a>
                    <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Abrir tablero</a>
                </div>
            @else
                @if($isObserver)
                    <p class="text-sm text-gray-600 mb-3">Modo observador: solo lectura.</p>
                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                        <li>Revisa el avance general del proyecto.</li>
                        <li>Consulta el tablero y el calendario.</li>
                        <li>No puedes crear ni editar tareas.</li>
                    </ol>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Abrir tablero</a>
                        <a href="{{ route('projects.calendar.index', $project) }}" class="btn-secondary text-xs">Calendario</a>
                        <a href="{{ route('projects.dashboard.index', $project) }}" class="btn-secondary text-xs">Dashboard</a>
                    </div>
                @else
                    <p class="text-sm text-gray-600 mb-3">Modo miembro: trabaja en tareas asignadas.</p>
                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                        <li>Abre tus tareas para checklist y comentarios.</li>
                        <li>Inicia/det&eacute;n el timer cuando trabajes.</li>
                        <li>Cambia el estado en el tablero si est&aacute; asignada.</li>
                    </ol>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('projects.scrum-board.index', $project) }}" class="btn-secondary text-xs">Abrir tablero</a>
                        <a href="{{ route('projects.calendar.index', $project) }}" class="btn-secondary text-xs">Calendario</a>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @php
        $collection = $tasks->getCollection();
        $doneStatuses = $doneStatuses ?? [];
        $pendingTasks = $collection->filter(fn ($task) => !in_array($task->status, $doneStatuses, true));
        $completedTasks = $collection->filter(fn ($task) => in_array($task->status, $doneStatuses, true));
    @endphp

    @can('create', [\App\Models\Task::class, $project])
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Crear tarea</h3>
                    <p class="text-xs text-gray-500 mt-1">Backlog = tarea sin sprint; Sprint = tarea ya planificada.</p>
                </div>
                <span class="text-xs text-gray-400">Gu&iacute;a por pasos</span>
            </div>
            <form method="POST" action="{{ route('tasks.store', $project) }}" class="mt-5 space-y-6">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Paso 1 · Lo b&aacute;sico</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">T&iacute;tulo</label>
                            <input type="text" name="title" class="form-input w-full" placeholder="Ej: Cortar papas" required>
                            <p class="text-xs text-gray-500 mt-1">Nombre corto y claro de la tarea.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Descripci&oacute;n</label>
                            <textarea name="description" class="form-input w-full min-h-[160px]" rows="5" placeholder="Describe la tarea y el resultado esperado"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Paso 2 · Planificaci&oacute;n</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sprint</label>
                            <select name="sprint_id" class="form-input w-full">
                                <option value="">Backlog (sin sprint)</option>
                                @foreach($sprints as $sprint)
                                    <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Si no est&aacute; lista, d&eacute;jala en backlog.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado inicial</label>
                            <select name="status" class="form-input w-full">
                                @foreach($statuses as $key => $status)
                                    <option value="{{ $key }}">{{ $status['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Define el avance inicial.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad</label>
                            <select name="priority" class="form-input w-full">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority }}">{{ $priority }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Baja, media o alta.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha objetivo</label>
                            <input type="date" name="due_date" class="form-input w-full">
                            <p class="text-xs text-gray-500 mt-1">Aparece en el calendario.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Paso 3 · Relaciones y esfuerzo</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Subtarea</label>
                            <select name="parent_id" class="form-input w-full">
                                <option value="">Sin padre</option>
                                @foreach($parentOptions as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Usa esto si es parte de otra tarea.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Horas estimadas</label>
                            <input type="number" name="estimated_hours" step="0.01" min="0" class="form-input w-full" placeholder="Ej: 1.5">
                            <p class="text-xs text-gray-500 mt-1">Usado en reportes y burndown.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Crear tarea</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Tareas por realizar</h3>
            @if($pendingTasks->count() === 0)
                <p class="text-sm text-gray-500">No hay tareas pendientes.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Titulo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sprint</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Asignados</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendingTasks as $task)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                        @if($task->parent)
                                            <div class="text-xs text-gray-500">Subtarea de {{ $task->parent->title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @php($status = $statuses[$task->status] ?? ['label' => $task->status, 'color' => 'secondary'])
                                        <span class="badge badge-{{ $status['color'] }}">{{ $status['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $task->sprint?->name ?? 'Backlog' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $task->assignees->count() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <a href="{{ route('tasks.show', [$project, $task]) }}" class="btn-secondary text-xs">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Tareas realizadas</h3>
            @if($completedTasks->count() === 0)
                <p class="text-sm text-gray-500">A&uacute;n no hay tareas completadas.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Titulo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sprint</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Asignados</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($completedTasks as $task)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                        @if($task->parent)
                                            <div class="text-xs text-gray-500">Subtarea de {{ $task->parent->title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @php($status = $statuses[$task->status] ?? ['label' => $task->status, 'color' => 'secondary'])
                                        <span class="badge badge-{{ $status['color'] }}">{{ $status['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $task->sprint?->name ?? 'Backlog' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $task->assignees->count() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <a href="{{ route('tasks.show', [$project, $task]) }}" class="btn-secondary text-xs">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
</div>
@endsection
