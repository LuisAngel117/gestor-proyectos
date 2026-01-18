@extends('layouts.app')

@section('title', 'Usuarios')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios</h2>
            <p class="text-sm text-gray-600 mt-1">Gestion global de usuarios.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.create') }}" class="btn-primary">Crear usuario</a>
            <a href="{{ route('admin.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>
@endsection

@section('content')
<div
    class="space-y-6"
    x-data="{
        pageIds: @json($users->pluck('id')),
        selected: [],
        selectAll: false,
        confirmOpen: false,
        bulkMode: false,
        actionUrl: '',
        singleBase: '{{ url('admin/users') }}',
        bulkUrl: '{{ route('admin.users.bulk-deactivate') }}',
        init() {
            this.$watch('selected', value => {
                this.selectAll = value.length === this.pageIds.length && this.pageIds.length > 0;
            });
        },
        toggleAll() {
            this.selected = this.selectAll ? [...this.pageIds] : [];
        },
        openSingle(id) {
            this.bulkMode = false;
            this.actionUrl = `${this.singleBase}/${id}/deactivate`;
            this.confirmOpen = true;
        },
        openBulk() {
            if (this.selected.length === 0) return;
            this.bulkMode = true;
            this.actionUrl = this.bulkUrl;
            this.confirmOpen = true;
        },
        closeModal() {
            this.confirmOpen = false;
        }
    }"
    x-init="init()"
>
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Buscar usuarios (nombre, email, rol, estado o equipo)</label>
                    <input name="q" type="text" class="form-input w-full" placeholder="Ej: admin, activo, Equipo Alpha" value="{{ $filters['q'] ?? '' }}">
                </div>
                <button type="submit" class="btn-secondary">Buscar</button>
                @if(!empty($filters['q']))
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Limpiar</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <div class="text-sm text-gray-600" x-show="selected.length > 0" x-cloak>
                    <span x-text="`${selected.length} seleccionados`"></span>
                </div>
                <div class="flex flex-wrap gap-2 ml-auto">
                    <button
                        type="button"
                        class="btn-danger text-xs"
                        @click="openBulk()"
                        :disabled="selected.length === 0"
                        :class="{ 'opacity-50 cursor-not-allowed': selected.length === 0 }"
                    >
                        <span x-text="selected.length === 1 ? 'Eliminar' : 'Eliminar seleccionados'"></span>
                    </button>
                    <form method="POST" action="{{ route('admin.users.export-pdf') }}" class="inline-flex">
                        @csrf
                        <template x-for="id in selected" :key="`export-${id}`">
                            <input type="hidden" name="user_ids[]" :value="id">
                        </template>
                        <button
                            type="submit"
                            class="btn-secondary text-xs"
                            :disabled="selected.length === 0"
                            :class="{ 'opacity-50 cursor-not-allowed': selected.length === 0 }"
                        >
                            Exportar PDF
                        </button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" class="form-input h-4 w-4" x-model="selectAll" @change="toggleAll">
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Equipos</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Proyectos</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="checkbox" class="form-input h-4 w-4" value="{{ $user->id }}" x-model="selected">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $user->role }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $user->estado }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->teams_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $user->projects_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary text-xs">Detalles</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <div
        x-show="confirmOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-gray-900/50" @click="closeModal()"></div>
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminacion</h3>
            <p class="text-sm text-gray-600 mt-2">
                Esta accion marcara los usuarios como inactivos. Confirma con tu contrasena de superadmin.
            </p>
            <form method="POST" :action="actionUrl" class="mt-4 space-y-4">
                @csrf
                <template x-if="bulkMode">
                    <template x-for="id in selected" :key="`delete-${id}`">
                        <input type="hidden" name="user_ids[]" :value="id">
                    </template>
                </template>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Contrasena</label>
                    <input type="password" name="password" class="form-input w-full" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-secondary" @click="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
