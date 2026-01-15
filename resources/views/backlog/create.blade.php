@extends('layouts.app')

@section('title', 'Nuevo ítem de backlog')

@section('content')
<x-slot name="header">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo ítem de backlog
            </h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <a href="{{ route('backlog.index', $project) }}" class="btn-secondary">Volver</a>
    </div>
</x-slot>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('backlog.store', $project) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Nombre</label>
                <input id="name" name="name" type="text" class="form-input w-full" value="{{ old('name') }}" required>
                @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="description">Descripción</label>
                <textarea id="description" name="description" class="form-input w-full" rows="4">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="priority">Prioridad</label>
                <select id="priority" name="priority" class="form-input w-full" required>
                    @foreach($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
