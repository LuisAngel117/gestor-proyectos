@extends('layouts.app')

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
                <a href="{{ route('backlog.index', $project) }}" class="hover:text-primary-600">Backlog</a>
                <span class="mx-1">/</span>
                <span>Editar</span>
            </nav>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar ítem de backlog
            </h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <a href="{{ route('backlog.index', $project) }}" class="btn-secondary">Volver</a>
    </div>
@endsection
@section('title', 'Editar ítem de backlog')

@section('content')


<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('backlog.update', [$project, $item]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Nombre <span class="ml-1 text-gray-400 cursor-help" title="Actualiza el resumen del trabajo">?</span></label>
                <input id="name" name="name" type="text" class="form-input w-full" value="{{ old('name', $item->name) }}" required>
                @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="description">Descripción <span class="ml-1 text-gray-400 cursor-help" title="Agrega detalles para planificar mejor">?</span></label>
                <textarea id="description" name="description" class="form-input w-full" rows="4">{{ old('description', $item->description) }}</textarea>
                @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="priority">Prioridad <span class="ml-1 text-gray-400 cursor-help" title="Define el orden de ejecucion">?</span></label>
                <select id="priority" name="priority" class="form-input w-full" required>
                    @foreach($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', $item->priority) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="status">Estado <span class="ml-1 text-gray-400 cursor-help" title="Backlog = pendiente, Refinado = listo, Archivado = fuera de alcance">?</span></label>
                <select id="status" name="status" class="form-input w-full" required>
                    @foreach(['backlog' => 'Backlog', 'refinado' => 'Refinado', 'archivado' => 'Archivado'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $item->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

