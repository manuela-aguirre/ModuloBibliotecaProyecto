<?php

namespace App\Services;

use App\Models\Ejemplar;
use App\Models\EjemplarEvento;
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
}
