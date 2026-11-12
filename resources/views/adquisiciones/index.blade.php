<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Gestión de adquisiciones</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-6">
        @include('partials.mensajes')

        <div class="ui-toolbar">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Solicitudes de compra</h3>
                <p class="ui-muted">Registra pedidos, proveedores, costos y recepción de ejemplares.</p>
            </div>
            <a class="ui-btn ui-btn-light" href="{{ route('proveedores.index') }}">
                <i class="fa-solid fa-truck-field" aria-hidden="true"></i> Administrar proveedores
            </a>
        </div>

        <section class="ui-card" aria-labelledby="nueva-solicitud">
            <h3 id="nueva-solicitud" class="font-semibold text-slate-900">Nueva solicitud de compra</h3>
            <p class="ui-muted">Si el título aún no está en el catálogo, <a href="{{ route('libros.create') }}" class="font-semibold text-green-800 underline">regístralo primero</a>; las copias se crean al marcar el pedido como recibido.</p>
            <form method="POST" action="{{ route('adquisiciones.store') }}">
                @csrf
                <div class="ui-form-grid">
                    <h4 class="col-span-full border-b border-slate-200 pb-2 text-sm font-bold uppercase tracking-wide text-green-900">Libro y proveedor</h4>
                    <div class="ui-field">
                        <label class="ui-label" for="libro_id">Libro del catálogo</label>
                        <select class="ui-select" id="libro_id" name="libro_id" required>
                            <option value="">Selecciona un título</option>
                            @foreach ($libros as $libro)
                                <option value="{{ $libro->id }}" @selected(old('libro_id') == $libro->id)>
                                    {{ $libro->titulo }}{{ $libro->isbn ? ' · ISBN '.$libro->isbn : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('libro_id')" class="mt-2" />
                    </div>
                    <div class="ui-field">
                        <label class="ui-label" for="proveedor_id">Proveedor</label>
                        <select class="ui-select" id="proveedor_id" name="proveedor_id" required>
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" @selected(old('proveedor_id') == $proveedor->id)>{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                    </div>
                    <div class="ui-field">
                        <label class="ui-label" for="cantidad">Cantidad de ejemplares</label>
                        <input class="ui-input" type="number" id="cantidad" name="cantidad" min="1" max="500" value="{{ old('cantidad', 1) }}" required>
                    </div>
                    <div class="ui-field">
                        <label class="ui-label" for="costo_unitario">Costo por ejemplar</label>
                        <input class="ui-input" type="number" id="costo_unitario" name="costo_unitario" min="0" max="99999999.99" step="0.01" value="{{ old('costo_unitario') }}" required>
                    </div>
                    <h4 class="col-span-full border-b border-slate-200 pb-2 text-sm font-bold uppercase tracking-wide text-green-900">Ingreso al inventario</h4>
                    <div class="ui-field">
                        <label class="ui-label" for="ubicacion_id">Ubicación al recibir</label>
                        <select class="ui-select" id="ubicacion_id" name="ubicacion_id">
                            <option value="">Sin ubicación asignada</option>
                            @foreach ($ubicaciones as $ubicacion)
                                <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_id') == $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ui-field">
                        <label class="ui-label" for="coleccion">Colección</label>
                        <select class="ui-select" id="coleccion" name="coleccion" required>
                            @foreach ($colecciones as $valor => $nombre)
                                <option value="{{ $valor }}" @selected(old('coleccion', 'general') === $valor)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ui-field" style="grid-column:1 / -1;">
                        <label class="ui-label" for="observaciones">Observaciones</label>
                        <textarea class="ui-input" id="observaciones" name="observaciones" rows="2" maxlength="2000">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <button class="ui-btn" type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Registrar solicitud</button>
            </form>
        </section>

        <section aria-labelledby="solicitudes-lista">
            <h3 id="solicitudes-lista" class="mb-3 text-lg font-semibold text-slate-900">Pedidos registrados</h3>
            @if ($solicitudes->isEmpty())
                <div class="ui-empty">Todavía no hay solicitudes de compra.</div>
            @else
                <p class="ui-result-count mb-3">Mostrando {{ $solicitudes->firstItem() }}–{{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }} solicitudes</p>
                <div class="space-y-3">
                    @foreach ($solicitudes as $solicitud)
                        <article class="ui-card">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-semibold text-slate-900">{{ $solicitud->libro->titulo }}</h4>
                                    <p class="ui-muted">Solicitud #{{ $solicitud->id }} · {{ $solicitud->proveedor->nombre }} · Registrada por {{ $solicitud->solicitante->name }}</p>
                                </div>
                                <span class="ui-badge {{ $solicitud->estado === 'recibida' ? 'ok' : ($solicitud->estado === 'cancelada' ? 'bad' : 'warn') }}">
                                    {{ $estados[$solicitud->estado] }}
                                </span>
                            </div>

                            <div class="ui-inline flex-wrap text-sm text-slate-600">
                                <span>{{ $solicitud->cantidad }} ejemplar(es)</span>
                                <span aria-hidden="true">·</span>
                                <span>${{ number_format((float) $solicitud->costo_unitario, 2, ',', '.') }} c/u</span>
                                <span aria-hidden="true">·</span>
                                <strong>Total: ${{ number_format($solicitud->costo_total, 2, ',', '.') }}</strong>
                                @if ($solicitud->ubicacion)
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $solicitud->ubicacion->nombre }}</span>
                                @endif
                            </div>
                            @if ($solicitud->observaciones)
                                <p class="ui-muted">{{ $solicitud->observaciones }}</p>
                            @endif

                            @if ($solicitud->estado === 'solicitada' || $solicitud->estado === 'aprobada')
                                <form method="POST" action="{{ route('adquisiciones.estado', $solicitud) }}" class="ui-inline flex-wrap">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="estado-{{ $solicitud->id }}">Cambiar estado de solicitud {{ $solicitud->id }}</label>
                                    <select class="ui-select" id="estado-{{ $solicitud->id }}" name="estado" style="max-width:220px;" required>
                                        @if ($solicitud->estado === 'solicitada')
                                            <option value="aprobada">Aprobar pedido</option>
                                        @else
                                            <option value="recibida">Marcar recibida y crear ejemplares</option>
                                        @endif
                                        <option value="cancelada">Cancelar pedido</option>
                                    </select>
                                    <button class="ui-btn ui-btn-sm" type="submit">Actualizar</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
                <div class="mt-5">{{ $solicitudes->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
