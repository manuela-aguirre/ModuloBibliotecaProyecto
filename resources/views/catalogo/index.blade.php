<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Consultar disponibilidad</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <form method="GET" action="{{ route('catalogo.index') }}" class="ui-toolbar">
            <input class="ui-input" style="max-width:320px" type="text" name="q" value="{{ $q }}" placeholder="Título, autor, ISBN, código de barras, signatura o palabra clave...">
            <select class="ui-select" style="max-width:280px" name="genero_id">
                <option value="">Todas las carreras / géneros</option>
                @foreach ($generos as $g)
                    <option value="{{ $g->id }}" @selected(request('genero_id') == $g->id)>{{ $g->nombre }}</option>
                @endforeach
            </select>
            <label class="ui-inline">
                <input type="checkbox" name="solo_disponibles" value="1" @checked(request()->boolean('solo_disponibles'))>
                Solo disponibles
            </label>
            <button class="ui-btn">Buscar</button>
        </form>

        @if ($libros->isEmpty())
            <div class="ui-empty">No se encontraron libros con esos filtros.</div>
        @else
            <div class="ui-grid">
                @foreach ($libros as $libro)
                    <div class="ui-card">
                        <div style="font-weight:600;color:#0f172a;">{{ $libro->titulo }}</div>
                        <div class="ui-muted">{{ $libro->autores->pluck('nombre')->join(', ') ?: 'Autor no registrado' }}</div>
                        <div class="ui-inline">
                            <span class="ui-badge">{{ $libro->genero->nombre }}</span>
                            @if ($libro->disponibles_count > 0)
                                <span class="ui-badge ok">{{ $libro->disponibles_count }} disponible(s)</span>
                            @else
                                <span class="ui-badge bad">No disponible</span>
                            @endif
                        </div>
                        <div><a class="ui-btn ui-btn-sm" href="{{ route('catalogo.show', $libro) }}">Ver detalle</a></div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:1.25rem;">{{ $libros->links() }}</div>
        @endif
    </div>
</x-app-layout>