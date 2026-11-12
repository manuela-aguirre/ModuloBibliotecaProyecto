<?php

namespace App\Services;

use App\Models\Ejemplar;
use App\Models\InventoryMovement;
use App\Models\Libro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function createExemplar(Libro $libro, array $attributes, bool $processWaitlist = true): Ejemplar
    {
        $created = DB::transaction(function () use ($libro, $attributes) {
            $libro = Libro::query()->lockForUpdate()->findOrFail($libro->id);
            $ejemplar = $libro->ejemplares()->create($attributes);

            $this->recordMovement(
                $libro,
                $ejemplar,
                'entrada',
                'ingreso_ejemplar',
                1,
                $this->availableCount($libro),
                "Ingreso del ejemplar {$ejemplar->codigo_barras}.",
            );
            app(EjemplarHistoryService::class)->record($ejemplar, 'alta', 'Ejemplar registrado en el inventario.');

            return $ejemplar;
        });

        if ($processWaitlist) {
            DB::afterCommit(fn () => app(WaitlistService::class)->assignPending($this));
        }

        return $created;
    }

    public function updateExemplar(
        Ejemplar $ejemplar,
        array $attributes,
        ?string $reason = null,
        ?string $eventType = null,
    ): Ejemplar
    {
        $updated = DB::transaction(function () use ($ejemplar, $attributes, $reason, $eventType) {
            $libro = Libro::query()->lockForUpdate()->findOrFail($ejemplar->libro_id);
            $ejemplar = Ejemplar::query()->lockForUpdate()->findOrFail($ejemplar->id);
            $previousState = $ejemplar->estado;
            $previousLocation = $ejemplar->ubicacion_id;

            $ejemplar->fill($attributes);
            $ejemplar->save();

            $this->recordAvailabilityChange($libro, $ejemplar, $previousState, $ejemplar->estado, null, $reason);
            if ($previousState !== $ejemplar->estado) {
                if ($eventType === 'perdida' && $ejemplar->estado !== 'baja'
                    || $eventType === 'reparacion' && ($previousState !== 'danado' || $ejemplar->estado !== 'disponible')) {
                    throw ValidationException::withMessages([
                        'tipo_evento' => 'El tipo de evento no corresponde al cambio de estado seleccionado.',
                    ]);
                }

                app(EjemplarHistoryService::class)->record(
                    $ejemplar,
                    $eventType ?: $this->historyTypeFor($previousState, $ejemplar->estado, $reason),
                    $reason,
                    $previousState,
                    $ejemplar->estado,
                );
            }
            if ($previousLocation !== $ejemplar->ubicacion_id) {
                app(EjemplarHistoryService::class)->record(
                    $ejemplar,
                    'cambio_ubicacion',
                    $reason,
                    $ejemplar->estado,
                    $ejemplar->estado,
                    $previousLocation,
                    $ejemplar->ubicacion_id,
                );
            }
            if ($previousState !== 'disponible' && $ejemplar->estado === 'disponible') {
                DB::afterCommit(fn () => app(WaitlistService::class)->assignPending($this));
            }

            return $ejemplar;
        });

        return $updated;
    }

    public function changeStatus(
        Ejemplar $ejemplar,
        string $newState,
        ?Model $reference = null,
        ?string $reason = null,
    ): Ejemplar {
        $updated = DB::transaction(function () use ($ejemplar, $newState, $reference, $reason) {
            $libro = Libro::query()->lockForUpdate()->findOrFail($ejemplar->libro_id);
            $ejemplar = Ejemplar::query()->lockForUpdate()->findOrFail($ejemplar->id);
            $previousState = $ejemplar->estado;

            if ($previousState === $newState) {
                return $ejemplar;
            }

            $ejemplar->update(['estado' => $newState]);
            $this->recordAvailabilityChange($libro, $ejemplar, $previousState, $newState, $reference, $reason);
            app(EjemplarHistoryService::class)->record(
                $ejemplar,
                $this->historyTypeFor($previousState, $newState, $reason),
                $reason,
                $previousState,
                $newState,
                null,
                null,
                $reference,
            );
            return $ejemplar;
        });

        if ($newState === 'disponible') {
            DB::afterCommit(fn () => app(WaitlistService::class)->assignPending($this));
        }

        return $updated;
    }

    public function deleteExemplar(Ejemplar $ejemplar): void
    {
        DB::transaction(function () use ($ejemplar) {
            $libro = Libro::query()->lockForUpdate()->findOrFail($ejemplar->libro_id);
            $ejemplar = Ejemplar::query()->lockForUpdate()->findOrFail($ejemplar->id);

            if (in_array($ejemplar->estado, ['prestado', 'reservado'], true) || $ejemplar->prestamos()->exists()) {
                throw ValidationException::withMessages([
                    'estado' => 'Este ejemplar está en uso o tiene historial de préstamos. Márcalo como "baja" en lugar de eliminarlo.',
                ]);
            }

            $wasAvailable = $ejemplar->estado === 'disponible';
            $code = $ejemplar->codigo_barras;
            app(EjemplarHistoryService::class)->record(
                $ejemplar,
                'eliminacion',
                'Ejemplar eliminado del inventario.',
                $ejemplar->estado,
                null,
            );
            $ejemplar->delete();

            if ($wasAvailable) {
                $this->recordMovement(
                    $libro,
                    null,
                    'salida',
                    'retiro',
                    1,
                    $this->availableCount($libro),
                    "Retiro del ejemplar {$code}.",
                    $code,
                );
            }
        });
    }

    public function initializeOpening(Libro $libro): ?InventoryMovement
    {
        return DB::transaction(function () use ($libro) {
            $libro = Libro::query()->lockForUpdate()->findOrFail($libro->id);

            if ($libro->movimientosInventario()->exists()) {
                return null;
            }

            $balance = $this->availableCount($libro);
            $libro->update(['stock' => $balance]);

            return $this->recordMovement(
                $libro,
                null,
                'entrada',
                'inventario_inicial',
                $balance,
                $balance,
                'Saldo de ejemplares disponibles al inicializar el kardex.',
            );
        });
    }

    private function recordAvailabilityChange(
        Libro $libro,
        Ejemplar $ejemplar,
        string $previousState,
        string $newState,
        ?Model $reference,
        ?string $reason,
    ): void {
        if ($previousState === $newState) {
            return;
        }

        $isAdjustment = in_array($previousState, ['danado', 'baja'], true)
            || in_array($newState, ['danado', 'baja'], true);

        if ($isAdjustment && mb_strlen(trim((string) $reason)) < 10) {
            throw ValidationException::withMessages([
                'motivo' => 'El motivo del ajuste debe tener al menos 10 caracteres.',
            ]);
        }

        $wasAvailable = $previousState === 'disponible';
        $isAvailable = $newState === 'disponible';

        if ($wasAvailable === $isAvailable) {
            $this->recordMovement(
                $libro,
                $ejemplar,
                'cambio',
                'cambio_estado',
                0,
                $this->availableCount($libro),
                $reason ?? "Estado: {$previousState} → {$newState}.",
                null,
                $reference,
            );

            return;
        }

        if ($wasAvailable) {
            $concept = $newState === 'reservado'
                ? 'reserva'
                : ($newState === 'prestado' ? 'prestamo' : 'ajuste');
            $type = 'salida';
        } else {
            $concept = in_array($previousState, ['reservado', 'prestado'], true)
                ? ($previousState === 'reservado' ? 'liberacion_reserva' : 'devolucion')
                : 'ajuste';
            $type = 'entrada';
        }

        $this->recordMovement(
            $libro,
            $ejemplar,
            $type,
            $concept,
            1,
            $this->availableCount($libro),
            $reason,
            null,
            $reference,
        );
    }

    private function recordMovement(
        Libro $libro,
        ?Ejemplar $ejemplar,
        string $type,
        string $concept,
        int $quantity,
        int $balance,
        ?string $notes = null,
        ?string $ejemplarCode = null,
        ?Model $reference = null,
    ): InventoryMovement {
        return InventoryMovement::create([
            'libro_id' => $libro->id,
            'ejemplar_id' => $ejemplar?->id,
            'ejemplar_codigo' => $ejemplarCode ?? $ejemplar?->codigo_barras,
            'type' => $type,
            'concept' => $concept,
            'quantity' => $quantity,
            'balance' => $balance,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'user_id' => auth()->id(),
            'notes' => $notes,
        ]);
    }

    private function availableCount(Libro $libro): int
    {
        return $libro->ejemplares()->where('estado', 'disponible')->count();
    }

    private function historyTypeFor(string $previousState, string $newState, ?string $reason): string
    {
        if ($previousState === 'danado' && $newState === 'disponible') {
            return 'reparacion';
        }

        if ($newState === 'prestado') {
            return 'prestamo';
        }

        if ($previousState === 'prestado' && in_array($newState, ['disponible', 'danado'], true)) {
            return 'devolucion';
        }

        if ($newState === 'reservado') {
            return 'reserva';
        }

        if ($previousState === 'reservado' && $newState === 'disponible') {
            return 'cancelacion_reserva';
        }

        if ($newState === 'baja' && str_contains(mb_strtolower((string) $reason), 'perdid')) {
            return 'perdida';
        }

        return 'cambio_estado';
    }
}
