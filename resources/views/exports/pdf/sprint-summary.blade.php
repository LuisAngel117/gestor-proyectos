<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen Sprint</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1, h2, h3 { margin: 0 0 6px 0; }
        .muted { color: #6b7280; }
        .section { margin-bottom: 18px; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; }
        .summary { border-collapse: collapse; width: 100%; }
        .summary th, .summary td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        .summary th { background: #f3f4f6; }
        .table { border-collapse: collapse; width: 100%; }
        .table th, .table td { border: 1px solid #e5e7eb; padding: 5px; }
        .table th { background: #f9fafb; font-size: 10px; text-transform: uppercase; letter-spacing: 0.02em; }
    </style>
</head>
<body>
    <div class="section">
        <h1>Resumen de Sprint</h1>
        <p class="muted">
            Proyecto: {{ $project->name }} |
            Sprint: {{ $sprint->name }} |
            Estado: {{ $sprint->status }} |
            Generado: {{ $generated_at->format('Y-m-d H:i') }}
        </p>
        <p class="muted">
            Fechas: {{ $sprint->start_date?->format('Y-m-d') ?? 'N/A' }} -
            {{ $sprint->end_date?->format('Y-m-d') ?? 'N/A' }}
        </p>
    </div>

    <div class="section">
        <h2>Resumen</h2>
        <table class="summary">
            <tr>
                <th>Total tareas</th>
                <td>{{ $tasks_total }}</td>
                <th>Estimado total (h)</th>
                <td>{{ $estimated_total }}</td>
                <th>Estimado completado (h)</th>
                <td>{{ $estimated_completed }}</td>
            </tr>
            <tr>
                <th>Tiempo real (h)</th>
                <td>{{ $real_total_hours }}</td>
                <th colspan="4">Estados</th>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="4">
                    @foreach($status_counts as $key => $count)
                        <span>{{ $statuses[$key]['label'] ?? $key }}: {{ $count }}</span>
                        @if(!$loop->last) | @endif
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Burndown</h2>
        {!! $burndown_svg !!}
    </div>

    <div class="section">
        <h2>Tareas del sprint</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulo</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Asignados</th>
                    <th>Estimado (h)</th>
                    <th>Real (h)</th>
                    <th>Completado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                    <tr>
                        <td>{{ $task['id'] }}</td>
                        <td>{{ $task['title'] }}</td>
                        <td>{{ $statuses[$task['status']]['label'] ?? $task['status'] }}</td>
                        <td>{{ $task['priority'] }}</td>
                        <td>{{ $task['assignees'] ?: 'Sin asignar' }}</td>
                        <td>{{ $task['estimated_hours'] }}</td>
                        <td>{{ $task['real_hours'] }}</td>
                        <td>{{ $task['completed_at'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
