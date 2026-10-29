<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Reservas</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <div class="ui-tabs">
            @foreach (['activa' => 'Activas', 'retirada' => 'Retiradas', 'caducada' => 'Caducadas', 'cancelada' => 'Canceladas', 'todas' => 'Todas'] as $valor => $texto)
                <a class="ui-btn ui-btn-sm {{ $estado === $valor ? '' : 'ui-btn-light' }}" href="{{ route('reservas.todas', ['estado' => $valor]) }}">{{ $texto }}</a>
            @endforeach
        </div>

        @if ($reservas->isEmpty())
            <div class="ui-empty">No hay reservas en este estado.</div>
        @else
            <table class="ui-table">
                <thead><tr><th>Usuario</th><th>Libro</th><th>Ejemplar</th><th>Retira el</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @foreach ($reservas as $r)
                    <tr>
                        <td>{{ $r->user->name }}<div class="ui-muted">{{ $r->user->tipo_identificacion }} {{ $r->user->numero_identificacion }}</div>
                            <div class="ui-muted">{{ $r->user->carrera }}@if ($r->user->semestre) · Sem. {{ $r->user->semestre }}@endif @if ($r->user->telefono) · {{ $r->user->telefono }}@endif</div></td>
                        <td>{{ $r->ejemplar->libro->titulo }}</td>
                        <td>{{ $r->ejemplar->codigo_barras }}<div class="ui-muted">{{ $r->ejemplar->ubicacion?->nombre }}</div></td>
                        <td>
                            {{ $r->fecha_retiro->format('d/m/Y') }}
                            @if ($r->estado === 'activa' && $r->fecha_retiro->lt(today()))
                                <span class="ui-badge bad">Vencida</span>
                            @endif
                        </td>
                        <td><span class="ui-badge">{{ ucfirst($r->estado) }}</span></td>
                        <td>
                            @if ($r->estado === 'activa')
                                <div class="ui-inline">
                                    <form method="POST" action="{{ route('reservas.entregar', $r) }}">
                                        @csrf
                                        <button class="ui-btn ui-btn-sm">Entregar</button>
                                    </form>
                                    <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Cancelar la reserva?');">
                                        @csrf @method('DELETE')
                                        <button class="ui-btn ui-btn-danger ui-btn-sm">Cancelar</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:1.25rem;">{{ $reservas->links() }}</div>
        @endif
    </div>
</x-app-layout>