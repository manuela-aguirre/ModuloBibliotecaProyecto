<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Catálogo de biblioteca</h2></x-slot>
    @include('partials.ui')

    <style>
        .catalogo-page { display: grid; gap: 1.5rem; }
        .catalogo-heading { display: grid; gap: 0.35rem; }
        .catalogo-heading h3,
        .catalogo-results-heading h3 { margin: 0; color: #0f172a; font-size: 1.1rem; font-weight: 700; }
        .catalogo-heading p,
        .catalogo-results-heading p { margin: 0; }
        .catalogo-search-panel { padding: 1.25rem; border: 1px solid #e2e8f0; border-radius: 1rem; background: #f8fafc; }
        .catalogo-search-main { margin-bottom: 1rem; }
        .catalogo-search-main .ui-field { margin-bottom: 0; }
        .catalogo-filter-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem; }
        .catalogo-filter-grid .ui-field { margin-bottom: 0; min-width: 0; }
        .catalogo-actions { display: flex; flex-wrap: wrap; gap: 0.65rem; margin-top: 1rem; }
        .catalogo-results { display: grid; gap: 1rem; }
        .catalogo-results-heading { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.5rem 1rem; }
        .catalogo-count { padding: 0.35rem 0.7rem; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: 0.82rem; font-weight: 600; }
        .catalogo-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; align-items: stretch; }
        .catalogo-book { display: flex; min-width: 0; flex-direction: column; gap: 0.75rem; padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035); transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease; }
        .catalogo-book:hover { transform: translateY(-2px); border-color: #bbf7d0; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08); }
        .catalogo-book:focus-within { outline: 3px solid rgba(21, 128, 61, 0.2); outline-offset: 2px; }
        .catalogo-book .ui-book-cover { margin-bottom: 0.15rem; }
        .catalogo-book-title { margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.4; overflow-wrap: anywhere; }
        .catalogo-book-title a { color: #0f172a; text-decoration: none; }
        .catalogo-book-title a:hover { color: #14532d; text-decoration: underline; text-underline-offset: 3px; }
        .catalogo-book-author { margin: 0; color: #64748b; font-size: 0.86rem; line-height: 1.5; overflow-wrap: anywhere; }
        .catalogo-book-meta { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.4rem; }
        .catalogo-book-meta .ui-badge { max-width: 100%; line-height: 1.45; white-space: normal; overflow-wrap: anywhere; }
        .catalogo-book-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: auto; padding-top: 0.25rem; }
        .catalogo-book-actions .ui-btn { justify-content: center; }
        .catalogo-empty { padding: 2rem 1.25rem; border: 1px dashed #cbd5e1; border-radius: 1rem; background: #f8fafc; text-align: center; color: #475569; }
        .catalogo-empty p { margin: 0 0 0.75rem; }
        .catalogo-pagination { overflow-x: auto; }
        .catalogo-page .ui-input:focus,
        .catalogo-page .ui-select:focus { border-color: #15803d; outline: 3px solid rgba(21, 128, 61, 0.15); outline-offset: 1px; }

        @media (max-width: 1100px) {
            .catalogo-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .catalogo-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .catalogo-search-panel { padding: 1rem; }
            .catalogo-grid,
            .catalogo-filter-grid { grid-template-columns: 1fr; }
            .catalogo-book { padding: 1rem; }
            .catalogo-actions .ui-btn { flex: 1 1 auto; justify-content: center; }
        }

        @media (prefers-reduced-motion: reduce) {
            .catalogo-book { transition: none; }
            .catalogo-book:hover { transform: none; }
        }
    </style>

    <div class="cotec-panel ui-wrap catalogo-page">
        @include('partials.mensajes')

        <section class="catalogo-search-panel" aria-labelledby="catalogo-search-title">
            <div class="catalogo-heading">
                <h3 id="catalogo-search-title">Encuentra un libro</h3>
                <p class="ui-muted">Busca por información bibliográfica o combina los filtros para precisar los resultados.</p>
            </div>

            <form method="GET" action="{{ route('catalogo.index') }}" aria-label="Buscar y filtrar el catálogo">
                <div class="catalogo-search-main ui-field">
                    <label class="ui-label" for="q">Búsqueda general</label>
                    <input
                        class="ui-input"
                        id="q"
                        name="q"
                        type="search"
                        maxlength="150"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Título, autor, ISBN, código o signatura"
                        aria-describedby="ayuda-busqueda"
                    >
                    <p id="ayuda-busqueda" class="ui-muted">Puedes escribir varios términos; se buscarán en los datos bibliográficos y de los ejemplares.</p>
                </div>

                <div class="catalogo-filter-grid">
                    <div class="ui-field">
                        <label class="ui-label" for="tema">Tema o palabra clave</label>
                        <input class="ui-input" id="tema" name="tema" type="search" maxlength="100"
                               value="{{ $filters['tema'] ?? '' }}" placeholder="Ej.: bases de datos">
                    </div>

                    <div class="ui-field">
                        <label class="ui-label" for="autor_id">Autor</label>
                        <select class="ui-select" id="autor_id" name="autor_id">
                            <option value="">Todos los autores</option>
                            @foreach ($autores as $autor)
                                <option value="{{ $autor->id }}" @selected(($filters['autor_id'] ?? '') == $autor->id)>
                                    {{ $autor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ui-field">
                        <label class="ui-label" for="genero_id">Área o género</label>
                        <select class="ui-select" id="genero_id" name="genero_id">
                            <option value="">Todas las áreas</option>
                            @foreach ($generos as $genero)
                                <option value="{{ $genero->id }}" @selected(($filters['genero_id'] ?? '') == $genero->id)>
                                    {{ $genero->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ui-field">
                        <label class="ui-label" for="disponibilidad">Disponibilidad</label>
                        <select class="ui-select" id="disponibilidad" name="disponibilidad">
                            <option value="">Todos los libros</option>
                            <option value="disponibles" @selected(($filters['disponibilidad'] ?? '') === 'disponibles')>Con ejemplares disponibles</option>
                            <option value="no_disponibles" @selected(($filters['disponibilidad'] ?? '') === 'no_disponibles')>Sin ejemplares disponibles</option>
                        </select>
                    </div>

                    <div class="ui-field">
                        <label class="ui-label" for="orden">Ordenar por</label>
                        <select class="ui-select" id="orden" name="orden">
                            <option value="titulo" @selected(($filters['orden'] ?? 'titulo') === 'titulo')>Título</option>
                            <option value="autor" @selected(($filters['orden'] ?? '') === 'autor')>Autor</option>
                            <option value="disponibilidad" @selected(($filters['orden'] ?? '') === 'disponibilidad')>Disponibilidad</option>
                        </select>
                    </div>
                </div>

                <div class="catalogo-actions">
                    <button class="ui-btn" type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Buscar</button>
                    <a class="ui-btn ui-btn-light" href="{{ route('catalogo.index') }}">Limpiar filtros</a>
                </div>
            </form>
        </section>

        <section class="catalogo-results" aria-labelledby="resultados-titulo">
            <div class="catalogo-results-heading">
                <h3 id="resultados-titulo">Resultados del catálogo</h3>
                <div class="flex flex-wrap items-center gap-2">
                    @if (collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
                        <span class="catalogo-count">Filtros aplicados</span>
                    @endif
                    <p class="catalogo-count" aria-live="polite">
                        {{ $libros->total() }} {{ $libros->total() === 1 ? 'título encontrado' : 'títulos encontrados' }}
                    </p>
                </div>
            </div>

            @if ($libros->isEmpty())
                <div class="catalogo-empty" role="status">
                    <p>No se encontraron títulos con esos criterios.</p>
                    <a href="{{ route('catalogo.index') }}" class="ui-btn ui-btn-light">Quitar filtros y ver todo el catálogo</a>
                </div>
            @else
                <div class="catalogo-grid">
                    @foreach ($libros as $libro)
                        <article class="catalogo-book" aria-labelledby="libro-{{ $libro->id }}">
                            @include('catalogo._portada', ['clase' => 'ui-book-cover--card'])
                            <h4 id="libro-{{ $libro->id }}" class="catalogo-book-title">
                                <a href="{{ route('catalogo.show', $libro) }}">{{ $libro->titulo }}</a>
                            </h4>

                            <p class="catalogo-book-author">
                                <span class="sr-only">Autor(es): </span>
                                {{ $libro->autores->pluck('nombre')->join(', ') ?: 'Autor no registrado' }}
                            </p>

                            <div class="catalogo-book-meta" aria-label="Área temática y disponibilidad">
                                <span class="ui-badge">{{ $libro->genero?->nombre ?? 'Área sin clasificar' }}</span>
                                @if ($libro->disponibles_count > 0)
                                    <span class="ui-badge ok">{{ $libro->disponibles_count }} {{ $libro->disponibles_count === 1 ? 'ejemplar disponible' : 'ejemplares disponibles' }}</span>
                                @else
                                    <span class="ui-badge bad">Sin ejemplares disponibles</span>
                                @endif
                            </div>

                            <div class="catalogo-book-actions">
                                <a class="ui-btn ui-btn-sm" href="{{ route('catalogo.show', $libro) }}" aria-label="Ver detalle de {{ $libro->titulo }}">
                                    Ver detalle
                                </a>
                                @if ($libro->recurso_digital_url)
                                    <a class="ui-btn ui-btn-light ui-btn-sm" href="{{ $libro->recurso_digital_url }}" target="_blank" rel="noopener noreferrer">
                                        Recurso digital <span class="sr-only">(se abre en una pestaña nueva)</span>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <nav aria-label="Paginación del catálogo" class="catalogo-pagination">
                    {{ $libros->links() }}
                </nav>
            @endif
        </section>
    </div>
</x-app-layout>
