<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Préstamos</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <div class="ui-toolbar">
            <div class="ui-tabs ui-filter-tabs" style="margin:0;">
                @foreach ([
                    'total' => ['estado' => null, 'texto' => 'Todos'],
                    'abiertos' => ['estado' => 'abiertos', 'texto' => 'Abiertos'],
                    'vencidos' => ['estado' => 'vencidos', 'texto' => 'Vencidos'],
                    'cerrados' => ['estado' => 'cerrados', 'texto' => 'Cerrados'],
                ] as $clave => $filtro)
                    <a class="ui-btn ui-btn-sm {{ $estado === $filtro['estado'] ? '' : 'ui-btn-light' }}" href="{{ route('prestamos.index', array_filter(['estado' => $filtro['estado'], 'q' => $search], fn ($value) => $value !== null && $value !== '')) }}">{{ $filtro['texto'] }} <span>({{ $counts[$clave] }})</span></a>
                @endforeach
            </div>
            <a class="ui-btn" href="{{ route('prestamos.create') }}"><i class="fa-solid fa-plus"></i> Nuevo préstamo</a>
        </div>

        <form method="GET" action="{{ route('prestamos.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
            @if ($estado)<input type="hidden" name="estado" value="{{ $estado }}">@endif
            <div class="ui-field mb-0 min-w-[min(100%,18rem)] flex-1">
                <label class="ui-label" for="prestamo-busqueda">Buscar préstamos</label>
                <input class="ui-input" id="prestamo-busqueda" type="search" name="q" value="{{ $search }}" placeholder="Usuario, identificación o título">
            </div>
            <button class="ui-btn ui-btn-light" type="submit">Buscar</button>
            @if ($search)
                <a class="text-sm font-semibold text-slate-600 underline" href="{{ route('prestamos.index', $estado ? ['estado' => $estado] : []) }}">Limpiar</a>
            @endif
        </form>

        @if ($prestamos->isEmpty())
            <div class="ui-empty">{{ $search ? 'No se encontraron préstamos con esa búsqueda.' : 'No hay préstamos para mostrar.' }}</div>
        @else
            <p class="ui-result-count mb-3">Mostrando {{ $prestamos->firstItem() }}–{{ $prestamos->lastItem() }} de {{ $prestamos->total() }} préstamos</p>
            <div class="ui-desktop-records ui-table-scroll" role="region" aria-label="Listado de préstamos, desplazable horizontalmente" tabindex="0">
                <table class="ui-table min-w-[980px]">
                    <thead><tr><th>Usuario</th><th>Material</th><th>Prestado</th><th>Límite</th><th>Devuelto</th><th>Multa</th><th>Acción</th></tr></thead>
                    <tbody>
                    @foreach ($prestamos as $p)
                    @php
                        $multaActual = $p->multaActual();
                        $alertaPrestamo = $p->estaVencido()
                            ? 'vencido'
                            : (! $p->fecha_devolucion && $p->fecha_limite->lte(today()->addDays(2)) ? 'proximo' : null);
                    @endphp
                    <tr class="{{ $alertaPrestamo === 'vencido' ? 'bg-red-50' : ($alertaPrestamo === 'proximo' ? 'bg-amber-50' : '') }}">
                        <td>{{ $p->user->name }}<div class="ui-muted">{{ $p->user->numero_identificacion }}</div></td>
                        <td>
                            @foreach ($p->ejemplares as $e)
                                <div>{{ $e->libro->titulo }} <span class="ui-muted">({{ $e->codigo_barras }})</span></div>
                            @endforeach
                        </td>
                        <td>{{ $p->fecha_prestamo->format('d/m/Y') }}</td>
                        <td>
                            {{ $p->fecha_limite->format('d/m/Y') }}
                            @if ($alertaPrestamo === 'vencido')
                                <span class="ui-badge bad">Vencido</span>
                            @elseif ($alertaPrestamo === 'proximo')
                                <span class="ui-badge warn">Vence pronto</span>
                            @endif
                        </td>
                        <td>{{ $p->fecha_devolucion?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if ($multaActual === null && $p->estaVencido())
                                <span class="ui-badge warn" title="Configure BIBLIOTECA_SMLMV en .env para calcular la multa.">Tarifa sin configurar</span>
                            @elseif ($multaActual > 0)
                                ${{ number_format($multaActual, 0, ',', '.') }}
                                @if (! $p->fecha_devolucion)
                                    <span class="ui-badge warn">Acumulándose</span>
                                @elseif ($p->multa_pagada)
                                    <span class="ui-badge ok">Pagada</span>
                                @else
                                    <form method="POST" action="{{ route('prestamos.multa-pagada', $p) }}" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button class="ui-btn ui-btn-light ui-btn-sm">Marcar pagada</button>
                                    </form>
                                    @if ($p->condonacion_porcentaje == 0 && $p->fecha_devolucion)
                                        <details style="margin-top:.4rem;">
                                            <summary class="ui-btn ui-btn-light ui-btn-sm" style="cursor:pointer;">Condonar</summary>
                                            <form method="POST" action="{{ route('prestamos.condonar', $p) }}" class="ui-inline" style="margin-top:.4rem;">
                                                @csrf @method('PATCH')
                                                <select name="motivo" class="ui-select" style="padding:.25rem .5rem;" required>
                                                    <optgroup label="Condonación del 100 %">
                                                        <option value="incapacidad">Incapacidad médica</option>
                                                        <option value="calamidad">Calamidad</option>
                                                        <option value="reposicion">Reposición del material</option>
                                                    </optgroup>
                                                    <optgroup label="Condonación del 50 %">
                                                        <option value="capacitacion">Capacitación de la Biblioteca</option>
                                                        <option value="material_apoyo">Material de apoyo</option>
                                                    </optgroup>
                                                </select>
                                                <button class="ui-btn ui-btn-sm">Aplicar</button>
                                            </form>
                                        </details>
                                    @else
                                        <span class="ui-badge warn" title="{{ $p->condonacion_motivo }}">Condonada {{ $p->condonacion_porcentaje }} %</span>
                                    @endif
                                @endif
                            @elseif ($p->multaActual() === 0)
                                —
                                @if ($p->condonacion_porcentaje > 0)
                                    <span class="ui-badge warn" title="{{ $p->condonacion_motivo }}">Condonada {{ $p->condonacion_porcentaje }} %</span>
                                @endif
                            @endif
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
            </div>
            <div class="ui-mobile-records" aria-label="Préstamos">
                @foreach ($prestamos as $p)
                    @php
                        $multaActual = $p->multaActual();
                        $alertaPrestamo = $p->estaVencido()
                            ? 'vencido'
                            : (! $p->fecha_devolucion && $p->fecha_limite->lte(today()->addDays(2)) ? 'proximo' : null);
                    @endphp
                    <article class="rounded-xl border p-4 shadow-sm {{ $alertaPrestamo === 'vencido' ? 'border-red-300 bg-red-50' : ($alertaPrestamo === 'proximo' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white') }}">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold text-slate-900">{{ $p->user->name }}</h3>
                                <p class="ui-muted">{{ $p->user->numero_identificacion }}</p>
                            </div>
                            @if ($alertaPrestamo === 'vencido')
                                <span class="ui-badge bad">Vencido</span>
                            @elseif ($alertaPrestamo === 'proximo')
                                <span class="ui-badge warn">Vence pronto</span>
                            @endif
                        </div>
                        <div class="mt-3 space-y-1 text-sm text-slate-700">
                            <p><span class="font-semibold">Material:</span> {{ $p->ejemplares->map(fn ($copy) => $copy->libro->titulo)->join(', ') }}</p>
                            <p><span class="font-semibold">Préstamo:</span> {{ $p->fecha_prestamo->format('d/m/Y') }} · <span class="font-semibold">Límite:</span> {{ $p->fecha_limite->format('d/m/Y') }}</p>
                            <p><span class="font-semibold">Devuelto:</span> {{ $p->fecha_devolucion?->format('d/m/Y') ?? 'Pendiente' }}</p>
                            <p><span class="font-semibold">Multa:</span>
                                @if ($multaActual === null && $p->estaVencido())
                                    <span class="ui-badge warn">Tarifa sin configurar</span>
                                @elseif ($multaActual > 0)
                                    ${{ number_format($multaActual, 0, ',', '.') }}
                                    <span class="ui-badge {{ $p->fecha_devolucion ? ($p->multa_pagada ? 'ok' : 'bad') : 'warn' }}">{{ $p->fecha_devolucion ? ($p->multa_pagada ? 'Pagada' : 'Pendiente') : 'Acumulándose' }}</span>
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if (! $p->fecha_devolucion)
                                <form method="POST" action="{{ route('prestamos.devolver', $p) }}" class="flex flex-wrap gap-2">
                                    @csrf @method('PATCH')
                                    <select aria-label="Condición de devolución" name="condicion" class="ui-select w-auto">
                                        <option value="devuelto">En buen estado</option>
                                        <option value="danado">Dañado</option>
                                    </select>
                                    <button class="ui-btn ui-btn-sm">Registrar devolución</button>
                                </form>
                            @elseif ($multaActual > 0 && ! $p->multa_pagada)
                                <details>
                                    <summary class="ui-btn ui-btn-light ui-btn-sm cursor-pointer">Gestionar multa</summary>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('prestamos.multa-pagada', $p) }}">
                                            @csrf @method('PATCH')
                                            <button class="ui-btn ui-btn-light ui-btn-sm">Marcar pagada</button>
                                        </form>
                                        @if ($p->condonacion_porcentaje == 0)
                                            <form method="POST" action="{{ route('prestamos.condonar', $p) }}" class="flex flex-wrap gap-2">
                                                @csrf @method('PATCH')
                                                <select name="motivo" class="ui-select w-auto" aria-label="Motivo de condonación" required>
                                                    <option value="">Condonar...</option>
                                                    <option value="incapacidad">Incapacidad médica (100 %)</option>
                                                    <option value="calamidad">Calamidad (100 %)</option>
                                                    <option value="reposicion">Reposición (100 %)</option>
                                                    <option value="capacitacion">Capacitación (50 %)</option>
                                                    <option value="material_apoyo">Material de apoyo (50 %)</option>
                                                </select>
                                                <button class="ui-btn ui-btn-sm">Aplicar</button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div style="margin-top:1.25rem;">{{ $prestamos->links() }}</div>
        @endif
    </div>
</x-app-layout>