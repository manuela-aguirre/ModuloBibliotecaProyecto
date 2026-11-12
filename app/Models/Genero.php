<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Genero extends Model
{
    use Auditable;

    protected string $auditLog = 'generos';

    protected $fillable = [
        'nombre',
    ];

    // Relación: Genero tiene muchos Libros
    public function libros()
    {
        return $this->hasMany(Libro::class);
    }
}
