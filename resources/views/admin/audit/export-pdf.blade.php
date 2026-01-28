<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Auditoria</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 6px; }
        .muted { color: #6b7280; }
        .meta { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .02em; color: #6b7280; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 9px; background: #eef2ff; color: #4338ca; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Auditoria del sistema</h1>
    <div class="meta muted">
        Generado: {{ $generatedAt->format('Y-m-d H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 14%;">Fecha</th>
                <th style="width: 18%;">Usuario</th>
                <th style="width: 24%;">Accion</th>
                <th style="width: 16%;">Entidad</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td class="nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $log->user?->full_name ?? 'Sistema' }}</td>
                <td>{{ $log->action }}</td>
                <td>
                    @if($log->auditable_type)
                        <span class="badge">{{ ucfirst(explode('.', $log->action)[0] ?? 'N/A') }}</span>
                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                    @else
                        -
                    @endif
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
                <td colspan="5" class="muted">No hay registros para los filtros seleccionados.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
