<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Editorial extends Model
{
    use Auditable;

    protected string $auditLog = 'editoriales';

    protected $fillable = [
        'nombre',
    ];

    // Relación: Editorial tiene muchos Libros
    public function libros()
    {
        return $this->hasMany(Libro::class);
    }
}
