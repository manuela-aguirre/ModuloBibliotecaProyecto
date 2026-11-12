<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    public const CONCEPTS = [
        'inventario_inicial' => 'Inventario inicial',
        'ingreso_ejemplar' => 'Ingreso de ejemplar',
        'reserva' => 'Reserva',
        'prestamo' => 'Préstamo directo',
        'liberacion_reserva' => 'Liberación de reserva',
        'devolucion' => 'Devolución',
        'cambio_estado' => 'Cambio de estado',
        'ajuste' => 'Ajuste de estado',
        'retiro' => 'Retiro de ejemplar',
    ];

    protected $fillable = [
        'libro_id', 'ejemplar_id', 'ejemplar_codigo', 'type', 'concept',
        'quantity', 'balance', 'reference_type', 'reference_id', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'balance' => 'integer',
        ];
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function ejemplar()
    {
        return $this->belongsTo(Ejemplar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function conceptLabel(): string
    {
        return self::CONCEPTS[$this->concept] ?? $this->concept;
    }
}
