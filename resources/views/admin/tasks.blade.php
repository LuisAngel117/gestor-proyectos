@extends('layouts.app')

@section('title', 'Tareas globales')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tareas globales</h2>
            <p class="text-sm text-gray-600 mt-1">Listado completo de tareas de todos los equipos.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
@php
    $statuses = \App\Services\Boards\ScrumBoardService::STATUSES;
    $teams = collect();
    $totalTasks = $tasks->total() ?? $tasks->count();
    $doneCount = 0;
    foreach ($tasks as $task) {
        if ($task->project?->team) {
            $teams->put($task->project->team->id, $task->project->team);
        }
        if ($task->status === 'done') {
            $doneCount++;
        }
    }
@endphp
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-primary-600">Tareas globales</p>
                    <h3 class="text-lg font-semibold text-gray-900 mt-1">Resumen general</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Desde aqu&iacute; puedes revisar r&aacute;pidamente el avance. Para cambios detallados, entra a la tarea o al tablero del proyecto.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.calendar') }}" class="btn-secondary text-xs">Calendario global</a>
                    <a href="{{ route('admin.scrum') }}" class="btn-secondary text-xs">Scrum global</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Tareas totales</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $totalTasks }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Completadas (p&aacute;gina)</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $doneCount }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Equipos activos</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $teams->count() }}</p>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 bg-white">
                    <p class="text-xs text-gray-500">Pendientes (p&aacute;gina)</p>
                    <p class="text-xl font-semibold text-gray-900">{{ max(0, $tasks->count() - $doneCount) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tarea</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Equipo / Proyecto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sprint</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Asignados</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tasks as $task)
                            @php($status = $statuses[$task->status] ?? ['label' => $task->status, 'color' => 'secondary'])
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <div>{{ $task->project?->team?->name ?? 'Sin equipo' }}</div>
                                    <div class="text-xs text-gray-500">{{ $task->project?->name ?? 'Sin proyecto' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <span class="badge badge-{{ $status['color'] }}">{{ $status['label'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $task->sprint?->name ?? 'Sin sprint' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $task->due_date ? $task->due_date->format('d/m/Y') : 'Sin fecha' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $task->assignees->count() }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 space-x-2">
                                    @if($task->project_id)
                                        <a href="{{ route('tasks.show', [$task->project_id, $task]) }}" class="btn-secondary text-xs">Ver tarea</a>
                                        <a href="{{ route('projects.scrum-board.index', $task->project_id) }}" class="btn-secondary text-xs">Tablero</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
