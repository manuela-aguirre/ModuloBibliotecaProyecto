<x-app-layout>
    <x-slot name="header">
        <h2 class="cotec-header-title">Catálogo de Libros</h2>
    </x-slot>

    <style>
        .cotec-header-title { font-family: 'Figtree', Arial, sans-serif; font-weight: 600; font-size: 1.25rem; color: #14532d; }
        .libros-wrap { padding: 1.5rem 1rem; }
        .libros-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .libros-search input { border-radius: 0.75rem; border: 1px solid #dcfce7; padding: 0.55rem 0.9rem; min-width: 260px; }
        .libros-btn-new { background: #14532d; color: white; padding: 0.6rem 1.1rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .libros-btn-new:hover { background: #166534; }
        table.libros-table { width: 100%; border-collapse: collapse; background: white; border-radius: 1rem; overflow: hidden; }
        table.libros-table th { background: #f0fdf4; color: #14532d; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.06em; padding: 0.85rem 1rem; text-align: left; }
        table.libros-table td { padding: 0.85rem 1rem; border-top: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; }
        .libro-badge { background: #f0fdf4; color: #14532d; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
        .libro-stock-low { color: #b91c1c; font-weight: 700; }
        .libros-actions a, .libros-actions button { font-size: 0.78rem; font-weight: 600; margin-right: 0.75rem; text-decoration: none; }
        .libros-actions a.edit { color: #14532d; }
        .libros-actions button.delete { color: #b91c1c; background: none; border: none; cursor: pointer; padding: 0; }
        .status-banner { background: #dcfce7; color: #14532d; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 1rem; font-weight: 600; font-size: 0.88rem; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: #64748b; }
    </style>

    <div class="cotec-panel libros-wrap">
        @if (session('status'))
            <div class="status-banner">{{ session('status') }}</div>
        @endif

        <div class="libros-toolbar">
            <form method="GET" action="{{ route('libros.index') }}" class="libros-search">
                <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título o ISBN...">
            </form>

            <a href="{{ route('libros.create') }}" class="libros-btn-new">
                <i class="fa-solid fa-plus"></i> Nuevo libro
            </a>
        </div>

        @if ($libros->isEmpty())
            <div class="empty-state">No hay libros registrados todavía.</div>
        @else
            <table class="libros-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>ISBN</th>
                        <th>Editorial</th>
                        <th>Género</th>
                        <th>Disponibles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($libros as $libro)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#0f172a;">{{ $libro->titulo }}</div>
                                @if ($libro->descripcion)
                                    <div style="color:#94a3b8;font-size:0.78rem;">{{ Str::limit($libro->descripcion, 60) }}</div>
                                @endif
                            </td>
                            <td>{{ $libro->isbn ?? '—' }}</td>
                            <td><span class="libro-badge">{{ $libro->editorial?->nombre ?: 'Sin editorial' }}</span></td>
                            <td><span class="libro-badge">{{ $libro->genero?->nombre ?: 'Sin género' }}</span></td>
                            <td class="{{ $libro->stock <= 2 ? 'libro-stock-low' : '' }}">{{ $libro->stock }}</td>
                            <td class="libros-actions">
    <a class="edit" href="{{ route('libros.ejemplares.index', $libro) }}"><i class="fa-solid fa-barcode"></i> Ejemplares</a>
    <a class="edit" href="{{ route('libros.edit', $libro) }}"><i class="fa-solid fa-pen"></i> Editar</a>
    <form action="{{ route('libros.destroy', $libro) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este libro?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="delete"><i class="fa-solid fa-trash"></i> Eliminar</button>
    </form>
</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:1.25rem;">
                {{ $libros->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
