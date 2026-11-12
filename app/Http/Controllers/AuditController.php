<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = $this->activitiesQuery($filters);
        $activities = (clone $query)
            ->paginate(20)
            ->withQueryString();
        $eventCounts = (clone $query)
            ->reorder()
            ->selectRaw('event, COUNT(*) as aggregate')
            ->groupBy('event')
            ->pluck('aggregate', 'event');

        return view('audit.index', [
            'activities' => $activities,
            'eventCounts' => [
                'total' => $activities->total(),
                'created' => (int) $eventCounts->get('created', 0),
                'updated' => (int) $eventCounts->get('updated', 0),
                'deleted' => (int) $eventCounts->get('deleted', 0),
                'restored' => (int) $eventCounts->get('restored', 0),
            ],
            'hasActiveFilters' => collect($filters)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->isNotEmpty(),
            'modules' => Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name'),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function pdf(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = $this->activitiesQuery($filters);
        $activities = (clone $query)->get();
        $eventCounts = (clone $query)
            ->reorder()
            ->selectRaw('event, COUNT(*) as aggregate')
            ->groupBy('event')
            ->pluck('aggregate', 'event');
        $eventLabels = [
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'deleted' => 'Eliminado',
            'restored' => 'Restaurado',
        ];
        $rows = $activities->map(function (Activity $activity) use ($eventLabels): array {
            $changes = $activity->attribute_changes ?? collect();
            $oldValues = collect($changes->get('old', []));
            $newValues = collect($changes->get('attributes', []));
            $changedFields = $oldValues->keys()->merge($newValues->keys())->unique();
            $changeDetails = $changedFields->map(fn ($field) => ucfirst(str_replace('_', ' ', $field)).': '.
                $this->formatValue($oldValues->get($field)).' → '.$this->formatValue($newValues->get($field)))
                ->implode("\n");
            $record = $activity->subject?->getKey()
                ? class_basename($activity->subject).' #'.$activity->subject->getKey()
                : '—';

            return [
                $activity->created_at?->format('d/m/Y H:i:s') ?? '—',
                ucfirst($activity->log_name ?? 'general')."\n".($eventLabels[$activity->event] ?? ucfirst($activity->event ?? 'Actividad')),
                $activity->description."\nRegistro: ".$record,
                $activity->causer?->name ?? 'Sistema',
                $changeDetails ?: 'Sin cambios detallados',
            ];
        });
        $generatedAt = now();
        $filename = 'auditoria_'.$generatedAt->format('Ymd_His').'.pdf';
        $filterSummary = [];

        if ($filters['module'] ?? null) {
            $filterSummary[] = 'Módulo: '.ucfirst($filters['module']);
        }
        if ($filters['event'] ?? null) {
            $filterSummary[] = 'Evento: '.$eventLabels[$filters['event']];
        }
        if ($filters['user'] ?? null) {
            $userName = User::query()->whereKey($filters['user'])->value('name');
            $filterSummary[] = 'Usuario: '.($userName ?? '#'.$filters['user']);
        }
        if ($filters['from'] ?? null) {
            $filterSummary[] = 'Desde: '.\Illuminate\Support\Carbon::parse($filters['from'])->format('d/m/Y');
        }
        if ($filters['to'] ?? null) {
            $filterSummary[] = 'Hasta: '.\Illuminate\Support\Carbon::parse($filters['to'])->format('d/m/Y');
        }

        return Pdf::loadView('audit.pdf', [
            'generatedAt' => $generatedAt,
            'rows' => $rows,
            'total' => $activities->count(),
            'eventCounts' => [
                'created' => (int) $eventCounts->get('created', 0),
                'updated' => (int) $eventCounts->get('updated', 0),
                'deleted' => (int) $eventCounts->get('deleted', 0),
                'restored' => (int) $eventCounts->get('restored', 0),
            ],
            'filterSummary' => $filterSummary,
        ])
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'module' => ['nullable', 'string', 'max:100'],
            'event' => ['nullable', 'in:created,updated,deleted,restored'],
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    private function activitiesQuery(array $filters): Builder
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['module'] ?? null, fn ($query, $module) => $query->where('log_name', $module))
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query
                ->where('causer_type', (new User)->getMorphClass())
                ->where('causer_id', $userId))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id');
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
    }
}
