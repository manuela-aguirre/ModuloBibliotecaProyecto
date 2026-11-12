<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaEspera extends Model
{
    protected $table = 'lista_espera';

    protected $fillable = ['libro_id', 'user_id', 'estado', 'reserva_id', 'atendida_at'];

    protected function casts(): array
    {
        return ['atendida_at' => 'datetime'];
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}
