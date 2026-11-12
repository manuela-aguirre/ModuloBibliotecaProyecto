<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class SolicitudCompra extends Model
{
    use Auditable;

    protected $table = 'solicitudes_compra';

    public const ESTADOS = [
        'solicitada' => 'Solicitada',
        'aprobada' => 'Aprobada',
        'recibida' => 'Recibida',
        'cancelada' => 'Cancelada',
    ];

    protected string $auditLog = 'adquisiciones';

    protected $fillable = [
        'libro_id', 'proveedor_id', 'ubicacion_id', 'solicitado_por',
        'aprobado_por', 'recibido_por', 'cantidad', 'costo_unitario',
        'coleccion', 'estado', 'observaciones', 'aprobada_at', 'recibida_at',
    ];

    protected function casts(): array
    {
        return [
            'costo_unitario' => 'decimal:2',
            'aprobada_at' => 'datetime',
            'recibida_at' => 'datetime',
        ];
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function receptor()
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    public function getCostoTotalAttribute(): float
    {
        return (float) $this->costo_unitario * $this->cantidad;
    }
}
