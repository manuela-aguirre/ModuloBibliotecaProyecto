<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Libro;
use Illuminate\Database\Seeder;

class AutorSeeder extends Seeder
{
    public function run(): void
    {
        $autores = collect([
            'Ian Sommerville', 'Roger S. Pressman', 'Abraham Silberschatz', 'Andrew Tanenbaum',
            'Gabriel García Márquez', 'Miguel de Cervantes', 'Thomas Cormen', 'Martin Fowler',
        ])->map(fn ($nombre) => Autor::create(['nombre' => $nombre]));

        // Un autor aleatorio por libro (en producción se asigna desde el formulario del libro)
        Libro::all()->each(fn (Libro $libro) => $libro->autores()->attach($autores->random()->id));
    }
}