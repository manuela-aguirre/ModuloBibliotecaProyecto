<?php

namespace App\Http\Controllers;

use App\Models\Genero;
use App\Models\Libro;
use App\Models\ListaEspera;
use App\Models\Reserva;
use App\Models\Autor;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'tema' => ['nullable', 'string', 'max:100'],
            'genero_id' => ['nullable', 'integer', 'exists:generos,id'],
            'autor_id' => ['nullable', 'integer', 'exists:autores,id'],
            'disponibilidad' => ['nullable', 'in:disponibles,no_disponibles'],
            'orden' => ['nullable', 'in:titulo,autor,disponibilidad'],
        ]);

        $libros = Libro::with(['genero', 'autores'])
            ->conConteos()
            ->buscar($filters['q'] ?? null)
            ->conTema($filters['tema'] ?? null)
            ->when($filters['genero_id'] ?? null, fn ($q, $id) => $q->where('genero_id', $id))
            ->when($filters['autor_id'] ?? null, fn ($q, $id) => $q->whereHas('autores', fn ($a) => $a->whereKey($id)))
            ->when(
                ($filters['disponibilidad'] ?? null) === 'disponibles',
                fn ($q) => $q->conDisponibles()
            )
            ->when(
                ($filters['disponibilidad'] ?? null) === 'no_disponibles',
                fn ($q) => $q->whereDoesntHave('ejemplares', fn ($e) => $e->where('estado', 'disponible'))
            )
            ->when(($filters['orden'] ?? 'titulo') === 'autor', fn ($q) => $q->orderBy(
                Autor::query()
                    ->select('autores.nombre')
                    ->join('autor_libro', 'autores.id', '=', 'autor_libro.autor_id')
                    ->whereColumn('autor_libro.libro_id', 'libros.id')
                    ->orderBy('autores.nombre')
                    ->limit(1)
            ))
            ->when(($filters['orden'] ?? 'titulo') === 'disponibilidad', fn ($q) => $q->orderByDesc('disponibles_count'))
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        return view('catalogo.index', [
            'libros' => $libros,
            'generos' => Genero::orderBy('nombre')->get(),
            'autores' => Autor::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Libro $libro)
    {
        $libro->load(['genero', 'editorial', 'autores', 'ejemplares.ubicacion']);
        $libro->loadCount([
            'ejemplares as total_ejemplares',
            'ejemplares as disponibles_count' => fn ($query) => $query->where('estado', 'disponible'),
        ]);

        $miReserva = $request->user()
            ? Reserva::activas()
                ->where('user_id', $request->user()->id)
                ->whereHas('ejemplar', fn ($q) => $q->where('libro_id', $libro->id))
                ->first()
            : null;
        $miEspera = $request->user()
            ? ListaEspera::query()
                ->where('user_id', $request->user()->id)
                ->where('libro_id', $libro->id)
                ->whereIn('estado', ['espera', 'reservada'])
                ->first()
            : null;
        $personasEnEspera = ListaEspera::query()
            ->where('libro_id', $libro->id)
            ->where('estado', 'espera')
            ->count();
        $posicionEspera = $miEspera?->estado === 'espera'
            ? ListaEspera::query()
                ->where('libro_id', $libro->id)
                ->where('estado', 'espera')
                ->where('id', '<=', $miEspera->id)
                ->count()
            : null;

        return view('catalogo.show', compact('libro', 'miReserva', 'miEspera', 'personasEnEspera', 'posicionEspera'));
    }
}