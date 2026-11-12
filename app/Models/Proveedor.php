<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use Auditable;

    protected $table = 'proveedores';

    protected string $auditLog = 'proveedores';

    protected $fillable = [
        'nombre', 'contacto', 'correo', 'telefono', 'direccion', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function solicitudesCompra()
    {
        return $this->hasMany(SolicitudCompra::class);
    }
}
