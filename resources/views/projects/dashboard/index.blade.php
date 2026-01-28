@extends('layouts.app')

@section('title', 'Dashboard - ' . $project->name)

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
                <span>Dashboard</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Volver al proyecto</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.dashboard.index', $project) }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sprint</label>
                    <select name="sprint" class="form-input">
                        <option value="active" @selected(($filters['sprint'] ?? '') === 'active')>Activo</option>
                        @foreach($sprints as $option)
                            <option value="{{ $option->id }}" @selected((string) ($filters['sprint'] ?? '') === (string) $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-secondary">Aplicar</button>
            </form>
            @if($warning)
                <p class="text-sm text-yellow-600 mt-2">{{ $warning }}</p>
            @endif
        </div>
    </div>

    @if($sprint)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card">
                <div class="card-body">
                    <p class="text-xs text-gray-500 uppercase">Velocidad</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $metrics['velocity']['completed_count'] ?? 0 }}</p>
                    <p class="text-sm text-gray-600">Horas completadas: {{ $metrics['velocity']['total_hours'] ?? 0 }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <p class="text-xs text-gray-500 uppercase">Sprint</p>
                    <p class="text-sm text-gray-700">{{ $sprint->name }}</p>
                    <p class="text-xs text-gray-500">Estado: {{ $sprint->status }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <p class="text-xs text-gray-500 uppercase">Sprint anterior</p>
                    @if($previousSprint && $metrics['previous_velocity'])
                        <p class="text-sm text-gray-700">{{ $previousSprint->name }}</p>
                        <p class="text-xs text-gray-500">{{ $metrics['previous_velocity']['total_hours'] }}h</p>
                    @else
                        <p class="text-sm text-gray-500">Sin datos</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Tiempo en estado (promedio)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($metrics['time_in_state']['statuses'] as $key => $status)
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-sm font-semibold">{{ $status['label'] }}</p>
                            <p class="text-xs text-gray-500">Promedio: {{ $metrics['time_in_state']['summaries'][$key]['avg_hours'] ?? 0 }}h</p>
                            <p class="text-xs text-gray-500">Tareas: {{ $metrics['time_in_state']['summaries'][$key]['task_count'] ?? 0 }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Carga por usuario</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Horas planificadas</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tareas planificadas</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Horas reales</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($metrics['workload']['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['label'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['planned_hours'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['planned_tasks'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $row['real_hours'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-sm text-gray-500">No hay sprint seleccionado.</div>
        </div>
    @endif
</div>
@endsection
