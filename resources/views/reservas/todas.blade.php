<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Reservas</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <div class="ui-tabs ui-filter-tabs">
            @foreach (['activa' => 'Activas', 'retirada' => 'Retiradas', 'caducada' => 'Caducadas', 'cancelada' => 'Canceladas', 'todas' => 'Todas'] as $valor => $texto)
                <a class="ui-btn ui-btn-sm {{ $estado === $valor ? '' : 'ui-btn-light' }}" href="{{ route('reservas.todas', array_filter(['estado' => $valor, 'q' => $search], fn ($value) => $value !== null && $value !== '')) }}">{{ $texto }} <span>({{ $counts[$valor] }})</span></a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('reservas.todas') }}" class="mb-4 flex flex-wrap items-end gap-3">
            <input type="hidden" name="estado" value="{{ $estado }}">
            <div class="ui-field mb-0 min-w-[min(100%,18rem)] flex-1">
                <label class="ui-label" for="reservas-busqueda">Buscar reservas</label>
                <input class="ui-input" id="reservas-busqueda" type="search" name="q" value="{{ $search }}" placeholder="Usuario, identificación, título o código">
            </div>
            <button class="ui-btn ui-btn-light" type="submit">Buscar</button>
            @if ($search)
                <a class="text-sm font-semibold text-slate-600 underline" href="{{ route('reservas.todas', ['estado' => $estado]) }}">Limpiar</a>
            @endif
        </form>

        @if ($reservas->isEmpty())
            <div class="ui-empty">{{ $search ? 'No se encontraron reservas con esa búsqueda.' : 'No hay reservas en este estado.' }}</div>
        @else
            <p class="ui-result-count mb-3">Mostrando {{ $reservas->firstItem() }}–{{ $reservas->lastItem() }} de {{ $reservas->total() }} reservas</p>
            <div class="ui-desktop-records ui-table-scroll" role="region" aria-label="Reservas, desplazable horizontalmente" tabindex="0">
                <table class="ui-table min-w-[1100px]">
                    <thead><tr><th>Usuario</th><th>Libro</th><th>Ejemplar</th><th>Retira el</th><th>Estado</th><th>Observación del bibliotecario</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($reservas as $r)
                    @php
                        $reservaVencida = $r->estado === 'activa' && $r->fecha_retiro->lt(today());
                        $claseEstadoReserva = match ($r->estado) {
                            'activa' => 'ok',
                            'caducada' => 'bad',
                            default => 'muted',
                        };
                    @endphp
                    <tr class="{{ $reservaVencida ? 'bg-red-50' : '' }}">
                        <td>{{ $r->user->name }}<div class="ui-muted">{{ $r->user->tipo_identificacion }} {{ $r->user->numero_identificacion }}</div>
                            <div class="ui-muted">{{ $r->user->carrera }}@if ($r->user->semestre) · Sem. {{ $r->user->semestre }}@endif @if ($r->user->telefono) · {{ $r->user->telefono }}@endif</div></td>
                        <td>{{ $r->ejemplar->libro->titulo }}</td>
                        <td>{{ $r->ejemplar->codigo_barras }}<div class="ui-muted">{{ $r->ejemplar->ubicacion?->nombre }}</div></td>
                        <td>
                            {{ $r->fecha_retiro->format('d/m/Y') }}
                            @if ($reservaVencida)
                                <span class="ui-badge bad">Vencida</span>
                            @endif
                        </td>
                        <td><span class="ui-badge {{ $claseEstadoReserva }}">{{ ucfirst($r->estado) }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('reservas.observacion', $r) }}" class="space-y-2">
                                @csrf @method('PATCH')
                                <textarea class="ui-input" name="observacion" rows="2" maxlength="2000" placeholder="Agregar información para el usuario">{{ old('observacion', $r->observacion) }}</textarea>
                                <button class="ui-btn ui-btn-sm">Guardar observación</button>
                                @if ($errors->has('observacion'))
                                    <p class="ui-muted">{{ $errors->first('observacion') }}</p>
                                @endif
                            </form>
                        </td>
                        <td>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($r->estado === 'activa')
                                    <form method="POST" action="{{ route('reservas.entregar', $r) }}">
                                        @csrf
                                        <button class="ui-btn ui-btn-sm">Entregar</button>
                                    </form>
                                @endif
                                <details>
                                    <summary class="ui-btn ui-btn-light ui-btn-sm cursor-pointer">Más acciones</summary>
                                    <div class="mt-2 flex flex-col gap-2">
                                        @if ($r->estado === 'activa')
                                            <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Cancelar la reserva?');">
                                                @csrf @method('DELETE')
                                                <button class="ui-btn ui-btn-danger ui-btn-sm">Cancelar reserva</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('reservas.eliminar', $r) }}" onsubmit="return confirm('¿Eliminar esta reserva?');">
                                            @csrf @method('DELETE')
                                            <button class="ui-btn ui-btn-danger ui-btn-sm">Eliminar registro</button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ui-mobile-records" aria-label="Reservas">
                @foreach ($reservas as $r)
                    @php
                        $reservaVencida = $r->estado === 'activa' && $r->fecha_retiro->lt(today());
                        $claseEstadoReserva = match ($r->estado) {
                            'activa' => 'ok',
                            'caducada' => 'bad',
                            default => 'muted',
                        };
                    @endphp
                    <article class="rounded-xl border p-4 shadow-sm {{ $reservaVencida ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white' }}">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $r->ejemplar->libro->titulo }}</h3>
                                <p class="ui-muted">{{ $r->user->name }} · {{ $r->user->tipo_identificacion }} {{ $r->user->numero_identificacion }}</p>
                            </div>
                            <span class="ui-badge {{ $claseEstadoReserva }}">{{ ucfirst($r->estado) }}</span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Ejemplar</dt><dd class="mt-1">{{ $r->ejemplar->codigo_barras }}</dd></div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-500">Retiro</dt>
                                <dd class="mt-1">
                                    {{ $r->fecha_retiro->format('d/m/Y') }}
                                    @if ($reservaVencida)
                                        <span class="ui-badge bad">Vencida</span>
                                    @endif
                                </dd>
                            </div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Ubicación</dt><dd class="mt-1">{{ $r->ejemplar->ubicacion?->nombre ?? '—' }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Contacto</dt><dd class="mt-1">{{ $r->user->telefono ?? '—' }}</dd></div>
                        </dl>
                        <form method="POST" action="{{ route('reservas.observacion', $r) }}" class="mt-4 space-y-2">
                            @csrf @method('PATCH')
                            <label class="ui-label" for="observacion-mobile-{{ $r->id }}">Observación del bibliotecario</label>
                            <textarea class="ui-input" id="observacion-mobile-{{ $r->id }}" name="observacion" rows="2" maxlength="2000" placeholder="Agregar información para el usuario">{{ $r->observacion }}</textarea>
                            @error('observacion')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                            <button class="ui-btn ui-btn-light ui-btn-sm">Guardar observación</button>
                        </form>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($r->estado === 'activa')
                                <form method="POST" action="{{ route('reservas.entregar', $r) }}">
                                    @csrf
                                    <button class="ui-btn ui-btn-sm">Entregar al usuario</button>
                                </form>
                            @endif
                            <details>
                                <summary class="ui-btn ui-btn-light ui-btn-sm cursor-pointer">Más acciones</summary>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($r->estado === 'activa')
                                        <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Cancelar la reserva?');">
                                            @csrf @method('DELETE')
                                            <button class="ui-btn ui-btn-danger ui-btn-sm">Cancelar</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('reservas.eliminar', $r) }}" onsubmit="return confirm('¿Eliminar esta reserva?');">
                                        @csrf @method('DELETE')
                                        <button class="ui-btn ui-btn-danger ui-btn-sm">Eliminar registro</button>
                                    </form>
                                </div>
                            </details>
                        </div>
                    </article>
                @endforeach
            </div>
            <div style="margin-top:1.25rem;">{{ $reservas->links() }}</div>
        @endif
    </div>
</x-app-layout>