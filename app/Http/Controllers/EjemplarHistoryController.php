<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;

class EjemplarHistoryController extends Controller
{
    public function show(Ejemplar $ejemplar)
    {
        $ejemplar->load(['libro', 'ubicacion']);
        $eventos = $ejemplar->historyEvents()
            ->with(['usuario', 'ubicacionAnterior', 'ubicacionNueva'])
            ->latest('ocurrido_at')
            ->paginate(30);

        return view('ejemplares.historial', compact('ejemplar', 'eventos'));
    }
}
