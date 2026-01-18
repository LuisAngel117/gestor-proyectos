@extends('layouts.app')

@section('title', 'Crear Sprint')

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Sprint</h2>
            <p class="text-sm text-gray-600 mt-1">Proyecto: {{ $project->name }}</p>
        </div>
        <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sprints.store', $project) }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="form-label">Nombre del Sprint *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', 'Sprint ' . $nextSequence) }}"
                        class="form-input w-full @error('name') border-red-500 @enderror"
                        required
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="goal" class="form-label">Objetivo</label>
                    <textarea
                        id="goal"
                        name="goal"
                        rows="3"
                        class="form-input w-full @error('goal') border-red-500 @enderror"
                        placeholder="Describe el objetivo principal del sprint..."
                    >{{ old('goal') }}</textarea>
                    @error('goal')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="form-label">Fecha de inicio *</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date') }}"
                            class="form-input w-full @error('start_date') border-red-500 @enderror"
                            required
                        >
                        @error('start_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="form-label">Fecha de fin *</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            class="form-input w-full @error('end_date') border-red-500 @enderror"
                            required
                        >
                        @error('end_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="status" class="form-label">Estado *</label>
                    <select
                        id="status"
                        name="status"
                        class="form-input w-full @error('status') border-red-500 @enderror"
                        required
                    >
                        <option value="planificacion" @selected(old('status', 'planificacion') === 'planificacion')>Planificación</option>
                        <option value="activo" @selected(old('status') === 'activo')>Activo</option>
                        <option value="cerrado" @selected(old('status') === 'cerrado')>Cerrado</option>
                    </select>
                    @error('status')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700">
                        Recomendación: inicia en “Planificación” y luego usa la vista de planificación para asignar backlog.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('sprints.index', $project) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Crear Sprint</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
