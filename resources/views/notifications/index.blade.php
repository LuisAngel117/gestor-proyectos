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
    $workEvents = ['task_assigned', 'task_time_logged', 'task_timer_started'];
    $collection = $notifications->getCollection();
    $workNotifications = $collection->filter(fn ($n) => in_array($n->data['event'] ?? '', $workEvents, true));
    $unreadTotal = $workNotifications->whereNull('read_at')->count();
@endphp

<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Ayuda r&aacute;pida</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Aqu&iacute; solo ves notificaciones de trabajo. Los mensajes sociales est&aacute;n en
                        <a href="{{ route('messages.index') }}" class="text-primary-600">Mensajes</a>.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-50 border border-gray-200 text-gray-600">
                        Total: {{ $workNotifications->count() }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700">
                        Pendientes: {{ $unreadTotal }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Trabajo</h3>
                <span class="text-xs text-gray-500">Últimas notificaciones</span>
            </div>
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
                    <div class="border border-gray-200 rounded-2xl p-4 bg-white shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($actor?->full_name ?? 'N', 0, 1)) }}
                                </div>
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
                                @elseif ($event === 'task_timer_started')
                                    <p class="text-sm font-semibold text-gray-900">Timer iniciado</p>
                                    <p class="text-sm text-gray-700 mt-1">
                                        @if ($actor)
                                            <span class="font-medium">{{ $actor->full_name }}</span>
                                        @else
                                            Un usuario
                                        @endif
                                        inici&oacute; el timer de una tarea.
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
