<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Préstamos</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <div class="ui-toolbar">
            <div class="ui-tabs" style="margin:0;">
                @foreach ([null => 'Todos', 'abiertos' => 'Abiertos', 'vencidos' => 'Vencidos', 'cerrados' => 'Cerrados'] as $valor => $texto)
                    <a class="ui-btn ui-btn-sm {{ $estado === $valor ? '' : 'ui-btn-light' }}" href="{{ route('prestamos.index', $valor ? ['estado' => $valor] : []) }}">{{ $texto }}</a>
                @endforeach
            </div>
            <a class="ui-btn" href="{{ route('prestamos.create') }}"><i class="fa-solid fa-plus"></i> Nuevo préstamo</a>
        </div>

        @if ($prestamos->isEmpty())
            <div class="ui-empty">No hay préstamos para mostrar.</div>
        @else
            <table class="ui-table">
                <thead><tr><th>Usuario</th><th>Material</th><th>Prestado</th><th>Límite</th><th>Devuelto</th><th>Multa</th><th>Acción</th></tr></thead>
                <tbody>
                @foreach ($prestamos as $p)
                    <tr>
                        <td>{{ $p->user->name }}<div class="ui-muted">{{ $p->user->numero_identificacion }}</div></td>
                        <td>
                            @foreach ($p->ejemplares as $e)
                                <div>{{ $e->libro->titulo }} <span class="ui-muted">({{ $e->codigo_barras }})</span></div>
                            @endforeach
                        </td>
                        <td>{{ $p->fecha_prestamo->format('d/m/Y') }}</td>
                        <td>
                            {{ $p->fecha_limite->format('d/m/Y') }}
                            @if ($p->estaVencido()) <span class="ui-badge bad">Vencido</span> @endif
                        </td>
                        <td>{{ $p->fecha_devolucion?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if ($p->multa > 0)
                                ${{ number_format($p->multa, 0, ',', '.') }}
                                @if ($p->multa_pagada)
                                    <span class="ui-badge ok">Pagada</span>
                                @else
                                    <form method="POST" action="{{ route('prestamos.multa-pagada', $p) }}" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button class="ui-btn ui-btn-light ui-btn-sm">Marcar pagada</button>
                                    </form>
                                @endif
                            @else — @endif
                        </td>
                        <td>
                            @if (! $p->fecha_devolucion)
                                <form method="POST" action="{{ route('prestamos.devolver', $p) }}" class="ui-inline">
                                    @csrf @method('PATCH')
                                    <select name="condicion" class="ui-select" style="padding:.25rem .5rem;">
                                        <option value="devuelto">En buen estado</option>
                                        <option value="danado">Dañado</option>
                                    </select>
                                    <button class="ui-btn ui-btn-sm">Devolver</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:1.25rem;">{{ $prestamos->links() }}</div>
        @endif
    </div>
</x-app-layout>