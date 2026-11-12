<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Descargar reportes</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-5">
        <p class="text-sm text-slate-600">Selecciona el reporte y el formato de archivo que quieres descargar.</p>

        <form method="GET" action="{{ route('reportes.descargar') }}" class="max-w-xl space-y-4">
            <div class="ui-field">
                <label class="ui-label" for="reporte">Reporte</label>
                <select class="ui-select" id="reporte" name="reporte" required>
                    <option value="">Selecciona un reporte</option>
                    <option value="prestamos">Préstamos</option>
                    <option value="inventario">Inventario</option>
                    <option value="reservas">Reservas</option>
                </select>
            </div>

            <div class="ui-field">
                <label class="ui-label" for="formato">Formato</label>
                <select class="ui-select" id="formato" name="formato" required>
                    <option value="">Selecciona un formato</option>
                    <option value="csv">CSV</option>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel (.xlsx)</option>
                </select>
            </div>

            <button type="submit" class="ui-btn">
                <i class="fa-solid fa-download"></i>
                Descargar reporte
            </button>
        </form>
    </div>
</x-app-layout>
