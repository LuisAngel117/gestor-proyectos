@extends('layouts.app')

@section('title', 'Tablero Scrum - ' . $project->name)

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tablero Scrum</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Volver al proyecto</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if(!$board['active_sprint'])
        <div class="card">
            <div class="card-body text-sm text-gray-500">No hay sprint activo para mostrar el tablero.</div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="text-sm text-gray-600">
                    Sprint activo: <span class="font-semibold">{{ $board['active_sprint']->name }}</span>
                </div>
            </div>
        </div>

        @foreach($board['lanes'] as $lane)
            <div class="card">
                <div class="card-body">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $lane['label'] }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($board['statuses'] as $statusKey => $status)
                            <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-700">{{ $status['label'] }}</span>
                                    <span class="text-xs text-gray-500">{{ count($board['task_buckets'][$lane['key']][$statusKey]) }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($board['task_buckets'][$lane['key']][$statusKey] as $item)
                                        @php($task = $item['task'])
                                        <div class="bg-white border border-gray-200 rounded p-2 text-sm">
                                            <a href="{{ route('tasks.show', [$project, $task]) }}" class="font-semibold text-gray-800 hover:text-primary-600">
                                                {{ $task->title }}
                                            </a>
                                            @if($item['extra_assignees'] > 0)
                                                <span class="text-xs text-gray-500 block">+{{ $item['extra_assignees'] }} asignados</span>
                                            @endif
                                            @can('update', $task)
                                                <form method="POST" action="{{ route('tasks.scrum-board.move', [$project, $task]) }}" class="mt-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" class="form-input text-xs" onchange="this.form.submit()">
                                                        @foreach($board['statuses'] as $moveKey => $moveStatus)
                                                            <option value="{{ $moveKey }}" @selected($task->status === $moveKey)>{{ $moveStatus['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @endcan
                                        </div>
                                    @endforeach
                                    @if(empty($board['task_buckets'][$lane['key']][$statusKey]))
                                        <p class="text-xs text-gray-400">Sin tareas</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
