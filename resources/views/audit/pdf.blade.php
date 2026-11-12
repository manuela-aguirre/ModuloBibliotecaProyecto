<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de auditoría</title>
    <style>
        @page { margin: 28px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1e293b; }
        h1 { margin: 0 0 5px; color: #14532d; font-size: 17px; }
        p { margin: 3px 0; color: #475569; }
        .summary { margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        th { background: #14532d; color: #fff; font-size: 8px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        td { line-height: 1.35; }
        .date { width: 12%; }
        .event { width: 15%; }
        .activity { width: 25%; }
        .user { width: 14%; }
        .changes { width: 34%; }
    </style>
</head>
<body>
    <h1>Resultados de auditoría</h1>
    <div class="summary">
        <p><strong>Registros:</strong> {{ $total }} · <strong>Generado:</strong> {{ $generatedAt->format('d/m/Y H:i:s T') }}</p>
        <p><strong>Creaciones:</strong> {{ $eventCounts['created'] }} · <strong>Actualizaciones:</strong> {{ $eventCounts['updated'] }} · <strong>Eliminaciones:</strong> {{ $eventCounts['deleted'] }} · <strong>Restauraciones:</strong> {{ $eventCounts['restored'] }}</p>
        @if ($filterSummary)
            <p><strong>Filtros aplicados:</strong> {{ implode(' · ', $filterSummary) }}</p>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th class="date">Fecha y hora</th>
                <th class="event">Módulo / evento</th>
                <th class="activity">Actividad / registro</th>
                <th class="user">Responsable</th>
                <th class="changes">Detalle de cambios</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{!! nl2br(e((string) $value)) !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="5">No hay registros para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
