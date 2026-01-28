@php
    $labelMap = $labelMap ?? [];
    $typeBadge = $typeBadge ?? null;
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Accion</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Entidad</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Detalle</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $labelMap[$log->action] ?? $log->action }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if($typeBadge)
                            <span class="inline-flex items-center px-2 py-1 rounded text-[10px] {{ $typeBadge($log->action) }}">
                                {{ ucfirst(explode('.', $log->action)[0] ?? 'N/A') }}
                            </span>
                        @endif
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
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-sm text-gray-500 text-center">
                        No hay historial para estos filtros.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
