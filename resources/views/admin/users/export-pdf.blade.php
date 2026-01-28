<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        h2 { font-size: 14px; margin: 18px 0 6px 0; }
        p { margin: 0 0 12px 0; color: #6b7280; }
        .section { margin-bottom: 16px; }
        .muted { color: #6b7280; font-size: 11px; }
        .chip { display: inline-block; padding: 2px 6px; border-radius: 10px; background: #eef2ff; color: #3730a3; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h1>Usuarios</h1>
    <p>Generado: {{ $generatedAt->format('Y-m-d H:i') }}</p>

    @foreach ($users as $user)
        @php($tasks = $taskMap[$user->id] ?? collect())
        <div class="section">
            <h2>{{ $user->full_name }} <span class="chip">{{ $user->role }}</span></h2>
            <p class="muted">Estado: {{ $user->estado }} | Email: {{ $user->email }}</p>

            <table>
                <thead>
                    <tr>
                        <th colspan="4">Datos del usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Nombre</strong><br>{{ $user->full_name }}</td>
                        <td><strong>Email</strong><br>{{ $user->email }}</td>
                        <td><strong>Rol del sistema</strong><br>{{ $user->role }}</td>
                        <td><strong>Estado</strong><br>{{ $user->estado }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tel&eacute;fono</strong><br>{{ $user->profile?->telefono ?? 'Sin registro' }}</td>
                        <td><strong>Cargo</strong><br>{{ $user->profile?->cargo ?? 'Sin registro' }}</td>
                        <td><strong>Departamento</strong><br>{{ $user->profile?->departamento ?? 'Sin registro' }}</td>
                        <td><strong>ID universitario</strong><br>{{ $user->profile?->id_universitario ?? 'Sin registro' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Equipos ({{ $user->teams->count() }})</h2>
            @if($user->teams->isEmpty())
                <p class="muted">Sin equipos asignados.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Equipo</th>
                            <th>Rol en equipo</th>
                            <th>Ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->teams as $team)
                            <tr>
                                <td>{{ $team->name }}</td>
                                <td>{{ $team->pivot->role ?? 'Sin rol' }}</td>
                                <td>{{ $team->pivot->joined_at ? \Carbon\Carbon::parse($team->pivot->joined_at)->format('Y-m-d') : 'Sin fecha' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <h2>Proyectos ({{ $user->projects->count() }})</h2>
            @if($user->projects->isEmpty())
                <p class="muted">Sin proyectos asignados.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Equipo</th>
                            <th>Rol en proyecto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->projects as $project)
                            <tr>
                                <td>{{ $project->name }}</td>
                                <td>{{ $project->team?->name ?? 'Sin equipo' }}</td>
                                <td>{{ $project->pivot->role ?? 'Sin rol' }}</td>
                                <td>{{ $project->status ?? 'Sin estado' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <h2>&Uacute;ltimas tareas asignadas ({{ $tasks->count() }} de {{ $taskLimit }})</h2>
            @if($tasks->isEmpty())
                <p class="muted">Sin tareas asignadas.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Tarea</th>
                            <th>Proyecto</th>
                            <th>Sprint</th>
                            <th>Estado</th>
                            <th>Fecha l&iacute;mite</th>
                            <th>Asignada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->project?->name ?? 'Sin proyecto' }}</td>
                                <td>{{ $task->sprint?->name ?? 'Sin sprint' }}</td>
                                <td>{{ $task->status }}</td>
                                <td>{{ $task->due_date ? $task->due_date->format('Y-m-d') : 'Sin fecha' }}</td>
                                <td>{{ $task->pivot?->assigned_at ? \Carbon\Carbon::parse($task->pivot->assigned_at)->format('Y-m-d H:i') : 'Sin fecha' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
