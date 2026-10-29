<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = ['user_id', 'ejemplar_id', 'fecha_retiro', 'estado'];

    protected function casts(): array
    {
        return ['fecha_retiro' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ejemplar()
    {
        return $this->belongsTo(Ejemplar::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}