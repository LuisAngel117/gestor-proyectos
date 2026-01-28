<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de usuario</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 6px; }
        .muted { color: #6b7280; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .02em; color: #6b7280; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Historial de {{ $user->full_name }}</h1>
    <div class="meta muted">
        Generado: {{ $generatedAt->format('Y-m-d H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 14%;">Fecha</th>
                <th style="width: 26%;">Accion</th>
                <th style="width: 18%;">Entidad</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $log->action }}</td>
                <td>
                    {{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : '-' }}
                </td>
                <td>
                    @if(!empty($log->meta))
                        @foreach($log->meta as $key => $value)
                            <div><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</div>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="muted">No hay historial para los filtros seleccionados.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
