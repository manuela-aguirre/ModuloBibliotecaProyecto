<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    use Auditable;

    protected $table = 'autores';

    protected string $auditLog = 'autores';

    protected $fillable = ['nombre'];

    public function libros()
    {
        return $this->belongsToMany(Libro::class, 'autor_libro');
    }
}