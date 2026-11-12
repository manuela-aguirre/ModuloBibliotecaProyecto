<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Ajuste de inventario</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-5">
        @include('partials.mensajes')

        <p class="text-sm text-slate-600">Registre un cambio de estado después de verificar físicamente el ejemplar. Los ejemplares prestados o reservados no se pueden ajustar desde esta pantalla.</p>

        <form method="POST" action="{{ route('inventory.adjust.store') }}" class="space-y-4">
            @csrf
            <div class="ui-field">
                <label class="ui-label" for="ejemplar_id">Ejemplar</label>
                <select class="ui-select" id="ejemplar_id" name="ejemplar_id" required>
                    <option value="">Seleccione un ejemplar</option>
                    @foreach ($libros as $libro)
                        @if ($libro->ejemplares->isNotEmpty())
                            <optgroup label="{{ $libro->titulo }}">
                                @foreach ($libro->ejemplares as $ejemplar)
                                    <option value="{{ $ejemplar->id }}" @selected(old('ejemplar_id') == $ejemplar->id)>
                                        {{ $ejemplar->codigo_barras }} — {{ ucfirst($ejemplar->estado) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('ejemplar_id')" class="mt-2" />
            </div>

            <div class="ui-field">
                <label class="ui-label" for="estado">Estado verificado</label>
                <select class="ui-select" id="estado" name="estado" required>
                    <option value="">Seleccione</option>
                    <option value="disponible" @selected(old('estado') === 'disponible')>Disponible</option>
                    <option value="danado" @selected(old('estado') === 'danado')>Dañado</option>
                    <option value="baja" @selected(old('estado') === 'baja')>Dado de baja</option>
                </select>
                <x-input-error :messages="$errors->get('estado')" class="mt-2" />
            </div>

            <div class="ui-field">
                <label class="ui-label" for="motivo">Motivo (mínimo 10 caracteres)</label>
                <textarea class="ui-input" id="motivo" name="motivo" rows="3" minlength="10" maxlength="255" required>{{ old('motivo') }}</textarea>
                <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
            </div>

            <div class="ui-inline">
                <button class="ui-btn">Registrar ajuste</button>
                <a class="ui-btn ui-btn-light" href="{{ route('inventory.kardex') }}">Volver al kardex</a>
            </div>
        </form>
    </div>
</x-app-layout>
