@extends('layouts.app')

@section('title', 'Auditoria')

@section('sidebar')
    @include('components.sidebar')
@endsection

@section('header')
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Auditoria</h2>
            <p class="text-sm text-gray-600 mt-1">Registro de actividad clave del sistema.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-body">
            @php
                $typeOptions = [
                    'team' => 'Equipos',
                    'project' => 'Proyectos',
                    'sprint' => 'Sprints',
                    'task' => 'Tareas',
                    'checklist' => 'Checklist',
                    'time' => 'Tiempo manual',
                    'timer' => 'Timer',
                    'message' => 'Mensajes',
                    'auth' => 'Sesion',
                ];
                $actionOptions = [
                    'create' => 'Crear',
                    'update' => 'Actualizar',
                    'delete' => 'Eliminar',
                    'move' => 'Mover',
                    'assign' => 'Asignar',
                    'unassign' => 'Desasignar',
                    'start' => 'Iniciar',
                    'stop' => 'Finalizar',
                    'login' => 'Login',
                    'logout' => 'Logout',
                ];
                $labelMap = [
                    'auth.login' => 'Inicio de sesion',
                    'auth.logout' => 'Cierre de sesion',
                    'team.create' => 'Equipo creado',
                    'team.update' => 'Equipo actualizado',
                    'team.delete' => 'Equipo eliminado',
                    'team.member.add' => 'Miembro agregado al equipo',
                    'team.member.role' => 'Rol de equipo actualizado',
                    'team.member.remove' => 'Miembro removido del equipo',
                    'project.create' => 'Proyecto creado',
                    'project.update' => 'Proyecto actualizado',
                    'project.delete' => 'Proyecto eliminado',
                    'project.transfer_owner' => 'Owner del proyecto actualizado',
                    'project.member.add' => 'Miembro agregado al proyecto',
                    'project.member.role' => 'Rol de proyecto actualizado',
                    'project.member.remove' => 'Miembro removido del proyecto',
                    'sprint.create' => 'Sprint creado',
                    'sprint.delete' => 'Sprint eliminado',
                    'sprint.start' => 'Sprint iniciado',
                    'sprint.close' => 'Sprint cerrado',
                    'task.create' => 'Tarea creada',
                    'task.update' => 'Tarea actualizada',
                    'task.delete' => 'Tarea eliminada',
                    'task.move' => 'Tarea movida',
                    'task.assign' => 'Tarea asignada',
                    'task.unassign' => 'Tarea desasignada',
                    'checklist.update' => 'Checklist actualizado',
                    'checklist.delete' => 'Checklist eliminado',
                    'timer.start' => 'Timer iniciado',
                    'timer.stop' => 'Timer finalizado',
                    'time.manual.create' => 'Tiempo manual registrado',
                    'time.manual.update' => 'Tiempo manual actualizado',
                    'time.manual.delete' => 'Tiempo manual eliminado',
                    'message.send' => 'Mensaje enviado',
                ];
                $typeBadge = function ($action) {
                    $prefix = explode('.', $action)[0] ?? '';
                    $colors = [
                        'auth' => 'bg-slate-100 text-slate-700',
                        'team' => 'bg-sky-100 text-sky-700',
                        'project' => 'bg-indigo-100 text-indigo-700',
                        'sprint' => 'bg-amber-100 text-amber-700',
                        'task' => 'bg-violet-100 text-violet-700',
                        'checklist' => 'bg-emerald-100 text-emerald-700',
                        'timer' => 'bg-rose-100 text-rose-700',
                        'time' => 'bg-rose-100 text-rose-700',
                        'message' => 'bg-teal-100 text-teal-700',
                    ];
                    return $colors[$prefix] ?? 'bg-gray-100 text-gray-700';
                };
            @endphp
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Usuario</label>
                    <select name="user" class="form-input w-full">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($filters['user'] == $user->id)>{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                    <select name="type" class="form-input w-full">
                        <option value="">Todos</option>
                        @foreach($typeOptions as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Accion</label>
                    <select name="action" class="form-input w-full">
                        <option value="">Todas</option>
                        @foreach($actionOptions as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['action'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-6 flex flex-wrap items-center gap-2">
                    <button class="btn-primary" type="submit">Filtrar</button>
                    <a href="{{ route('admin.audit.index') }}" class="btn-secondary">Limpiar</a>
                    <a href="{{ route('admin.audit.export-pdf', request()->query()) }}" class="btn-secondary">Exportar PDF</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Accion</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Entidad</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $log->user?->full_name ?? 'Sistema' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $labelMap[$log->action] ?? $log->action }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] {{ $typeBadge($log->action) }}">
                                        {{ ucfirst(explode('.', $log->action)[0] ?? 'N/A') }}
                                    </span>
                                    {{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if(!empty($log->meta))
                                        @foreach($log->meta as $key => $value)
                                            <div><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
