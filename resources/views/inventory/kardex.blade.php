<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Kardex de inventario</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-5">
        @include('partials.mensajes')

        <div class="flex flex-wrap gap-3">
            <a class="ui-btn" href="{{ route('inventory.adjust') }}">Ajustar ejemplar</a>
        </div>

        <form method="GET" action="{{ route('inventory.kardex') }}" class="grid gap-3 md:grid-cols-5">
            <div class="ui-field">
                <label class="ui-label" for="libro_id">Libro</label>
                <select class="ui-select" id="libro_id" name="libro_id" required>
                    <option value="">Seleccione un libro</option>
                    @foreach ($libros as $opcion)
                        <option value="{{ $opcion->id }}" @selected(request('libro_id') == $opcion->id)>{{ $opcion->titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ui-field">
                <label class="ui-label" for="concepto">Concepto</label>
                <select class="ui-select" id="concepto" name="concepto">
                    <option value="">Todos</option>
                    @foreach ($conceptos as $key => $label)
                        <option value="{{ $key }}" @selected(request('concepto') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ui-field">
                <label class="ui-label" for="desde">Desde</label>
                <input class="ui-input" id="desde" type="date" name="desde" value="{{ request('desde') }}">
            </div>
            <div class="ui-field">
                <label class="ui-label" for="hasta">Hasta</label>
                <input class="ui-input" id="hasta" type="date" name="hasta" value="{{ request('hasta') }}">
            </div>
            <div class="flex items-end"><button class="ui-btn">Consultar</button></div>
        </form>

        @if ($libro)
            <section class="space-y-3">
                <div>
                    <h3 class="text-lg font-semibold">{{ $libro->titulo }}</h3>
                    <p class="text-sm text-slate-600">Stock disponible: {{ $libro->stock }} · Último saldo del kardex: {{ $saldoKardex }}</p>
                    @if ((int) $saldoKardex === (int) $libro->stock)
                        <p class="text-sm text-green-700">El saldo del kardex coincide con los ejemplares disponibles.</p>
                    @else
                        <p class="text-sm font-semibold text-red-700">El saldo del kardex no coincide con los ejemplares disponibles. Inicialice o revise los movimientos.</p>
                    @endif
                </div>

                <div class="ui-table-scroll" role="region" aria-label="Movimientos de inventario, desplazable horizontalmente" tabindex="0">
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr><th>Fecha</th><th>Concepto</th><th>Ejemplar</th><th>Documento</th><th>Entrada</th><th>Salida</th><th>Saldo</th><th>Usuario</th><th>Motivo</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($movimientos as $movimiento)
                                <tr>
                                    <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $movimiento->conceptLabel() }}</td>
                                    <td>{{ $movimiento->ejemplar_codigo ?? '—' }}</td>
                                    <td>{{ $movimiento->reference?->id ? class_basename($movimiento->reference).' #'.$movimiento->reference->id : '—' }}</td>
                                    <td>{{ $movimiento->type === 'entrada' ? $movimiento->quantity : '—' }}</td>
                                    <td>{{ $movimiento->type === 'salida' ? $movimiento->quantity : '—' }}</td>
                                    <td>{{ $movimiento->balance }}</td>
                                    <td>{{ $movimiento->user?->name ?? 'Sistema' }}</td>
                                    <td>{{ $movimiento->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9">No hay movimientos registrados para este libro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="ui-result-count">Mostrando {{ $movimientos->firstItem() ?? 0 }}–{{ $movimientos->lastItem() ?? 0 }} de {{ $movimientos->total() }} movimientos</p>
                {{ $movimientos->links() }}
            </section>
        @else
            <p class="text-sm text-slate-600">Seleccione un libro para consultar sus movimientos.</p>
        @endif
    </div>
</x-app-layout>
