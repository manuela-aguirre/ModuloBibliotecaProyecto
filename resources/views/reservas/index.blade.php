<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Mis reservas</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        @if ($reservas->isEmpty() && $listaEspera->isEmpty())
            <div class="ui-empty">Aún no tienes reservas ni estás en listas de espera. <a href="{{ route('catalogo.index') }}" style="color:#14532d;font-weight:600;">Explora el catálogo</a>.</div>
        @elseif ($reservas->isNotEmpty())
            <div class="ui-desktop-records ui-table-scroll" role="region" aria-label="Mis reservas, desplazable horizontalmente" tabindex="0">
                <table class="ui-table min-w-[760px]">
                    <thead><tr><th>Libro</th><th>Ubicación</th><th>Día de retiro</th><th>Estado</th><th>Observación</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($reservas as $r)
                    @php $clase = ['activa' => 'ok', 'retirada' => 'ok', 'cancelada' => 'warn', 'caducada' => 'bad'][$r->estado] ?? ''; @endphp
                    <tr>
                        <td>{{ $r->ejemplar->libro->titulo }}</td>
                        <td>{{ $r->ejemplar->ubicacion?->nombre ?? '—' }}</td>
                        <td>{{ $r->fecha_retiro->format('d/m/Y') }}</td>
                        <td><span class="ui-badge {{ $clase }}">{{ ucfirst($r->estado) }}</span></td>
                        <td>{{ $r->observacion ?: '—' }}</td>
                        <td class="ui-inline">
                            @if ($r->estado === 'activa')
                                <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Cancelar esta reserva?');">
                                    @csrf @method('DELETE')
                                    <button class="ui-btn ui-btn-danger ui-btn-sm">Cancelar</button>
                                </form>
                            @endif
                            <details>
                                <summary class="ui-btn ui-btn-light ui-btn-sm cursor-pointer">Más acciones</summary>
                                <form method="POST" action="{{ route('reservas.eliminar', $r) }}" class="mt-2" onsubmit="return confirm('¿Eliminar esta reserva de tu historial?');">
                                    @csrf @method('DELETE')
                                    <button class="ui-btn ui-btn-danger ui-btn-sm">Eliminar del historial</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ui-mobile-records" aria-label="Mis reservas">
                @foreach ($reservas as $r)
                    @php $clase = ['activa' => 'ok', 'retirada' => 'ok', 'cancelada' => 'warn', 'caducada' => 'bad'][$r->estado] ?? ''; @endphp
                    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $r->ejemplar->libro->titulo }}</h3>
                            <span class="ui-badge {{ $clase }}">{{ ucfirst($r->estado) }}</span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Ubicación</dt><dd class="mt-1">{{ $r->ejemplar->ubicacion?->nombre ?? '—' }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Día de retiro</dt><dd class="mt-1">{{ $r->fecha_retiro->format('d/m/Y') }}</dd></div>
                        </dl>
                        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm">
                            <span class="font-semibold">Observación:</span> {{ $r->observacion ?: 'Sin observaciones.' }}
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($r->estado === 'activa')
                                <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Cancelar esta reserva?');">
                                    @csrf @method('DELETE')
                                    <button class="ui-btn ui-btn-danger ui-btn-sm">Cancelar</button>
                                </form>
                            @endif
                            <details>
                                <summary class="ui-btn ui-btn-light ui-btn-sm cursor-pointer">Más acciones</summary>
                                <form method="POST" action="{{ route('reservas.eliminar', $r) }}" class="mt-2" onsubmit="return confirm('¿Eliminar esta reserva de tu historial?');">
                                    @csrf @method('DELETE')
                                    <button class="ui-btn ui-btn-danger ui-btn-sm">Eliminar del historial</button>
                                </form>
                            </details>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <section class="mt-8" aria-labelledby="mis-listas-espera">
            <h3 id="mis-listas-espera" class="mb-3 text-lg font-semibold text-slate-900">Mis listas de espera</h3>
            @if ($listaEspera->isEmpty())
                <p class="ui-muted">No tienes títulos en lista de espera.</p>
            @else
                <div class="space-y-3">
                    @foreach ($listaEspera as $entrada)
                        <article class="ui-card">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <a class="font-semibold text-slate-900 underline" href="{{ route('catalogo.show', $entrada->libro) }}">{{ $entrada->libro->titulo }}</a>
                                <span class="ui-badge {{ $entrada->estado === 'reservada' || $entrada->estado === 'atendida' ? 'ok' : ($entrada->estado === 'espera' ? 'warn' : '') }}">
                                    {{ ['espera' => 'En espera', 'reservada' => 'Reserva asignada', 'atendida' => 'Atendida', 'cancelada' => 'Cancelada'][$entrada->estado] ?? ucfirst($entrada->estado) }}
                                </span>
                            </div>
                            @if ($entrada->estado === 'reservada' && $entrada->reserva)
                                <p class="ui-muted">Se separó un ejemplar para ti. Pasa por la biblioteca antes del {{ $entrada->reserva->fecha_retiro->format('d/m/Y') }}.</p>
                            @elseif ($entrada->estado === 'espera')
                                <p class="ui-muted">Te avisamos en esta página cuando se asigne un ejemplar.</p>
                                <form method="POST" action="{{ route('lista-espera.destroy', $entrada) }}" onsubmit="return confirm('¿Salir de la lista de espera?');">
                                    @csrf @method('DELETE')
                                    <button class="ui-btn ui-btn-danger ui-btn-sm" type="submit">Salir de la lista</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-app-layout>