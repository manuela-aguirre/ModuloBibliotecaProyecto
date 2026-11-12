<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Historial del ejemplar</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-5">
        <div class="ui-toolbar">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $ejemplar->libro->titulo }}</h3>
                <p class="ui-muted">Código de barras: {{ $ejemplar->codigo_barras }} · Estado actual: {{ ucfirst($ejemplar->estado) }}</p>
            </div>
            <a class="ui-btn ui-btn-light" href="{{ route('libros.ejemplares.index', $ejemplar->libro) }}">Volver a ejemplares</a>
        </div>

        @if ($eventos->isEmpty())
            <div class="ui-empty">Este ejemplar aún no tiene eventos en su historial.</div>
        @else
            <div class="ui-table-scroll" role="region" aria-label="Historial del ejemplar, desplazable horizontalmente" tabindex="0">
                <table class="ui-table">
                    <thead>
                        <tr><th>Fecha</th><th>Evento</th><th>Detalle</th><th>Estado</th><th>Ubicación</th><th>Registrado por</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($eventos as $evento)
                        <tr>
                            <td>{{ $evento->ocurrido_at->format('d/m/Y H:i') }}</td>
                            <td>{{ [
                                'alta' => 'Alta de ejemplar',
                                'prestamo' => 'Préstamo',
                                'devolucion' => 'Devolución',
                                'reparacion' => 'Reparación',
                                'perdida' => 'Pérdida',
                                'cambio_estado' => 'Cambio de estado',
                                'cambio_ubicacion' => 'Cambio de ubicación',
                                'reserva' => 'Reserva',
                                'cancelacion_reserva' => 'Liberación de reserva',
                                'eliminacion' => 'Eliminación',
                            ][$evento->tipo] ?? ucfirst(str_replace('_', ' ', $evento->tipo)) }}</td>
                            <td>{{ $evento->descripcion ?: '—' }}</td>
                            <td>
                                @if ($evento->estado_anterior || $evento->estado_nuevo)
                                    {{ $evento->estado_anterior ? ucfirst($evento->estado_anterior) : '—' }}
                                    → {{ $evento->estado_nuevo ? ucfirst($evento->estado_nuevo) : '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($evento->tipo === 'cambio_ubicacion')
                                    {{ $evento->ubicacion_anterior_nombre ?? $evento->ubicacionAnterior?->nombre ?? 'Sin ubicación' }}
                                    → {{ $evento->ubicacion_nueva_nombre ?? $evento->ubicacionNueva?->nombre ?? 'Sin ubicación' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $evento->usuario?->name ?? 'Sistema' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="ui-result-count">Mostrando {{ $eventos->firstItem() }}–{{ $eventos->lastItem() }} de {{ $eventos->total() }} eventos</p>
            {{ $eventos->links() }}
        @endif
    </div>
</x-app-layout>
