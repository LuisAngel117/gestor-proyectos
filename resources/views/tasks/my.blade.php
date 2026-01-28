@extends('layouts.app')

@section('title', 'Mis tareas')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis tareas</h2>
            <p class="text-sm text-gray-600 mt-1">Tareas donde est&aacute;s asignado.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tarea</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Proyecto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sprint</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha l&iacute;mite</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tasks as $task)
                        @php($status = $statuses[$task->status] ?? ['label' => $task->status, 'color' => 'secondary'])
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $task->title }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $task->project?->name ?? 'Sin proyecto' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $task->sprint?->name ?? 'Sin sprint' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge badge-{{ $status['color'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $task->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($task->project)
                                    <a href="{{ route('tasks.show', [$task->project, $task]) }}" class="btn-secondary text-xs">Abrir</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-sm text-gray-500 text-center">
                                No tienes tareas asignadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection
