<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Mis préstamos</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @if ($prestamos->isEmpty())
            <div class="ui-empty">Todavía no tienes préstamos registrados.</div>
        @else
            <table class="ui-table">
                <thead><tr><th>Libro</th><th>Prestado</th><th>Debes devolver</th><th>Devuelto</th><th>Multa</th></tr></thead>
                <tbody>
                @foreach ($prestamos as $p)
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
    </div>
</x-app-layout>