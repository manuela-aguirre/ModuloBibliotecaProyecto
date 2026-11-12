<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Importación de catálogo</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-6">
        @include('partials.mensajes')

        <section class="ui-card" aria-labelledby="importar-catalogo-titulo">
            <h3 id="importar-catalogo-titulo" class="text-lg font-semibold text-slate-900">Importar inventario desde archivo</h3>
            <p class="ui-muted">Cada fila representa un ejemplar físico y contiene sus datos de inventario junto con los datos del libro.</p>
            <form method="POST" action="{{ route('importaciones.catalogo.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="ui-field">
                    <label class="ui-label" for="archivo">Archivo CSV, Excel (.xlsx) o PDF</label>
                    <input class="ui-input" type="file" id="archivo" name="archivo" accept=".csv,.xlsx,.pdf" required>
                    <p class="ui-muted">Tamaño máximo: 10 MB. Se procesan hasta 2.000 filas por carga.</p>
                    <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
                </div>
                <button class="ui-btn" type="submit"><i class="fa-solid fa-file-import" aria-hidden="true"></i> Importar inventario</button>
            </form>
        </section>

        @if (session('import_result'))
            @php($resultado = session('import_result'))
            <section class="ui-card" aria-labelledby="resultado-importacion" role="status">
                <h3 id="resultado-importacion" class="font-semibold text-slate-900">Resultado de la importación</h3>
                <p class="ui-muted">
                    {{ $resultado['creados'] }} ejemplar(es) creado(s), de los cuales {{ $resultado['libros_creados'] }} corresponden a títulos nuevos · {{ $resultado['duplicados'] }} duplicado(s) omitido(s) · {{ $resultado['invalidos'] }} fila(s) inválida(s).
                </p>
                @if ($resultado['issues'])
                    <ul class="list-inside list-disc space-y-1 text-sm text-slate-700">
                        @foreach ($resultado['issues'] as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                    @if ($resultado['issues_total'] > count($resultado['issues']))
                        <p class="ui-muted">Se muestran las primeras {{ count($resultado['issues']) }} incidencias de {{ $resultado['issues_total'] }}.</p>
                    @endif
                @endif
            </section>
        @endif

        <section class="ui-card" aria-labelledby="plantilla-importacion">
            <h3 id="plantilla-importacion" class="font-semibold text-slate-900">Plantillas y reglas</h3>
            <p class="ui-muted">Usa las 20 columnas de inventario en este orden; una fila equivale a una copia física:</p>
            <code class="block overflow-x-auto rounded-lg bg-slate-100 p-3 text-xs">"Codigo de barras",Signatura,Titulo,ISBN,Editorial,Coleccion,Edicion,Anio,Tipo,Ubicacion,Estado,"Fecha adquisicion",Factura,Valor,"Numero de entrada",Paginas,"Medio adquisicion","Recibido por","Fecha ingreso","Palabras clave"</code>
            <ul class="list-inside list-disc space-y-1 text-sm text-slate-700">
                <li>Código de barras, título, editorial, colección, tipo y estado son obligatorios. Los demás campos pueden quedar vacíos si no aplican.</li>
                <li>Se omite y reporta un ejemplar cuyo código de barras ya existe; si el libro ya está registrado por ISBN o por título y editorial, se agrega la nueva copia a ese libro.</li>
                <li>La colección debe ser General, Reserva u Otras colecciones; el estado debe ser disponible, dañado o baja. Los ejemplares prestados o reservados deben gestionarse desde el sistema para conservar sus relaciones.</li>
                <li>La ubicación debe existir en el sistema. Si el título es nuevo, se registra con el género «Sin clasificar» para completar el campo requerido por el catálogo.</li>
                <li>El PDF debe contener texto seleccionable y filas en líneas separadas con columnas delimitadas por <code>|</code>. No admite documentos escaneados.</li>
                <li>Si usas el CSV de inventario descargado desde Reportes, la importación detecta automáticamente los encabezados aunque el archivo incluya sus metadatos iniciales.</li>
            </ul>
            <div class="ui-inline flex-wrap">
                <a class="ui-btn ui-btn-light" href="{{ route('importaciones.catalogo.plantilla-csv') }}">Descargar plantilla CSV</a>
                <a class="ui-btn ui-btn-light" href="{{ route('importaciones.catalogo.plantilla-excel') }}">Descargar plantilla Excel</a>
                <a class="ui-btn ui-btn-light" href="{{ route('importaciones.catalogo.plantilla-pdf') }}">Descargar formato PDF</a>
            </div>
        </section>
    </div>
</x-app-layout>
