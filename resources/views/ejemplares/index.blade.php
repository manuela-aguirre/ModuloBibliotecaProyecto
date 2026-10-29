<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Ejemplares: {{ $libro->titulo }}</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap">
        @include('partials.mensajes')

        <div class="ui-toolbar">
            <a class="ui-btn ui-btn-light" href="{{ route('libros.index') }}">← Libros</a>
            <a class="ui-btn" href="{{ route('libros.ejemplares.create', $libro) }}"><i class="fa-solid fa-plus"></i> Agregar ejemplar</a>
        </div>

        @if ($libro->ejemplares->isEmpty())
            <div class="ui-empty">Este libro aún no tiene ejemplares físicos registrados.</div>
        @else
            <table class="ui-table">
                <thead><tr><th>Código de barras</th><th>Signatura</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @foreach ($libro->ejemplares as $e)
                    @php $clase = ['disponible' => 'ok', 'reservado' => 'warn', 'prestado' => 'warn', 'danado' => 'bad', 'baja' => 'bad'][$e->estado] ?? ''; @endphp
                    <tr>
                        <td>{{ $e->codigo_barras }}</td>
                        <td>{{ $e->signatura ?? '—' }}</td>
                        <td>{{ $e->ubicacion?->nombre ?? '—' }}</td>
                        <td><span class="ui-badge {{ $clase }}">{{ ucfirst($e->estado) }}</span></td>
                        <td class="ui-inline">
                            <a class="ui-btn ui-btn-light ui-btn-sm" href="{{ route('ejemplares.edit', $e) }}">Editar</a>
                            <form method="POST" action="{{ route('ejemplares.destroy', $e) }}" onsubmit="return confirm('¿Eliminar este ejemplar?');">
                                @csrf @method('DELETE')
                                <button class="ui-btn ui-btn-danger ui-btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>