@extends('layouts.app')

@section('title', 'Backlog - ' . $project->name)

@section('content')
<x-slot name="header">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Backlog · {{ $project->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">Prioriza el trabajo pendiente del proyecto.</p>
        </div>
        <div class="flex gap-2">
            @can('create', [\App\Models\BacklogItem::class, $project])
            <a href="{{ route('backlog.create', $project) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo ítem
            </a>
            @endcan
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">
                Volver al proyecto
            </a>
        </div>
    </div>
</x-slot>

<div class="space-y-6">
    @if($errors->any())
        <div class="card">
            <div class="card-body text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        </div>
    @endif
    @if($items->count() === 0)
        <div class="card">
            <div class="card-body text-center py-10">
                <p class="text-gray-600">No hay ítems en el backlog todavía.</p>
                @can('create', [\App\Models\BacklogItem::class, $project])
                <a href="{{ route('backlog.create', $project) }}" class="btn-primary mt-4">Crear primer ítem</a>
                @endcan
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('backlog.reorder', $project) }}">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posición</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($items as $item)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                name="positions[{{ $item->id }}]"
                                                value="{{ old('positions.' . $item->id, $item->position) }}"
                                                min="1"
                                                class="form-input w-20"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->description ?? 'Sin descripción' }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-{{ $priorityColors[$item->priority] ?? 'secondary' }}">
                                                {{ $priorities[$item->priority] ?? ucfirst($item->priority) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-secondary">{{ ucfirst($item->status) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            @can('update', $item)
                                            <a href="{{ route('backlog.edit', [$project, $item]) }}" class="btn-secondary text-xs py-1 px-3">
                                                Editar
                                            </a>
                                            @endcan
                                            @can('delete', $item)
                                            <form class="inline" method="POST" action="{{ route('backlog.destroy', [$project, $item]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger text-xs py-1 px-3" onclick="return confirm('¿Archivar este ítem?')">
                                                    Archivar
                                                </button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @can('reorder', [\App\Models\BacklogItem::class, $project])
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn-primary">Guardar orden</button>
                    </div>
                    @endcan
                </form>

                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
