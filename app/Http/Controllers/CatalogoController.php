<?php

namespace App\Http\Controllers;

use App\Models\Genero;
use App\Models\Libro;
use App\Models\Reserva;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $libros = Libro::with(['genero', 'autores'])
            ->conConteos()
            ->buscar($request->get('q'))
            ->when($request->filled('genero_id'), fn ($q) => $q->where('genero_id', $request->genero_id))
            ->when($request->boolean('solo_disponibles'), fn ($q) => $q->conDisponibles())
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        return view('catalogo.index', [
            'libros' => $libros,
            'generos' => Genero::orderBy('nombre')->get(),
            'q' => $request->get('q'),
        ]);
    }

    public function show(Request $request, Libro $libro)
    {
        $libro->load(['genero', 'editorial', 'autores', 'ejemplares.ubicacion']);

        $miReserva = $request->user()
            ? Reserva::activas()
                ->where('user_id', $request->user()->id)
                ->whereHas('ejemplar', fn ($q) => $q->where('libro_id', $libro->id))
                ->first()
            : null;

        return view('catalogo.show', compact('libro', 'miReserva'));
    }
}