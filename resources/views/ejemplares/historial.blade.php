<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Historial del ejemplar</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-5">
        <div class="ui-toolbar">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ $ejemplar->libro->titulo }}</h3>
                <p class="ui-muted">Código de barras: {{ $ejemplar->codigo_barras }} · Signatura: {{ $ejemplar->signatura ?: 'Sin signatura' }}</p>
            </div>
            <a class="ui-btn ui-btn-light" href="{{ route('libros.ejemplares.index', $ejemplar->libro) }}">Volver a ejemplares</a>
        </div>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumen actual del ejemplar">
            <div class="ui-card">
                <span class="ui-muted">Estado actual</span>
                <strong><span class="ui-badge {{ ['disponible' => 'ok', 'reservado' => 'warn', 'prestado' => 'warn', 'danado' => 'bad', 'baja' => 'bad'][$ejemplar->estado] ?? 'muted' }}">{{ ucfirst($ejemplar->estado) }}</span></strong>
            </div>
            <div class="ui-card">
                <span class="ui-muted">Ubicación actual</span>
                <strong>{{ $ejemplar->ubicacion?->nombre ?? 'Sin ubicación' }}</strong>
            </div>
            <div class="ui-card">
                <span class="ui-muted">Colección</span>
                <strong>{{ \App\Models\Ejemplar::COLECCIONES[$ejemplar->coleccion] ?? ucfirst($ejemplar->coleccion) }}</strong>
            </div>
            <div class="ui-card">
                <span class="ui-muted">Eventos registrados</span>
                <strong>{{ $eventos->total() }}</strong>
            </div>
        </section>

        @if ($eventos->isEmpty())
            <div class="ui-empty">Este ejemplar aún no tiene eventos en su historial.</div>
        @else
            <div class="ui-table-scroll" role="region" aria-label="Historial del ejemplar, desplazable horizontalmente" tabindex="0">
                <table class="ui-table min-w-[1050px]">
                    <thead>
                        <tr><th>Fecha y hora</th><th>Evento</th><th>Detalle y contexto</th><th>Cambio de estado</th><th>Ubicación</th><th>Referencia</th><th>Registrado por</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($eventos as $evento)
                        @php
                            $tipoEvento = [
                                'alta' => 'Alta de ejemplar',
                                'prestamo' => 'Préstamo',
                                'devolucion' => 'Devolución',
                                'reparacion' => 'Reparación',
                                'perdida' => 'Pérdida',
                                'cambio_estado' => 'Cambio de estado',
                                'cambio_ubicacion' => 'Cambio de ubicación',
                                'actualizacion_datos' => 'Actualización de datos',
                                'reserva' => 'Reserva',
                                'cancelacion_reserva' => 'Liberación de reserva',
                                'eliminacion' => 'Eliminación',
                            ][$evento->tipo] ?? ucfirst(str_replace('_', ' ', $evento->tipo));
                            $claseEvento = match ($evento->tipo) {
                                'alta', 'devolucion', 'reparacion' => 'ok',
                                'perdida', 'eliminacion' => 'bad',
                                'reserva', 'prestamo', 'cancelacion_reserva' => 'warn',
                                default => 'muted',
                            };
                            $referencia = str_contains($evento->referencia_type ?? '', 'Prestamo')
                                ? 'Préstamo'
                                : (str_contains($evento->referencia_type ?? '', 'Reserva') ? 'Reserva' : null);
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">
                                <strong>{{ $evento->ocurrido_at->format('d/m/Y') }}</strong>
                                <div class="ui-muted">{{ $evento->ocurrido_at->format('H:i') }}</div>
                            </td>
                            <td><span class="ui-badge {{ $claseEvento }}">{{ $tipoEvento }}</span></td>
                            <td class="min-w-[320px] whitespace-pre-line">{{ $evento->descripcion ?: '—' }}</td>
                            <td>
                                @if ($evento->estado_anterior || $evento->estado_nuevo)
                                    <span class="ui-badge muted">{{ $evento->estado_anterior ? ucfirst($evento->estado_anterior) : 'Nuevo' }}</span>
                                    <span aria-hidden="true">→</span>
                                    <span class="ui-badge {{ $evento->estado_nuevo === 'disponible' ? 'ok' : ($evento->estado_nuevo ? 'warn' : 'muted') }}">{{ $evento->estado_nuevo ? ucfirst($evento->estado_nuevo) : 'Retirado' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($evento->ubicacion_anterior_nombre || $evento->ubicacion_nueva_nombre || $evento->ubicacionAnterior || $evento->ubicacionNueva)
                                    {{ $evento->ubicacion_anterior_nombre ?? $evento->ubicacionAnterior?->nombre ?? 'Sin ubicación' }}
                                    → {{ $evento->ubicacion_nueva_nombre ?? $evento->ubicacionNueva?->nombre ?? 'Sin ubicación' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $referencia ? $referencia.' #'.$evento->referencia_id : '—' }}</td>
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
