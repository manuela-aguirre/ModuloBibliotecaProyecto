<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Ubicaciones</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <form method="POST" action="{{ route('ubicaciones.store') }}" class="ui-toolbar" style="justify-content:flex-start;">
            @csrf
            <label class="sr-only" for="ubicacion-nombre">Nombre de la ubicación</label>
            <input class="ui-input" id="ubicacion-nombre" style="max-width:280px" name="nombre" placeholder="Ej. Estantería C - Nivel 1" required>
            <label class="sr-only" for="ubicacion-descripcion">Descripción de la ubicación</label>
            <input class="ui-input" id="ubicacion-descripcion" style="max-width:320px" name="descripcion" placeholder="Descripción (opcional)">
            <button class="ui-btn" type="submit">Agregar</button>
        </form>

        <form method="GET" action="{{ route('ubicaciones.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
            <div class="ui-field mb-0 min-w-[min(100%,18rem)] flex-1">
                <label class="ui-label" for="busqueda-ubicaciones">Buscar ubicación</label>
                <input class="ui-input" id="busqueda-ubicaciones" type="search" name="q" value="{{ $search }}" placeholder="Nombre o descripción">
            </div>
            <button class="ui-btn ui-btn-light" type="submit">Buscar</button>
            @if ($search)
                <a class="text-sm font-semibold text-slate-600 underline" href="{{ route('ubicaciones.index') }}">Limpiar</a>
            @endif
        </form>

        @if ($ubicaciones->isNotEmpty())
            <p class="ui-result-count mb-3">Mostrando {{ $ubicaciones->firstItem() }}–{{ $ubicaciones->lastItem() }} de {{ $ubicaciones->total() }} ubicaciones</p>
        @endif
        <div class="ui-table-scroll" role="region" aria-label="Ubicaciones, desplazable horizontalmente" tabindex="0">
            <table class="ui-table">
                <thead><tr><th>Nombre</th><th>Descripción</th><th>Ejemplares</th><th></th></tr></thead>
                <tbody>
                @forelse ($ubicaciones as $u)
                <tr>
                    <td>{{ $u->nombre }}</td>
                    <td>{{ $u->descripcion ?? '—' }}</td>
                    <td>{{ $u->ejemplares_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('ubicaciones.destroy', $u) }}" onsubmit="return confirm('¿Eliminar la ubicación?');">
                            @csrf @method('DELETE')
                            <button class="ui-btn ui-btn-danger ui-btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="ui-empty">{{ $search ? 'No se encontraron ubicaciones con esa búsqueda.' : 'No hay ubicaciones registradas.' }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $ubicaciones->links() }}</div>
    </div>
</x-app-layout>