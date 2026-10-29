<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Mis reservas</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        @if ($reservas->isEmpty())
            <div class="ui-empty">Aún no tienes reservas. <a href="{{ route('catalogo.index') }}" style="color:#14532d;font-weight:600;">Explora el catálogo</a>.</div>
        @else
            <table class="ui-table">
                <thead><tr><th>Libro</th><th>Ubicación</th><th>Día de retiro</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @foreach ($reservas as $r)
                    @php $clase = ['activa' => 'ok', 'retirada' => 'ok', 'cancelada' => 'warn', 'caducada' => 'bad'][$r->estado] ?? ''; @endphp
                    <tr>
                        <td>{{ $r->ejemplar->libro->titulo }}</td>
                        <td>{{ $r->ejemplar->ubicacion?->nombre ?? '—' }}</td>
                        <td>{{ $r->fecha_retiro->format('d/m/Y') }}</td>
                        <td><span class="ui-badge {{ $clase }}">{{ ucfirst($r->estado) }}</span></td>
                        <td>
                            @if ($r->estado === 'activa')
                                <form method="POST" action="{{ route('reservas.destroy', $r) }}" onsubmit="return confirm('¿Cancelar esta reserva?');">
                                    @csrf @method('DELETE')
                                    <button class="ui-btn ui-btn-danger ui-btn-sm">Cancelar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>