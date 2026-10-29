<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    protected $fillable = [
        'titulo', 'descripcion', 'portada_url', 'stock', 'isbn', 'editorial_id', 'genero_id',
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

    /** stock = cantidad de ejemplares en estado "disponible" */
    public function sincronizarStock(): void
    {
        $this->update(['stock' => $this->ejemplares()->where('estado', 'disponible')->count()]);
    }

    /** Busca por título, ISBN o nombre de autor. */
    public function scopeBuscar($query, ?string $texto)
    {
        if (! $texto) {
            return $query;
        }

        return $query->where(function ($q) use ($texto) {
            $q->where('titulo', 'like', "%{$texto}%")
                ->orWhere('isbn', 'like', "%{$texto}%")
                ->orWhereHas('autores', fn ($a) => $a->where('nombre', 'like', "%{$texto}%"));
        });
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