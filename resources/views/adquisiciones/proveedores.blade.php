<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Proveedores</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-6">
        @include('partials.mensajes')

        <div class="ui-toolbar">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Directorio de proveedores</h3>
                <p class="ui-muted">Los proveedores se pueden reutilizar en distintas solicitudes de compra.</p>
            </div>
            <a class="ui-btn ui-btn-light" href="{{ route('adquisiciones.index') }}">Volver a adquisiciones</a>
        </div>

        <form class="ui-card" method="POST" action="{{ route('proveedores.store') }}">
            @csrf
            <h3 class="font-semibold text-slate-900">Agregar proveedor</h3>
            <div class="ui-form-grid">
                <div class="ui-field">
                    <label class="ui-label" for="nuevo-nombre">Nombre</label>
                    <input class="ui-input" id="nuevo-nombre" name="nombre" maxlength="150" value="{{ old('nombre') }}" required>
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="nuevo-contacto">Persona de contacto</label>
                    <input class="ui-input" id="nuevo-contacto" name="contacto" maxlength="120">
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="nuevo-correo">Correo</label>
                    <input class="ui-input" type="email" id="nuevo-correo" name="correo" maxlength="150">
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="nuevo-telefono">Teléfono</label>
                    <input class="ui-input" id="nuevo-telefono" name="telefono" maxlength="40">
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="nuevo-direccion">Dirección</label>
                    <input class="ui-input" id="nuevo-direccion" name="direccion" maxlength="255">
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="nuevo-activo">Estado</label>
                    <select class="ui-select" id="nuevo-activo" name="activo">
                        <option value="1" selected>Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <button class="ui-btn" type="submit">Guardar proveedor</button>
        </form>

        <section aria-labelledby="proveedores-registrados">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                <h3 id="proveedores-registrados" class="text-lg font-semibold text-slate-900">Proveedores registrados</h3>
                <form method="GET" action="{{ route('proveedores.index') }}" class="flex flex-wrap items-end gap-2">
                    <div class="ui-field mb-0">
                        <label class="ui-label" for="proveedor-busqueda">Buscar</label>
                        <input class="ui-input" id="proveedor-busqueda" type="search" name="q" value="{{ $search }}" placeholder="Nombre, contacto o correo">
                    </div>
                    <div class="ui-field mb-0">
                        <label class="ui-label" for="proveedor-estado">Estado</label>
                        <select class="ui-select" id="proveedor-estado" name="estado">
                            <option value="">Todos</option>
                            <option value="activo" @selected($estado === 'activo')>Activos</option>
                            <option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option>
                        </select>
                    </div>
                    <button class="ui-btn ui-btn-light" type="submit">Filtrar</button>
                    @if ($search || $estado)
                        <a class="text-sm font-semibold text-slate-600 underline" href="{{ route('proveedores.index') }}">Limpiar</a>
                    @endif
                </form>
            </div>
            @if ($proveedores->isNotEmpty())
                <p class="ui-result-count mb-3">Mostrando {{ $proveedores->firstItem() }}–{{ $proveedores->lastItem() }} de {{ $proveedores->total() }} proveedores</p>
            @endif
            <div class="space-y-4">
                @forelse ($proveedores as $proveedor)
                    <article class="ui-card">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="font-semibold text-slate-900">{{ $proveedor->nombre }}</h4>
                            <span class="ui-badge {{ $proveedor->activo ? 'ok' : 'bad' }}">{{ $proveedor->activo ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                        <p class="ui-muted">{{ $proveedor->solicitudes_compra_count }} solicitud(es) asociada(s)</p>
                        <details>
                            <summary class="cursor-pointer text-sm font-semibold text-green-800">Editar datos del proveedor</summary>
                        <form method="POST" action="{{ route('proveedores.update', $proveedor) }}">
                            @csrf
                            @method('PATCH')
                            <div class="ui-form-grid">
                                <div class="ui-field">
                                    <label class="ui-label" for="nombre-{{ $proveedor->id }}">Nombre</label>
                                    <input class="ui-input" id="nombre-{{ $proveedor->id }}" name="nombre" maxlength="150" value="{{ $proveedor->nombre }}" required>
                                </div>
                                <div class="ui-field">
                                    <label class="ui-label" for="contacto-{{ $proveedor->id }}">Contacto</label>
                                    <input class="ui-input" id="contacto-{{ $proveedor->id }}" name="contacto" maxlength="120" value="{{ $proveedor->contacto }}">
                                </div>
                                <div class="ui-field">
                                    <label class="ui-label" for="correo-{{ $proveedor->id }}">Correo</label>
                                    <input class="ui-input" type="email" id="correo-{{ $proveedor->id }}" name="correo" maxlength="150" value="{{ $proveedor->correo }}">
                                </div>
                                <div class="ui-field">
                                    <label class="ui-label" for="telefono-{{ $proveedor->id }}">Teléfono</label>
                                    <input class="ui-input" id="telefono-{{ $proveedor->id }}" name="telefono" maxlength="40" value="{{ $proveedor->telefono }}">
                                </div>
                                <div class="ui-field">
                                    <label class="ui-label" for="direccion-{{ $proveedor->id }}">Dirección</label>
                                    <input class="ui-input" id="direccion-{{ $proveedor->id }}" name="direccion" maxlength="255" value="{{ $proveedor->direccion }}">
                                </div>
                                <div class="ui-field">
                                    <label class="ui-label" for="activo-{{ $proveedor->id }}">Estado</label>
                                    <select class="ui-select" id="activo-{{ $proveedor->id }}" name="activo">
                                        <option value="1" @selected($proveedor->activo)>Activo</option>
                                        <option value="0" @selected(! $proveedor->activo)>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="ui-inline">
                                <button class="ui-btn ui-btn-sm" type="submit">Guardar cambios</button>
                            </div>
                        </form>
                        </details>
                        @if ($proveedor->solicitudes_compra_count === 0)
                            <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}" onsubmit="return confirm('¿Eliminar este proveedor?');">
                                @csrf
                                @method('DELETE')
                                <button class="ui-btn ui-btn-danger ui-btn-sm" type="submit">Eliminar</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="ui-empty">{{ $search || $estado ? 'No se encontraron proveedores con esos filtros.' : 'Todavía no hay proveedores registrados.' }}</div>
                @endforelse
            </div>
            <div class="mt-5">{{ $proveedores->links() }}</div>
        </section>
    </div>
</x-app-layout>
