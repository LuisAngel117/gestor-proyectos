@extends('layouts.app')

@section('title', 'Tareas - ' . $project->name)

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
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
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('tasks.index', $project) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="status" class="form-input">
                        <option value="">Todos</option>
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sprint</label>
                    <select name="sprint" class="form-input">
                        <option value="">Todos</option>
                        <option value="backlog" @selected($filters['sprint'] === 'backlog')>Backlog</option>
                        @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}" @selected((string) $filters['sprint'] === (string) $sprint->id)>{{ $sprint->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Asignado</label>
                    <select name="assignee" class="form-input">
                        <option value="">Todos</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected((string) $filters['assignee'] === (string) $assignee->id)>{{ $assignee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-secondary">Filtrar</button>
                    <a href="{{ route('tasks.index', $project) }}" class="btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    @can('create', [\App\Models\Task::class, $project])
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Crear tarea</h3>
            <form method="POST" action="{{ route('tasks.store', $project) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">T&iacute;tulo</label>
                    <input type="text" name="title" class="form-input" placeholder="Ej: Cortar papas" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="status" class="form-input">
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}">{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad</label>
                    <select name="priority" class="form-input">
                        @foreach($priorities as $priority)
                            <option value="{{ $priority }}">{{ $priority }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sprint</label>
                    <select name="sprint_id" class="form-input">
                        <option value="">Backlog</option>
                        @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Deja en Backlog si no va al sprint activo.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtarea</label>
                    <select name="parent_id" class="form-input">
                        <option value="">Sin padre</option>
                        @foreach($parentOptions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Opcional.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha objetivo</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Horas estimadas</label>
                    <input type="number" name="estimated_hours" step="0.01" min="0" class="form-input" placeholder="Ej: 1.5">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripci&oacute;n</label>
                    <textarea name="description" class="form-input" rows="2" placeholder="Describe la tarea"></textarea>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="btn-primary">Crear tarea</button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-body">
            @if($tasks->count() === 0)
                <p class="text-sm text-gray-500">No hay tareas registradas.</p>
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
                            @foreach($tasks as $task)
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

                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
