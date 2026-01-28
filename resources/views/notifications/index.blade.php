@extends('layouts.app')

@section('title', 'Notificaciones')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notificaciones</h2>
            <p class="text-sm text-gray-600 mt-1">Tu bandeja de alertas internas.</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-secondary">Marcar todas</button>
            </form>
            <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
@php
    $workEvents = ['task_assigned', 'task_time_logged'];
    $collection = $notifications->getCollection();
    $workNotifications = $collection->filter(fn ($n) => in_array($n->data['event'] ?? '', $workEvents, true));
@endphp

<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Ayuda r&aacute;pida</h3>
            <p class="text-sm text-gray-600">
                Aqu&iacute; solo ves notificaciones de trabajo. Los mensajes sociales est&aacute;n en
                <a href="{{ route('messages.index') }}" class="text-primary-600">Mensajes</a>.
            </p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Trabajo</h3>
            <div class="space-y-3">
                @forelse ($workNotifications as $notification)
                    @php
                        $meta = $notification->meta ?? [];
                        $event = $meta['event'] ?? ($notification->data['event'] ?? null);
                        $task = $meta['task'] ?? null;
                        $actor = $meta['actor'] ?? null;
                        $assignedAt = $meta['assigned_at'] ?? null;
                        $occurredAt = $meta['occurred_at'] ?? null;
                        $projectId = $meta['project_id'] ?? ($notification->data['project_id'] ?? null);
                        $taskLink = ($projectId && $task) ? route('tasks.show', [$projectId, $task]) : null;
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                @if ($event === 'task_assigned')
                                    <p class="text-sm font-semibold text-gray-900">Tarea asignada</p>
                                    <p class="text-sm text-gray-700 mt-1">
                                        @if ($actor)
                                            <span class="font-medium">{{ $actor->full_name }}</span> te asign&oacute; una tarea.
                                        @else
                                            Te asignaron una tarea.
                                        @endif
                                    </p>
                                @elseif ($event === 'task_time_logged')
                                    <p class="text-sm font-semibold text-gray-900">Tiempo registrado</p>
                                    <p class="text-sm text-gray-700 mt-1">
                                        @if ($actor)
                                            <span class="font-medium">{{ $actor->full_name }}</span>
                                        @else
                                            Un usuario
                                        @endif
                                        registr&oacute; tiempo en una tarea.
                                    </p>
                                @else
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $notification->data['event'] ?? 'Notificaci&oacute;n' }}
                                    </p>
                                @endif

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ ($assignedAt ?? $occurredAt ?? $notification->created_at)?->format('Y-m-d H:i') }}
                                </p>

                                @if ($task)
                                    <div class="text-sm text-gray-700 mt-2">
                                        Tarea:
                                        @if ($taskLink)
                                            <a href="{{ $taskLink }}" class="text-primary-600 hover:underline">{{ $task->title }}</a>
                                        @else
                                            {{ $task->title }}
                                        @endif
                                    </div>
                                    @if ($task->due_date)
                                        <p class="text-xs text-gray-500">Fecha l&iacute;mite: {{ $task->due_date->format('d/m/Y') }}</p>
                                    @endif
                                @elseif (!empty($notification->data['task_title']))
                                    <p class="text-sm text-gray-700 mt-2">Tarea: {{ $notification->data['task_title'] }}</p>
                                @endif

                                @if ($event === 'task_time_logged' && !empty($meta['duration_seconds']))
                                    @php($minutes = round(($meta['duration_seconds'] ?? 0) / 60, 1))
                                    <p class="text-xs text-gray-500 mt-1">Duraci&oacute;n: {{ $minutes }} min ({{ $meta['source'] ?? 'manual' }})</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($notification->read_at)
                                    <span class="badge badge-success">Leida</span>
                                @else
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-secondary text-sm">Marcar leida</button>
                                    </form>
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Sin notificaciones de trabajo.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
