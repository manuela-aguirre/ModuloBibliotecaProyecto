<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Ubicaciones</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <form method="POST" action="{{ route('ubicaciones.store') }}" class="ui-toolbar" style="justify-content:flex-start;">
            @csrf
            <input class="ui-input" style="max-width:280px" name="nombre" placeholder="Ej. Estantería C - Nivel 1" required>
            <input class="ui-input" style="max-width:320px" name="descripcion" placeholder="Descripción (opcional)">
            <button class="ui-btn">Agregar</button>
        </form>

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
                <tr><td colspan="4" class="ui-empty">No hay ubicaciones registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>