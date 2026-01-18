@extends('layouts.app')

@section('header')
<div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $sprint->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('plan', $sprint)
                @if($sprint->isPlanning())
                    <a href="{{ route('sprints.plan', [$project, $sprint]) }}" class="btn-primary">
                        Planificar
                    </a>
                @endif
            @endcan
            <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection
@section('title', 'Sprint - ' . $sprint->name)

@section('content')


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-body space-y-4">
                <div class="flex items-center justify-between">
                    <span class="badge badge-secondary">{{ ucfirst($sprint->status) }}</span>
                    <span class="text-xs text-gray-500">Secuencia #{{ $sprint->sequence }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Inicio</p>
                        <p class="text-gray-900">{{ $sprint->start_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Fin</p>
                        <p class="text-gray-900">{{ $sprint->end_date->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Ítems asignados</p>
                        <p class="text-gray-900">{{ $sprint->backlog_items_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estado operativo</p>
                        <p class="text-gray-900">
                            @if($sprint->isClosed() && $sprint->closed_at)
                                Cerrado el {{ $sprint->closed_at->format('d/m/Y H:i') }}
                            @elseif($sprint->started_at)
                                Iniciado el {{ $sprint->started_at->format('d/m/Y H:i') }}
                            @else
                                Sin iniciar
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @can('startSprint', $sprint)
            @if($sprint->isPlanning())
                <form method="POST" action="{{ route('sprints.start', [$project, $sprint]) }}" class="card">
                    @csrf
                    <div class="card-body">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Iniciar sprint</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Verifica que el backlog del sprint está listo antes de iniciar.
                        </p>
                        <button type="submit" class="btn-primary w-full">Iniciar sprint</button>
                    </div>
                </form>
            @endif
        @endcan

        @can('closeSprint', $sprint)
            @if($sprint->isActive())
                <form method="POST" action="{{ route('sprints.close', [$project, $sprint]) }}" class="card">
                    @csrf
                    <div class="card-body">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Cerrar sprint</h3>
                        <p class="text-xs text-gray-500 mb-4">
                            Esta acción marca el sprint como cerrado.
                        </p>
                        <button type="submit" class="btn-secondary w-full">Cerrar sprint</button>
                    </div>
                </form>
            @endif
        @endcan
    </div>
</div>
@endsection

