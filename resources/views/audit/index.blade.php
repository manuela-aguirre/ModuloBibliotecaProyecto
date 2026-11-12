<x-app-layout>
    <x-slot name="header"><h2 class="ui-title">Bitácora de auditoría</h2></x-slot>
    @include('partials.ui')

    <div class="cotec-panel ui-wrap space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">Actividad registrada</h3>
                <p class="mt-1 text-sm text-slate-600">Consulta y exporta los cambios realizados en el sistema.</p>
            </div>
            <a class="ui-btn" href="{{ route('audit.pdf', request()->query()) }}">Descargar PDF</a>
        </div>

        <section class="grid gap-3 grid-cols-2 lg:grid-cols-5" aria-label="Resumen de auditoría">
            @foreach ([
                ['label' => 'Total', 'count' => $eventCounts['total'], 'style' => 'border-slate-200 bg-white'],
                ['label' => 'Creaciones', 'count' => $eventCounts['created'], 'style' => 'border-green-200 bg-green-50'],
                ['label' => 'Actualizaciones', 'count' => $eventCounts['updated'], 'style' => 'border-amber-200 bg-amber-50'],
                ['label' => 'Eliminaciones', 'count' => $eventCounts['deleted'], 'style' => 'border-red-200 bg-red-50'],
                ['label' => 'Restauraciones', 'count' => $eventCounts['restored'], 'style' => 'border-blue-200 bg-blue-50'],
            ] as $summary)
                <div class="rounded-xl border p-4 {{ $summary['style'] }}">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $summary['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $summary['count'] }}</p>
                </div>
            @endforeach
        </section>

        <details class="rounded-xl border border-slate-200 bg-slate-50" @if ($hasActiveFilters) open @endif>
            <summary class="cursor-pointer list-none px-4 py-3 font-semibold text-slate-700">
                Filtrar actividad
                @if ($hasActiveFilters)
                    <span class="ml-2 ui-badge">Filtros activos</span>
                @endif
            </summary>
            <form method="GET" action="{{ route('audit.index') }}" class="grid gap-x-4 gap-y-2 border-t border-slate-200 px-4 pt-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="ui-field">
                    <label class="ui-label" for="module">Módulo</label>
                    <select class="ui-select" id="module" name="module">
                        <option value="">Todos los módulos</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="user">Usuario</label>
                    <select class="ui-select" id="user" name="user">
                        <option value="">Todos los usuarios</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="event">Tipo de evento</label>
                    <select class="ui-select" id="event" name="event">
                        <option value="">Todos los eventos</option>
                        @foreach (['created' => 'Creado', 'updated' => 'Actualizado', 'deleted' => 'Eliminado', 'restored' => 'Restaurado'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('event') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="from">Desde</label>
                    <input class="ui-input" id="from" type="date" name="from" value="{{ request('from') }}">
                </div>
                <div class="ui-field">
                    <label class="ui-label" for="to">Hasta</label>
                    <input class="ui-input" id="to" type="date" name="to" value="{{ request('to') }}">
                </div>
                <div class="flex flex-wrap items-end gap-2 pb-4">
                    <button class="ui-btn ui-btn-light" type="submit">Aplicar filtros</button>
                    <a class="text-sm font-semibold text-slate-600 underline" href="{{ route('audit.index') }}">Limpiar</a>
                </div>
            </form>
        </details>

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
            <p>Mostrando {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }} de {{ $activities->total() }} registros</p>
            @if ($hasActiveFilters)
                <p>El resumen y el PDF corresponden a los filtros aplicados.</p>
            @endif
        </div>

        <section aria-label="Resultados de auditoría">
            <div class="ui-table-scroll" role="region" aria-label="Resultados de auditoría, desplazable horizontalmente" tabindex="0">
                <table class="ui-table min-w-[1000px]">
                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Módulo / evento</th>
                            <th>Actividad / registro</th>
                            <th>Responsable</th>
                            <th>Detalle de cambios</th>
                        </tr>
                    </thead>
                    <tbody>
            @forelse ($activities as $activity)
                @php
                    $eventLabels = ['created' => 'Creado', 'updated' => 'Actualizado', 'deleted' => 'Eliminado', 'restored' => 'Restaurado'];
                    $event = $activity->event ?? '';
                    $eventClass = match ($event) {
                        'created', 'restored' => 'ok',
                        'deleted' => 'bad',
                        'updated' => 'warn',
                        default => '',
                    };
                    $changes = $activity->attribute_changes ?? collect();
                    $oldValues = collect($changes->get('old', []));
                    $newValues = collect($changes->get('attributes', []));
                    $changedFields = $oldValues->keys()->merge($newValues->keys())->unique();
                @endphp
                        <tr>
                            <td>
                                <time datetime="{{ $activity->created_at?->toIso8601String() }}">
                                    {{ $activity->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                                </time>
                            </td>
                            <td>
                                <span class="ui-badge">{{ ucfirst($activity->log_name ?? 'general') }}</span>
                                <span class="mt-1 block"><span class="ui-badge {{ $eventClass }}">{{ $eventLabels[$event] ?? ucfirst($event ?: 'Actividad') }}</span></span>
                            </td>
                            <td>
                                <span class="font-medium text-slate-800">{{ $activity->description }}</span>
                                <span class="mt-1 block text-xs text-slate-500">
                                    Registro: {{ $activity->subject?->getKey() ? class_basename($activity->subject).' #'.$activity->subject->getKey() : '—' }}
                                </span>
                            </td>
                            <td>{{ $activity->causer?->name ?? 'Sistema' }}</td>
                            <td>
                                @if ($changedFields->isNotEmpty())
                                    <details>
                                        <summary class="cursor-pointer font-semibold text-green-800">Ver {{ $changedFields->count() }} {{ $changedFields->count() === 1 ? 'cambio' : 'cambios' }}</summary>
                                        <dl class="mt-2 min-w-[300px] space-y-2">
                                            @foreach ($changedFields as $field)
                                                @php
                                                    $oldValue = $oldValues->get($field);
                                                    $newValue = $newValues->get($field);
                                                    $displayValue = fn ($value) => $value === null
                                                        ? '—'
                                                        : (is_bool($value)
                                                            ? ($value ? 'Sí' : 'No')
                                                            : (is_scalar($value)
                                                                ? (strlen((string) $value) ? (string) $value : '—')
                                                                : (json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—')));
                                                @endphp
                                                <div class="rounded-lg bg-slate-50 p-2">
                                                    <dt class="font-semibold">{{ ucfirst(str_replace('_', ' ', $field)) }}</dt>
                                                    <dd class="mt-1 text-xs"><span class="font-medium text-slate-500">Antes:</span> <span class="break-all">{{ $displayValue($oldValue) }}</span></dd>
                                                    <dd class="text-xs"><span class="font-medium text-slate-500">Después:</span> <span class="break-all">{{ $displayValue($newValue) }}</span></dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </details>
                                @else
                                    <span class="text-slate-500">Sin cambios detallados</span>
                                @endif
                            </td>
                        </tr>
            @empty
                        <tr><td colspan="5" class="ui-empty">No hay registros para los filtros seleccionados.</td></tr>
            @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $activities->links() }}
    </div>
</x-app-layout>
