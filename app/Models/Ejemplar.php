<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ejemplar extends Model
{
    protected $table = 'ejemplares';

    public const ESTADOS = ['disponible', 'reservado', 'prestado', 'danado', 'baja'];

    protected $fillable = [
        'libro_id', 'ubicacion_id', 'codigo_barras', 'signatura', 'edicion',
        'anio_edicion', 'tipo_material', 'numero_factura', 'valor',
        'fecha_adquisicion', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function prestamos()
    {
        return $this->belongsToMany(Prestamo::class, 'prestamo_ejemplar')
            ->withPivot('estado')
            ->withTimestamps();
    }

    public function reservaActiva()
    {
        return $this->hasOne(Reserva::class)->where('estado', 'activa');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }

    /**
     * Cada vez que un ejemplar cambia de estado o se elimina,
     * se recalcula el stock del libro (= ejemplares disponibles).
     */
    protected static function booted(): void
    {
        static::saved(fn (Ejemplar $e) => $e->libro?->sincronizarStock());
        static::deleted(fn (Ejemplar $e) => $e->libro?->sincronizarStock());
    }
}