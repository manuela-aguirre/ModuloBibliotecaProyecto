<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Historial por número de identificación</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        <form method="GET" action="{{ route('usuarios.historial') }}" class="ui-inline" style="margin-bottom:1.25rem;">
            <input class="ui-input" style="max-width:280px" name="numero_identificacion"
                   value="{{ request('numero_identificacion') }}" placeholder="Número de identificación" required>
            <button class="ui-btn">Buscar</button>
        </form>

        @if ($buscado && ! $usuario)
            <div class="ui-empty">No se encontró ningún usuario con ese número de identificación.</div>
        @elseif ($usuario)
            <div class="ui-card" style="margin-bottom:1.25rem;">
                <strong>{{ $usuario->name }}</strong>
                <span class="ui-muted">
                    {{ $usuario->tipo_identificacion }} {{ $usuario->numero_identificacion }}
                    · {{ $usuario->email }}
                    @if ($usuario->telefono) · {{ $usuario->telefono }} @endif
                    @if ($usuario->carrera) · {{ $usuario->carrera }} @endif
                    @if ($usuario->semestre) · Semestre {{ $usuario->semestre }} @endif
                </span>
            </div>

            <h3 class="ui-label" style="font-size:1rem;">Préstamos</h3>
            @if ($usuario->prestamos->isEmpty())
                <div class="ui-empty">Sin préstamos registrados.</div>
            @else
                <table class="ui-table" style="margin-bottom:1.5rem;">
                    <thead><tr><th>Libro</th><th>Prestado</th><th>Fecha límite</th><th>Devuelto</th><th>Multa</th></tr></thead>
                    <tbody>
                    @foreach ($usuario->prestamos as $p)
                        <tr>
                            <td>@foreach ($p->ejemplares as $e) <div>{{ $e->libro->titulo }}</div> @endforeach</td>
                            <td>{{ $p->fecha_prestamo->format('d/m/Y') }}</td>
                            <td>
                                {{ $p->fecha_limite->format('d/m/Y') }}
                                @if ($p->estaVencido()) <span class="ui-badge bad">Vencido</span> @endif
                            </td>
                            <td>{{ $p->fecha_devolucion?->format('d/m/Y') ?? 'Pendiente' }}</td>
                            <td>
                                @if ($p->multa > 0)
                                    ${{ number_format($p->multa, 0, ',', '.') }}
                                    <span class="ui-badge {{ $p->multa_pagada ? 'ok' : 'bad' }}">{{ $p->multa_pagada ? 'Pagada' : 'Pendiente' }}</span>
                                @else — @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            <h3 class="ui-label" style="font-size:1rem;">Reservas</h3>
            @if ($usuario->reservas->isEmpty())
                <div class="ui-empty">Sin reservas registradas.</div>
            @else
                <table class="ui-table">
                    <thead><tr><th>Libro</th><th>Ejemplar</th><th>Retira el</th><th>Estado</th></tr></thead>
                    <tbody>
                    @foreach ($usuario->reservas as $r)
                        <tr>
                            <td>{{ $r->ejemplar->libro->titulo }}</td>
                            <td>{{ $r->ejemplar->codigo_barras }}</td>
                            <td>{{ $r->fecha_retiro->format('d/m/Y') }}</td>
                            <td><span class="ui-badge">{{ ucfirst($r->estado) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
</x-app-layout>
