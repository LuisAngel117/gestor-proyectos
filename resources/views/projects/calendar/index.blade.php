@extends('layouts.app')

@section('title', 'Calendario - ' . $project->name)

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
                <span>Calendario</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendario</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('projects.calendar.index', [$project, 'month' => $calendar['prev_month']] + request()->except('month')) }}" class="btn-secondary">Mes anterior</a>
            <a href="{{ route('projects.calendar.index', [$project, 'month' => $calendar['next_month']] + request()->except('month')) }}" class="btn-secondary">Mes siguiente</a>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Gu&iacute;a r&aacute;pida</h3>
            <p class="text-sm text-gray-600 mb-3">El calendario muestra tareas con fecha objetivo.</p>
            <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                <li>Agrega fecha objetivo desde la vista de tareas.</li>
                <li>Filtra por sprint, estado o asignado.</li>
                <li>Las tareas sin fecha aparecen abajo.</li>
            </ul>
            <div class="flex flex-wrap gap-2 mt-4">
                <a href="{{ route('tasks.index', $project) }}" class="btn-secondary text-xs">Ir a tareas</a>
                <a href="{{ route('backlog.index', $project) }}" class="btn-secondary text-xs">Ver backlog</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.calendar.index', $project) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="hidden" name="month" value="{{ $calendar['month_value'] }}">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sprint</label>
                    <select name="sprint" class="form-input">
                        <option value="">Todos</option>
                        <option value="active" @selected(($calendar['filters']['sprint'] ?? '') === 'active')>Activo</option>
                        <option value="backlog" @selected(($calendar['filters']['sprint'] ?? '') === 'backlog')>Backlog</option>
                        @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}" @selected((string) ($calendar['filters']['sprint'] ?? '') === (string) $sprint->id)>{{ $sprint->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="status" class="form-input">
                        <option value="">Todos</option>
                        @foreach($calendar['statuses'] as $key => $status)
                            <option value="{{ $key }}" @selected(($calendar['filters']['status'] ?? '') === $key)>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Asignado</label>
                    <select name="assignee" class="form-input">
                        <option value="">Todos</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected((string) ($calendar['filters']['assignee'] ?? '') === (string) $assignee->id)>{{ $assignee->name }} {{ $assignee->apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-secondary">Aplicar</button>
                    <a href="{{ route('projects.calendar.index', $project) }}" class="btn-secondary">Limpiar</a>
                </div>
            </form>
            @if(!empty($calendar['warnings']))
                <div class="mt-3 text-sm text-yellow-600">
                    @foreach($calendar['warnings'] as $warning)
                        <p>{{ $warning }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ $calendar['month_label'] }}</h3>
            <div class="grid grid-cols-7 gap-2 text-xs text-gray-500 mb-2">
                <div>Lun</div><div>Mar</div><div>Mie</div><div>Jue</div><div>Vie</div><div>Sab</div><div>Dom</div>
            </div>
            <div class="grid grid-cols-7 gap-2">
                @foreach($calendar['days'] as $day)
                    <div class="border border-gray-200 rounded p-2 min-h-[110px] {{ $day['is_current_month'] ? '' : 'bg-gray-50' }}">
                        <div class="text-xs font-semibold text-gray-700">{{ $day['date']->format('d') }}</div>
                        <div class="mt-1 space-y-1">
                            @foreach($day['tasks'] as $task)
                                <a href="{{ route('tasks.show', [$project, $task]) }}" class="block text-xs text-gray-700 hover:text-primary-600">
                                    {{ $task->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Tareas sin fecha</h3>
            @if($calendar['undated_tasks']->isEmpty())
                <p class="text-sm text-gray-500">Sin tareas sin fecha.</p>
                <p class="text-xs text-gray-500 mt-2">Asigna una fecha objetivo desde la vista de tareas para verlas en el calendario.</p>
            @else
                <div class="space-y-2">
                    @foreach($calendar['undated_tasks'] as $task)
                        <a href="{{ route('tasks.show', [$project, $task]) }}" class="block text-sm text-gray-700 hover:text-primary-600">
                            {{ $task->title }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
