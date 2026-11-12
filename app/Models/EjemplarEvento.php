<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EjemplarEvento extends Model
{
    protected $table = 'ejemplar_eventos';

    protected $fillable = [
        'ejemplar_id', 'libro_id', 'codigo_barras', 'titulo_libro', 'tipo',
        'estado_anterior', 'estado_nuevo', 'ubicacion_anterior_id',
        'ubicacion_nueva_id', 'ubicacion_anterior_nombre', 'ubicacion_nueva_nombre',
        'user_id', 'referencia_type', 'referencia_id',
        'descripcion', 'ocurrido_at',
    ];

    protected function casts(): array
    {
        return ['ocurrido_at' => 'datetime'];
    }

    public function ejemplar()
    {
        return $this->belongsTo(Ejemplar::class);
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ubicacionAnterior()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_anterior_id');
    }

    public function ubicacionNueva()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_nueva_id');
    }

    public function referencia()
    {
        return $this->morphTo();
    }
}
