<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Registrar préstamo</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap" style="max-width:560px;">
        @include('partials.mensajes')

        <form method="POST" action="{{ route('prestamos.store') }}">
            @csrf
            <div class="ui-field">
                <label class="ui-label" for="numero_identificacion">Número de identificación del usuario</label>
                <input class="ui-input" id="numero_identificacion" name="numero_identificacion" value="{{ old('numero_identificacion') }}" required autofocus>
            </div>
            <div class="ui-field">
                <label class="ui-label" for="codigo_barras">Código de barras del ejemplar</label>
                <input class="ui-input" id="codigo_barras" name="codigo_barras" value="{{ old('codigo_barras') }}" required>
                <span class="ui-muted">Se puede escanear con lector de código de barras.</span>
            </div>
            <div class="ui-field">
                <label class="ui-label" for="dias">Plazo (días)</label>
                <input class="ui-input" type="number" id="dias" name="dias" min="1" max="30" value="{{ old('dias', config('biblioteca.dias_prestamo')) }}" required>
            </div>
            <div class="ui-field">
                <label class="ui-label" for="observaciones">Observaciones</label>
                <textarea class="ui-input" id="observaciones" name="observaciones" rows="2">{{ old('observaciones') }}</textarea>
            </div>
            <div class="ui-inline">
                <button class="ui-btn">Registrar préstamo</button>
                <a class="ui-btn ui-btn-light" href="{{ route('prestamos.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>