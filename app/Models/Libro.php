<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    use Auditable;

    protected string $auditLog = 'libros';

    protected array $auditExcept = ['stock'];

    protected $fillable = [
        'titulo', 'descripcion', 'portada_url', 'recurso_digital_url', 'stock', 'isbn',
        'palabras_clave', 'editorial_id', 'genero_id',
    ];

    public function editorial()
    {
        return $this->belongsTo(Editorial::class)->withDefault(['nombre' => 'Sin editorial']);
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class)->withDefault(['nombre' => 'Sin género']);
    }

    public function autores()
    {
        return $this->belongsToMany(Autor::class, 'autor_libro');
    }

    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /** stock = cantidad de ejemplares en estado "disponible" */
    public function sincronizarStock(): void
    {
        $this->update(['stock' => $this->ejemplares()->where('estado', 'disponible')->count()]);
    }

    /** Busca por título, ISBN o nombre de autor. */
     public function scopeBuscar($query, ?string $texto)
{
    $terminos = collect(preg_split('/\s+/u', trim((string) $texto), -1, PREG_SPLIT_NO_EMPTY))
        ->filter()
        ->unique()
        ->take(8);

    foreach ($terminos as $termino) {
        $query->where(function ($q) use ($termino) {
            $coincidencia = '%'.$termino.'%';

            $q->where('titulo', 'like', $coincidencia)
                ->orWhere('isbn', 'like', $coincidencia)
                ->orWhere('palabras_clave', 'like', $coincidencia)
                ->orWhereHas('autores', fn ($a) => $a->where('nombre', 'like', $coincidencia))
                ->orWhereHas('ejemplares', fn ($e) => $e
                    ->where('codigo_barras', 'like', $coincidencia)
                    ->orWhere('signatura', 'like', $coincidencia));
        });
    }

    return $query;
}

public function scopeConTema($query, ?string $tema)
{
    return $query->when($tema, fn ($q) => $q->where('palabras_clave', 'like', '%'.$tema.'%'));
}

    public function scopeConStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /** Agrega total_ejemplares y disponibles_count a cada libro (sin N+1). */
    public function scopeConConteos($query)
    {
        return $query->withCount([
            'ejemplares as total_ejemplares',
            'ejemplares as disponibles_count' => fn ($q) => $q->where('estado', 'disponible'),
        ]);
    }

    public function scopeConDisponibles($query)
    {
        return $query->whereHas('ejemplares', fn ($q) => $q->where('estado', 'disponible'));
    }
}