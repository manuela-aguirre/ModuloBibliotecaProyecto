<?php

namespace App\Services;

use App\Models\Ejemplar;
use App\Models\EjemplarEvento;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Model;

class EjemplarHistoryService
{
    public function record(
        Ejemplar $ejemplar,
        string $type,
        ?string $description = null,
        ?string $oldState = null,
        ?string $newState = null,
        ?int $oldLocation = null,
        ?int $newLocation = null,
        ?Model $reference = null,
    ): EjemplarEvento {
        $description = $this->appendReferenceDetails($description, $reference);

        return EjemplarEvento::create([
            'ejemplar_id' => $ejemplar->id,
            'libro_id' => $ejemplar->libro_id,
            'codigo_barras' => $ejemplar->codigo_barras,
            'titulo_libro' => $ejemplar->libro?->titulo,
            'tipo' => $type,
            'estado_anterior' => $oldState,
            'estado_nuevo' => $newState,
            'ubicacion_anterior_id' => $oldLocation,
            'ubicacion_nueva_id' => $newLocation,
            'ubicacion_anterior_nombre' => $oldLocation ? Ubicacion::find($oldLocation)?->nombre : null,
            'ubicacion_nueva_nombre' => $newLocation ? Ubicacion::find($newLocation)?->nombre : null,
            'user_id' => auth()->id(),
            'referencia_type' => $reference?->getMorphClass(),
            'referencia_id' => $reference?->getKey(),
            'descripcion' => $description,
            'ocurrido_at' => now(),
        ]);
    }

    private function appendReferenceDetails(?string $description, ?Model $reference): ?string
    {
        if ($reference instanceof Prestamo) {
            $reference->loadMissing('user');
            $user = $reference->user;
            $details = [
                'Préstamo #'.$reference->id,
                'Usuario: '.($user?->name ?? 'Usuario no disponible')
                    .($user?->numero_identificacion ? ' ('.$user->numero_identificacion.')' : ''),
                'Fecha: '.$reference->fecha_prestamo?->format('d/m/Y'),
                'Fecha límite: '.$reference->fecha_limite?->format('d/m/Y'),
            ];

            if ($reference->fecha_devolucion) {
                $details[] = 'Devuelto: '.$reference->fecha_devolucion->format('d/m/Y');
            }

            if ($reference->observaciones) {
                $details[] = 'Observaciones: '.$reference->observaciones;
            }

            return $this->joinDescription($description, implode(' · ', $details));
        }

        if ($reference instanceof Reserva) {
            $reference->loadMissing('user');
            $user = $reference->user;
            $details = [
                'Reserva #'.$reference->id,
                'Usuario: '.($user?->name ?? 'Usuario no disponible')
                    .($user?->numero_identificacion ? ' ('.$user->numero_identificacion.')' : ''),
                'Fecha de retiro: '.$reference->fecha_retiro?->format('d/m/Y'),
                'Estado de reserva: '.ucfirst($reference->estado),
            ];

            if ($reference->observacion) {
                $details[] = 'Observación: '.$reference->observacion;
            }

            return $this->joinDescription($description, implode(' · ', $details));
        }

        return $description;
    }

    private function joinDescription(?string $description, string $referenceDetails): string
    {
        return $description
            ? $description.' '.$referenceDetails
            : $referenceDetails;
    }
}
