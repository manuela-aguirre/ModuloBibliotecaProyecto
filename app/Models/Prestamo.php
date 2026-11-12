<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Support\CalendarioBiblioteca;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use Auditable;

    protected $table = 'prestamos';

    protected string $auditLog = 'prestamos';

    protected $fillable = [
        'user_id', 'registrado_por', 'fecha_prestamo', 'fecha_limite',
        'fecha_devolucion', 'multa', 'multa_pagada', 'observaciones',
        'multa_original', 'condonacion_porcentaje', 'condonacion_motivo',
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

    /**
     * Multa acumulada hasta hoy para préstamos abiertos, o multa final si ya se devolvió.
     * Devuelve null si la tarifa diaria aún no está configurada.
     */
    public function multaActual(): ?float
    {
        if ($this->fecha_devolucion !== null) {
            return (float) $this->multa;
        }

        if ($this->salarioMinimoMensual() <= 0) {
            return null;
        }

        return $this->calcularMultaHasta(CarbonImmutable::today());
    }

    public function calcularMultaHasta(CarbonImmutable $fecha): ?float
    {
        $salarioMinimo = $this->salarioMinimoMensual();
        if ($salarioMinimo <= 0) {
            return null;
        }

        $this->loadMissing('ejemplares');
        $diasServicio = CalendarioBiblioteca::contarDiasServicio(
            CarbonImmutable::parse($this->fecha_limite)->addDay(),
            $fecha,
        );

        $porcentajeAcumulado = $this->ejemplares->sum(
            fn (Ejemplar $ejemplar) => (float) config('biblioteca.porcentaje_multa.'.$ejemplar->coleccion, 0)
        );

        return round($diasServicio * ($salarioMinimo / 30) * $porcentajeAcumulado / 100, 2);
    }

    private function salarioMinimoMensual(): float
    {
        $smdlv = (float) config('biblioteca.smdlv', 0);
        if ($smdlv > 0) {
            return $smdlv * 30;
        }

        $salarioMinimo = (float) config('biblioteca.smlmv', 0);

        return $salarioMinimo;
    }
}