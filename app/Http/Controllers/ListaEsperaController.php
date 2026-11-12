<?php

namespace App\Http\Controllers;

use App\Models\ListaEspera;
use App\Models\Libro;
use App\Services\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ListaEsperaController extends Controller
{
    public function store(Request $request, Libro $libro, WaitlistService $waitlist)
    {
        $waitlist->join($request->user(), $libro);

        return redirect()->route('reservas.index')
            ->with('status', 'Te agregamos al final de la lista de espera. Revisa Mis reservas para ver si se te asignó un ejemplar.');
    }

    public function destroy(Request $request, ListaEspera $entrada)
    {
        abort_unless($entrada->user_id === $request->user()->id, 403);

        if ($entrada->estado === 'reservada') {
            throw ValidationException::withMessages([
                'lista_espera' => 'Ya se te asignó una reserva; cancélala desde Mis reservas si no la necesitas.',
            ]);
        }

        if ($entrada->estado === 'espera') {
            $entrada->update(['estado' => 'cancelada']);
        }

        return back()->with('status', 'Se actualizó tu lista de espera.');
    }
}
