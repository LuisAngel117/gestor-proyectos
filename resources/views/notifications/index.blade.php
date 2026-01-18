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
    $socialNotifications = $collection->reject(fn ($n) => in_array($n->data['event'] ?? '', $workEvents, true));
@endphp

<div class="space-y-6">
    @if ($notifications->isEmpty())
        <div class="card">
            <div class="card-body text-center text-sm text-gray-600">No hay notificaciones.</div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Trabajo</h3>
                    <div class="space-y-3">
                        @forelse ($workNotifications as $notification)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $notification->data['event'] ?? 'evento' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $notification->created_at?->format('Y-m-d H:i') }}
                                        </p>
                                        @if (!empty($notification->data['task_title']))
                                            <p class="text-sm text-gray-700 mt-2">
                                                Tarea: {{ $notification->data['task_title'] }}
                                            </p>
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

            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Social</h3>
                    <div class="space-y-3">
                        @forelse ($socialNotifications as $notification)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $notification->data['event'] ?? 'evento' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $notification->created_at?->format('Y-m-d H:i') }}
                                        </p>
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
                            <p class="text-sm text-gray-500">Sin notificaciones sociales.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
