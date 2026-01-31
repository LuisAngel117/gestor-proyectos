@extends('layouts.app')

@section('title', 'Tarea - ' . $task->title)

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
                <a href="{{ route('tasks.index', $project) }}" class="hover:text-primary-600">Tareas</a>
                <span class="mx-1">/</span>
                <span>{{ $task->title }}</span>
            </nav>
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
@php
    $activeOtherTimer = $activeTimerForUser
        && (int) $activeTimerForUser->task_id !== (int) $task->id;
    $hasActiveTimerEntry = $hasActiveTimerEntry ?? ($activeTimerEntry !== null);
    $requiresTimerForChecklist = !auth()->user()?->can('update', $task);
    $taskFinished = $task->status === 'hecho';
@endphp
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Gu&iacute;a r&aacute;pida de la tarea</h3>
            <p class="text-sm text-gray-600 mb-3">Sigue estos pasos para completar la tarea sin perderte.</p>
            <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                <li><a href="#task-assignees" class="text-primary-600 hover:underline">Asignar responsables</a>.</li>
                <li><a href="#task-checklist" class="text-primary-600 hover:underline">Agregar checklist</a> para dividir el trabajo.</li>
                @can('manageDependencies', $task)
                    <li><a href="#task-dependencies" class="text-primary-600 hover:underline">Definir dependencias</a> si hay bloqueos.</li>
                @endcan
                <li><a href="#task-time" class="text-primary-600 hover:underline">Registrar tiempo</a> (timer o manual).</li>
                <li><a href="#task-comments" class="text-primary-600 hover:underline">Comentar avances</a> y acuerdos.</li>
                <li><a href="#task-attachments" class="text-primary-600 hover:underline">Adjuntar archivos</a> relevantes.</li>
            </ol>
        </div>
    </div>

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
                    <p class="text-sm text-gray-900">{{ $task->sprint?->name ?? 'No asignado' }}</p>
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
            <div class="mt-4 border-t pt-4">
                <p class="text-xs text-gray-500 mb-1">Descripci&oacute;n</p>
                <p class="text-sm text-gray-900">{{ $task->description ?: 'Sin descripción.' }}</p>
            </div>

            @can('update', $task)
                <div class="mt-6 border-t pt-5">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Editar tarea</h4>
                            <p class="text-xs text-gray-500">Actualiza datos clave de la tarea.</p>
                        </div>
                        <span class="text-xs text-gray-400">Campos editables</span>
                    </div>
                    <form method="POST" action="{{ route('tasks.update', [$project, $task]) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">T&iacute;tulo</label>
                            <input type="text" name="title" value="{{ $task->title }}" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                            <select name="status" class="form-input w-full">
                                @foreach($statuses as $key => $status)
                                    <option value="{{ $key }}" @selected($task->status === $key)>{{ $status['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad</label>
                            <select name="priority" class="form-input w-full">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority }}" @selected($task->priority === $priority)>{{ $priority }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sprint</label>
                            <select name="sprint_id" class="form-input w-full">
                                <option value="">Sin asignar</option>
                                @foreach($sprints as $sprint)
                                    <option value="{{ $sprint->id }}" @selected((int) $task->sprint_id === (int) $sprint->id)>{{ $sprint->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha l&iacute;mite</label>
                            <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}" class="form-input w-full"
                                   @if($project->due_date) max="{{ $project->due_date->format('Y-m-d') }}" @endif>
                            @if($project->due_date)
                                <p class="text-xs text-amber-600 mt-1">No disponible despu&eacute;s de {{ $project->due_date->format('d/m/Y') }}.</p>
                            @endif
                            @error('due_date')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Horas estimadas</label>
                            <input type="text" name="estimated_hours" value="{{ $task->estimated_hours }}" inputmode="decimal" autocomplete="off"
                                   data-hour-input class="form-input w-full">
                            <p class="text-xs text-rose-600 mt-1 hidden" data-hour-message>Solo horas.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Descripci&oacute;n</label>
                            <textarea name="description" class="form-input w-full min-h-[140px]" rows="4">{{ $task->description }}</textarea>
                        </div>
                        <input type="hidden" name="backlog_item_id" value="{{ $task->backlog_item_id }}">
                        <input type="hidden" name="parent_id" value="{{ $task->parent_id }}">
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" class="btn-primary">Actualizar tarea</button>
                        </div>
                    </form>
                </div>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card" id="task-assignees">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Asignados</h3>
                <p class="text-xs text-gray-500 mb-3">Selecciona qui&eacute;n trabaja en esta tarea.</p>
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

        <div class="card" id="task-checklist">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Checklist</h3>
                <p class="text-xs text-gray-500 mb-3">Divide la tarea en pasos peque&ntilde;os.</p>
                @if($requiresTimerForChecklist && !$activeTimerEntry)
                    <p class="text-xs text-amber-600 mb-3">Inicia el timer para poder marcar el checklist.</p>
                @endif
                <div class="space-y-2 text-sm">
                    @foreach($task->checklistItems as $item)
                        <div class="flex items-center justify-between border border-gray-200 rounded px-2 py-1">
                            <span>{{ $item->content }}</span>
                            @can('toggleChecklist', $task)
                                <form method="POST" action="{{ route('tasks.checklist.update', [$project, $task, $item]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_completed" value="0">
                                    <label class="text-xs text-gray-500 flex items-center gap-1">
                                        <input type="checkbox" name="is_completed" value="1"
                                            @checked($item->is_completed)
                                            @disabled($requiresTimerForChecklist && !$activeTimerEntry)>
                                        Completar
                                    </label>
                                    <button type="submit" class="btn-secondary text-xs"
                                        @disabled($requiresTimerForChecklist && !$activeTimerEntry)>
                                        Guardar
                                    </button>
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

    @can('manageDependencies', $task)
    <div class="card" id="task-dependencies">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Dependencias</h3>
            <p class="text-xs text-gray-500 mb-3">Prerequisitos = tareas que deben terminar antes.</p>
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
    @endcan

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="task-time">
        <div class="card">
            <div class="card-body" x-data="{starting: false, stopping: false}">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Timer</h3>
                <p class="text-xs text-gray-500 mb-3">Inicia y det&eacute;n para registrar tiempo real.</p>
                @if($errors->has('timer'))
                    <div class="mb-3 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        {{ $errors->first('timer') }}
                    </div>
                @endif
                @if($activeOtherTimer)
                    <div class="mb-3 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        Ya tienes un timer activo en otra tarea:
                        <a class="font-semibold underline" href="{{ route('tasks.show', [$activeTimerForUser->task->project, $activeTimerForUser->task]) }}">
                            {{ $activeTimerForUser->task->title }}
                        </a>
                        ({{ $activeTimerForUser->task->project?->name ?? 'Proyecto' }}).
                    </div>
                @endif
                @can('trackTime', $task)
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('tasks.timer.start', [$project, $task]) }}"
                              @submit="if (!confirm('¿Iniciar el timer para esta tarea?')) { $event.preventDefault(); return; } starting = true;">
                            @csrf
                            <button type="submit" class="btn-success text-sm"
                                    @disabled($hasActiveTimerEntry || $activeOtherTimer || $taskFinished)
                                    x-bind:disabled="starting"
                                    x-bind:class="starting ? 'opacity-60 cursor-not-allowed' : ''">
                                Iniciar
                            </button>
                        </form>
                        <form method="POST" action="{{ route('tasks.timer.stop', [$project, $task]) }}"
                              @submit="if (!confirm('Al finalizar el timer, la tarea se marcará como HECHA y no podrás revertirla fácilmente. ¿Deseas continuar?')) { $event.preventDefault(); return; } stopping = true;">
                            @csrf
                            <button type="submit" class="btn-danger text-sm"
                                    @disabled(!$hasActiveTimerEntry || $taskFinished)
                                    x-bind:disabled="stopping"
                                    x-bind:class="stopping ? 'opacity-60 cursor-not-allowed' : ''">
                                Finalizar
                            </button>
                        </form>
                    </div>
                    @if($taskFinished)
                        <p class="text-xs text-gray-500 mt-2">La tarea est&aacute; finalizada. No puedes reiniciar el timer.</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500">Solo lectura.</p>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Registrar tiempo manual</h3>
                <p class="text-xs text-gray-500 mb-3">Usa si olvidaste iniciar el timer.</p>
                @can('trackTimeManual', $task)
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

    <div class="card" id="task-time-entries">
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

    <div class="card" id="task-comments">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Comentarios</h3>
            <p class="text-xs text-gray-500 mb-3">Deja avances o acuerdos con el equipo.</p>
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

    <div class="card" id="task-attachments">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Adjuntos</h3>
            <p class="text-xs text-gray-500 mb-3">Sube archivos relacionados con la tarea.</p>
            <div class="space-y-2 text-sm">
                @forelse($task->attachments as $attachment)
                        <div class="flex items-center justify-between border border-gray-200 rounded px-2 py-2">
                            <div>
                                <p class="text-gray-900">{{ $attachment->original_name }}</p>
                                <p class="text-xs text-gray-500">{{ $attachment->size_bytes }} bytes</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('tasks.attachments.download', [$project, $task, $attachment]) }}" class="btn-secondary text-xs">Descargar</a>
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
