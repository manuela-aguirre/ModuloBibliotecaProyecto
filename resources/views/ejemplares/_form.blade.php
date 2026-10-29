@php $e = $ejemplar ?? null; @endphp

<div class="ui-form-grid">
    <div class="ui-field">
        <label class="ui-label" for="codigo_barras">Código de barras</label>
        <input class="ui-input" id="codigo_barras" name="codigo_barras" value="{{ old('codigo_barras', $e?->codigo_barras) }}" required>
    </div>
    <div class="ui-field">
        <label class="ui-label" for="signatura">Signatura</label>
        <input class="ui-input" id="signatura" name="signatura" value="{{ old('signatura', $e?->signatura) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="ubicacion_id">Ubicación</label>
        <select class="ui-select" id="ubicacion_id" name="ubicacion_id">
            <option value="">Sin ubicación</option>
            @foreach ($ubicaciones as $u)
                <option value="{{ $u->id }}" @selected(old('ubicacion_id', $e?->ubicacion_id) == $u->id)>{{ $u->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="ui-field">
        <label class="ui-label" for="tipo_material">Tipo de material</label>
        <input class="ui-input" id="tipo_material" name="tipo_material" value="{{ old('tipo_material', $e?->tipo_material ?? 'Libro') }}" required>
    </div>
    <div class="ui-field">
        <label class="ui-label" for="edicion">Edición</label>
        <input class="ui-input" id="edicion" name="edicion" value="{{ old('edicion', $e?->edicion) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="anio_edicion">Año de edición</label>
        <input class="ui-input" type="number" id="anio_edicion" name="anio_edicion" value="{{ old('anio_edicion', $e?->anio_edicion) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="numero_factura">Número de factura</label>
        <input class="ui-input" id="numero_factura" name="numero_factura" value="{{ old('numero_factura', $e?->numero_factura) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="valor">Valor</label>
        <input class="ui-input" type="number" step="0.01" id="valor" name="valor" value="{{ old('valor', $e?->valor) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="fecha_adquisicion">Fecha de adquisición</label>
        <input class="ui-input" type="date" id="fecha_adquisicion" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', $e?->fecha_adquisicion?->toDateString()) }}">
    </div>

    <div class="ui-field">
        <label class="ui-label" for="numero_entrada">Número de entrada</label>
        <input class="ui-input"  id="numero_entrada" name="numero_entrada" value="{{ old('numero_entrada', $e?->numero_entrada) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="paginas">Páginas</label>
        <input class="ui-input" type="number" min="1" id="paginas" name="paginas" value="{{ old('paginas', $e?->paginas) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="medio_adquisicion">Medio de adquisición</label>
        <select class="ui-select" id="medio_adquisicion" name="medio_adquisicion">
            <option value="">Sin especificar</option>
            @foreach (['compra' => 'Compra', 'donacion' => 'Donación', 'canje' => 'Canje'] as $k => $t)
                <option value="{{ $k }}" @selected(old('medio_adquisicion', $e?->medio_adquisicion) === $k)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="ui-field">
        <label class="ui-label" for="recibido_por">Recibido por</label>
        <input class="ui-input"  id="recibido_por" name="recibido_por" value="{{ old('recibido_por', $e?->recibido_por) }}">
    </div>
    <div class="ui-field">
        <label class="ui-label" for="fecha_ingreso">Fecha de ingreso</label>
        <input class="ui-input" type="date" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', $e?->fecha_ingreso?->toDateString()) }}">
    </div>

    @if ($e && ! in_array($e->estado, ['prestado', 'reservado']))
        <div class="ui-field">
            <label class="ui-label" for="estado">Estado</label>
            <select class="ui-select" id="estado" name="estado">
                @foreach (['disponible' => 'Disponible', 'danado' => 'Dañado', 'baja' => 'Dado de baja'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected(old('estado', $e->estado) === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>

<div class="ui-inline" style="margin-top:1rem;">
    <button class="ui-btn">Guardar ejemplar</button>
    <a class="ui-btn ui-btn-light" href="{{ route('libros.ejemplares.index', $libro) }}">Cancelar</a>
</div>