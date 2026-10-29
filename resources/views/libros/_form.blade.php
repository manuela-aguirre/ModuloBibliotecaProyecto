<style>
    .libro-form { max-width: 640px; }
    .libro-form .field { margin-bottom: 1.1rem; }
    .libro-form input[type=text],
    .libro-form input[type=number],
    .libro-form input[type=url],
    .libro-form textarea,
    .libro-form select {
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid #dcfce7;
        padding: 0.6rem 0.9rem;
        margin-top: 0.35rem;
    }
    .libro-form label { font-weight: 600; color: #14532d; font-size: 0.85rem; }
    .libro-form .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .libro-form .hint { color: #64748b; font-size: 0.78rem; margin-top: 0.25rem; display: block; }
    .libro-form .actions { margin-top: 1.5rem; display: flex; gap: 0.75rem; }
    .libro-form .btn-cancel { padding: 0.6rem 1.1rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.85rem; text-decoration: none; color: #475569; background: #f1f5f9; }
</style>

<div class="libro-form">
    <div class="field">
        <label for="titulo">Título</label>
        <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $libro->titulo ?? '') }}" required maxlength="150">
        <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
    </div>

    <div class="field">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $libro->descripcion ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
    </div>

    <div class="grid-2">
        <div class="field">
            <label for="editorial_id">Editorial</label>
            <select id="editorial_id" name="editorial_id" required>
                <option value="">Selecciona una editorial</option>
                @foreach ($editoriales as $editorial)
                    <option value="{{ $editorial->id }}" @selected(old('editorial_id', $libro->editorial_id ?? '') == $editorial->id)>
                        {{ $editorial->nombre }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('editorial_id')" class="mt-1" />
        </div>

        <div class="field">
            <label for="genero_id">Género</label>
            <select id="genero_id" name="genero_id" required>
                <option value="">Selecciona un género</option>
                @foreach ($generos as $genero)
                    <option value="{{ $genero->id }}" @selected(old('genero_id', $libro->genero_id ?? '') == $genero->id)>
                        {{ $genero->nombre }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('genero_id')" class="mt-1" />
        </div>
    </div>

    {{-- NUEVO: autores (relación muchos a muchos) --}}
    <div class="field">
        <label for="autores">Autores</label>
        <select id="autores" name="autores[]" multiple size="5">
            @foreach ($autores as $autor)
                <option value="{{ $autor->id }}"
                    @selected(in_array($autor->id, old('autores', isset($libro) ? $libro->autores->pluck('id')->all() : [])))>
                    {{ $autor->nombre }}
                </option>
            @endforeach
        </select>
        <span class="hint">Mantén Ctrl (⌘ en Mac) para elegir varios.</span>
        <x-input-error :messages="$errors->get('autores')" class="mt-1" />
        <x-input-error :messages="$errors->get('autores.*')" class="mt-1" />
    </div>

    <div class="grid-2">
        <div class="field">
            <label for="isbn">ISBN</label>
            <input type="text" id="isbn" name="isbn" maxlength="20" value="{{ old('isbn', $libro->isbn ?? '') }}">
            <x-input-error :messages="$errors->get('isbn')" class="mt-1" />
        </div>

        <div class="field">
            <label for="portada_url">URL de la portada (opcional)</label>
            <input type="url" id="portada_url" name="portada_url" value="{{ old('portada_url', $libro->portada_url ?? '') }}">
            <x-input-error :messages="$errors->get('portada_url')" class="mt-1" />
        </div>
    </div>

    {{-- Solo al editar: el stock ya no se escribe a mano, se muestra --}}
    @isset($libro)
        <div class="field">
            <label>Ejemplares disponibles</label>
            <span class="hint">
                {{ $libro->stock }} disponible(s).
                Se actualiza solo desde
                <a href="{{ route('libros.ejemplares.index', $libro) }}" style="color:#14532d;font-weight:600;">Ejemplares</a>.
            </span>
        </div>
    @endisset

    <div class="actions">
        <x-primary-button>{{ isset($libro) ? 'Actualizar libro' : 'Guardar libro' }}</x-primary-button>
        <a href="{{ route('libros.index') }}" class="btn-cancel">Cancelar</a>
    </div>
</div>