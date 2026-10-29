<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    protected $table = 'prestamos';

    protected $fillable = [
        'user_id', 'registrado_por', 'fecha_prestamo', 'fecha_limite',
        'fecha_devolucion', 'multa', 'multa_pagada', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_prestamo' => 'date',
            'fecha_limite' => 'date',
            'fecha_devolucion' => 'date',
            'multa' => 'decimal:2',
            'multa_pagada' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function ejemplares()
    {
        return $this->belongsToMany(Ejemplar::class, 'prestamo_ejemplar')
            ->withPivot('estado')
            ->withTimestamps();
    }

    public function scopeAbiertos($query)
    {
        return $query->whereNull('fecha_devolucion');
    }

    public function scopeVencidos($query)
    {
        return $query->whereNull('fecha_devolucion')->whereDate('fecha_limite', '<', today());
    }

    public function estaVencido(): bool
    {
        return $this->fecha_devolucion === null && $this->fecha_limite->lt(today());
    }
}