<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Mis préstamos</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @if ($prestamos->isEmpty())
            <div class="ui-empty">Todavía no tienes préstamos registrados.</div>
        @else
            <div class="ui-desktop-records ui-table-scroll" role="region" aria-label="Mis préstamos, desplazable horizontalmente" tabindex="0">
                <table class="ui-table min-w-[680px]">
                    <thead><tr><th>Libro</th><th>Prestado</th><th>Debes devolver</th><th>Devuelto</th><th>Multa</th></tr></thead>
                    <tbody>
                    @foreach ($prestamos as $p)
                    @php $multaActual = $p->multaActual(); @endphp
                    <tr>
                        <td>@foreach ($p->ejemplares as $e) <div>{{ $e->libro->titulo }}</div> @endforeach</td>
                        <td>{{ $p->fecha_prestamo->format('d/m/Y') }}</td>
                        <td>
                            {{ $p->fecha_limite->format('d/m/Y') }}
                            @if ($p->estaVencido()) <span class="ui-badge bad">Vencido</span> @endif
                        </td>
                        <td>{{ $p->fecha_devolucion?->format('d/m/Y') ?? 'Pendiente' }}</td>
                        <td>
                            @if ($multaActual === null && $p->estaVencido())
                                <span class="ui-badge warn" title="La biblioteca debe configurar la tarifa de multa.">En mora; multa por configurar</span>
                            @elseif ($multaActual > 0)
                                ${{ number_format($multaActual, 0, ',', '.') }}
                                <span class="ui-badge {{ $p->fecha_devolucion ? ($p->multa_pagada ? 'ok' : 'bad') : 'warn' }}">
                                    {{ $p->fecha_devolucion ? ($p->multa_pagada ? 'Pagada' : 'Pendiente') : 'Acumulándose' }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ui-mobile-records mt-3" aria-label="Mis préstamos">
                @foreach ($prestamos as $p)
                    @php $multaActual = $p->multaActual(); @endphp
                    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $p->ejemplares->map(fn ($copy) => $copy->libro->titulo)->join(', ') }}</h3>
                            @if ($p->estaVencido())<span class="ui-badge bad">Vencido</span>@endif
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Préstamo</dt><dd class="mt-1">{{ $p->fecha_prestamo->format('d/m/Y') }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Fecha límite</dt><dd class="mt-1">{{ $p->fecha_limite->format('d/m/Y') }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Devolución</dt><dd class="mt-1">{{ $p->fecha_devolucion?->format('d/m/Y') ?? 'Pendiente' }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Multa</dt>
                                <dd class="mt-1">
                                    @if ($multaActual === null && $p->estaVencido())
                                        <span class="ui-badge warn">Por configurar</span>
                                    @elseif ($multaActual > 0)
                                        ${{ number_format($multaActual, 0, ',', '.') }}
                                        <span class="ui-badge {{ $p->fecha_devolucion ? ($p->multa_pagada ? 'ok' : 'bad') : 'warn' }}">{{ $p->fecha_devolucion ? ($p->multa_pagada ? 'Pagada' : 'Pendiente') : 'Acumulándose' }}</span>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>