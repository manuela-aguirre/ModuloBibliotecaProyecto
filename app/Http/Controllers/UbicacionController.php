<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function index()
    {
        return view('ubicaciones.index', [
            'ubicaciones' => Ubicacion::withCount('ejemplares')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Ubicacion::create($request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Ubicación creada.');
    }

    public function destroy(Ubicacion $ubicacion)
    {
        $ubicacion->delete(); // los ejemplares quedan "sin ubicación" (nullOnDelete)

        return back()->with('status', 'Ubicación eliminada.');
    }
}