<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use Auditable;

    protected $table = 'reservas';

    protected string $auditLog = 'reservas';

    protected $fillable = ['user_id', 'ejemplar_id', 'fecha_retiro', 'estado', 'observacion'];

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