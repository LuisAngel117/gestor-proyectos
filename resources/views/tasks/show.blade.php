@extends('layouts.app')

@section('title', 'Tarea - ' . $task->title)

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->title }}</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tasks.index', $project) }}" class="btn-secondary">Volver a tareas</a>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Proyecto</a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Estado</p>
                    @php($status = $statuses[$task->status] ?? ['label' => $task->status, 'color' => 'secondary'])
                    <span class="badge badge-{{ $status['color'] }}">{{ $status['label'] }}</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Prioridad</p>
                    <span class="badge badge-secondary">{{ $task->priority }}</span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Sprint</p>
                    <p class="text-sm text-gray-900">{{ $task->sprint?->name ?? 'Backlog' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Fecha limite</p>
                    <p class="text-sm text-gray-900">{{ $task->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Horas estimadas</p>
                    <p class="text-sm text-gray-900">{{ $task->estimated_hours ?? '0.00' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Creada</p>
                    <p class="text-sm text-gray-900">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            @can('update', $task)
                <form method="POST" action="{{ route('tasks.update', [$project, $task]) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-6">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="text" name="title" value="{{ $task->title }}" class="form-input" required>
                    <select name="status" class="form-input">
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" @selected($task->status === $key)>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="form-input">
                        @foreach($priorities as $priority)
                            <option value="{{ $priority }}" @selected($task->priority === $priority)>{{ $priority }}</option>
                        @endforeach
                    </select>
                    <select name="sprint_id" class="form-input">
                        <option value="">Backlog</option>
                        @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}" @selected((int) $task->sprint_id === (int) $sprint->id)>{{ $sprint->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}" class="form-input">
                    <input type="number" name="estimated_hours" value="{{ $task->estimated_hours }}" step="0.01" min="0" class="form-input">
                    <textarea name="description" class="form-input md:col-span-4" rows="2">{{ $task->description }}</textarea>
                    <input type="hidden" name="backlog_item_id" value="{{ $task->backlog_item_id }}">
                    <input type="hidden" name="parent_id" value="{{ $task->parent_id }}">
                    <div class="md:col-span-4">
                        <button type="submit" class="btn-primary">Actualizar tarea</button>
                    </div>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Asignados</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    @forelse($task->assignees as $assignee)
                        <div class="flex items-center justify-between">
                            <span>{{ $assignee->full_name }}</span>
                            @can('update', $task)
                                <form method="POST" action="{{ route('tasks.assignees.destroy', [$project, $task, $assignee]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600">Quitar</button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Sin asignados.</p>
                    @endforelse
                </div>

                @can('update', $task)
                    <form method="POST" action="{{ route('tasks.assignees.store', [$project, $task]) }}" class="mt-4">
                        @csrf
                        <label class="block text-xs font-medium text-gray-600 mb-1">Agregar asignados</label>
                        <select name="user_ids[]" multiple class="form-input w-full">
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-secondary mt-2">Asignar</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Checklist</h3>
                <div class="space-y-2 text-sm">
                    @foreach($task->checklistItems as $item)
                        <div class="flex items-center justify-between border border-gray-200 rounded px-2 py-1">
                            <span>{{ $item->content }}</span>
                            @can('update', $task)
                                <form method="POST" action="{{ route('tasks.checklist.update', [$project, $task, $item]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="content" value="{{ $item->content }}">
                                    <input type="hidden" name="is_completed" value="0">
                                    <label class="text-xs text-gray-500 flex items-center gap-1">
                                        <input type="checkbox" name="is_completed" value="1" @checked($item->is_completed)>
                                        Completar
                                    </label>
                                    <button type="submit" class="btn-secondary text-xs">Guardar</button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                    @if($task->checklistItems->isEmpty())
                        <p class="text-sm text-gray-500">Sin checklist.</p>
                    @endif
                </div>

                @can('update', $task)
                    <form method="POST" action="{{ route('tasks.checklist.store', [$project, $task]) }}" class="mt-4 flex gap-2">
                        @csrf
                        <input type="text" name="content" class="form-input flex-1" placeholder="Nuevo item" required>
                        <button type="submit" class="btn-secondary">Agregar</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Dependencias</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="font-medium text-gray-700 mb-2">Prerequisitos</p>
                    @forelse($task->prerequisites as $dep)
                        <div class="flex items-center justify-between border border-gray-200 rounded px-2 py-1 mb-2">
                            <span>{{ $dep->title }}</span>
                            @can('update', $task)
                                <form method="POST" action="{{ route('tasks.dependencies.destroy', [$project, $task, $dep]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600">Quitar</button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-gray-500">Sin prerequisitos.</p>
                    @endforelse
                </div>
                <div>
                    <p class="font-medium text-gray-700 mb-2">Dependientes</p>
                    @forelse($task->dependents as $dep)
                        <div class="border border-gray-200 rounded px-2 py-1 mb-2">
                            {{ $dep->title }}
                        </div>
                    @empty
                        <p class="text-gray-500">Sin dependientes.</p>
                    @endforelse
                </div>
            </div>

            @can('update', $task)
                <form method="POST" action="{{ route('tasks.dependencies.store', [$project, $task]) }}" class="mt-4 flex gap-2">
                    @csrf
                    <select name="depends_on_task_id" class="form-input flex-1">
                        @foreach($availableTasks as $option)
                            <option value="{{ $option->id }}">{{ $option->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-secondary">Agregar dependencia</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Timer</h3>
                @can('trackTime', $task)
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('tasks.timer.start', [$project, $task]) }}">
                            @csrf
                            <button type="submit" class="btn-primary text-sm">Iniciar</button>
                        </form>
                        <form method="POST" action="{{ route('tasks.timer.stop', [$project, $task]) }}">
                            @csrf
                            <button type="submit" class="btn-secondary text-sm">Detener</button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Solo lectura.</p>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Registrar tiempo manual</h3>
                @can('trackTime', $task)
                    <form method="POST" action="{{ route('tasks.time-entries.store', [$project, $task]) }}" class="space-y-2">
                        @csrf
                        <select name="user_id" class="form-input">
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                        <input type="datetime-local" name="started_at" class="form-input" required>
                        <input type="datetime-local" name="stopped_at" class="form-input" required>
                        <input type="text" name="note" class="form-input" placeholder="Nota opcional">
                        <button type="submit" class="btn-secondary">Guardar</button>
                    </form>
                @else
                    <p class="text-sm text-gray-500">Solo lectura.</p>
                @endcan
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Time entries</h3>
            @if($task->timeEntries->isEmpty())
                <p class="text-sm text-gray-500">Sin registros.</p>
            @else
                <div class="space-y-2 text-sm">
                    @foreach($task->timeEntries as $entry)
                        <div class="border border-gray-200 rounded px-2 py-2">
                            <p class="text-gray-700">
                                {{ $entry->user?->full_name }} - {{ $entry->duration_seconds }}s ({{ $entry->source }})
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $entry->started_at?->format('d/m/Y H:i') }} - {{ $entry->stopped_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Comentarios</h3>
            <div class="space-y-3">
                @forelse($task->comments as $comment)
                    <div class="border border-gray-200 rounded px-3 py-2">
                        <p class="text-sm text-gray-900">{{ $comment->body }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $comment->author?->full_name }} - {{ $comment->created_at->format('d/m/Y H:i') }}
                        </p>
                        @can('update', $comment)
                            <form method="POST" action="{{ route('tasks.comments.update', [$project, $task, $comment]) }}" class="mt-2 flex gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="lock_version" value="{{ $comment->lock_version }}">
                                <input type="text" name="body" value="{{ $comment->body }}" class="form-input flex-1">
                                <button type="submit" class="btn-secondary text-xs">Editar</button>
                            </form>
                        @endcan
                        @can('delete', $comment)
                            <form method="POST" action="{{ route('tasks.comments.destroy', [$project, $task, $comment]) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600">Eliminar</button>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Sin comentarios.</p>
                @endforelse
            </div>

            @can('create', [\App\Models\Comment::class, $task])
                <form method="POST" action="{{ route('tasks.comments.store', [$project, $task]) }}" class="mt-4 flex gap-2">
                    @csrf
                    <input type="text" name="body" class="form-input flex-1" placeholder="Nuevo comentario" required>
                    <button type="submit" class="btn-secondary">Agregar</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Adjuntos</h3>
            <div class="space-y-2 text-sm">
                @forelse($task->attachments as $attachment)
                    <div class="flex items-center justify-between border border-gray-200 rounded px-2 py-2">
                        <div>
                            <p class="text-gray-900">{{ $attachment->original_name }}</p>
                            <p class="text-xs text-gray-500">{{ $attachment->size_bytes }} bytes</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('tasks.attachments.download', [$project, $task, $attachment]) }}" class="btn-secondary text-xs">Descargar</a>
                            @can('update', $task)
                                <form method="POST" action="{{ route('tasks.attachments.destroy', [$project, $task, $attachment]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600">Eliminar</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Sin adjuntos.</p>
                @endforelse
            </div>

            @can('update', $task)
                <form method="POST" action="{{ route('tasks.attachments.store', [$project, $task]) }}" enctype="multipart/form-data" class="mt-4 flex gap-2">
                    @csrf
                    <input type="file" name="file" class="form-input flex-1" required>
                    <button type="submit" class="btn-secondary">Subir</button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
