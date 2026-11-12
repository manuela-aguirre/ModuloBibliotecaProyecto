<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use Auditable;

    protected string $auditLog = 'ubicaciones';

    protected $table = 'ubicaciones';

    protected $fillable = ['nombre', 'descripcion'];

    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class);
    }
}