<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservaController extends Controller
{
    /** Mis reservas (usuario). */
    public function index(Request $request)
    {
        $reservas = $request->user()->reservas()
            ->with('ejemplar.libro', 'ejemplar.ubicacion')
            ->latest()
            ->get();

        return view('reservas.index', compact('reservas'));
    }

    /** Todas las reservas (administrador). */
    public function todas(Request $request)
    {
        $estado = $request->get('estado', 'activa');

        $reservas = Reserva::with('user', 'ejemplar.libro', 'ejemplar.ubicacion')
            ->when($estado !== 'todas', fn ($q) => $q->where('estado', $estado))
            ->orderBy('fecha_retiro')
            ->paginate(20)
            ->withQueryString();

        return view('reservas.todas', compact('reservas', 'estado'));
    }

    /** Reserva un ejemplar disponible del libro. El sistema elige la copia. */
    public function store(Request $request, Libro $libro)
    {
        $request->validate([
            'fecha_retiro' => [
                'required', 'date', 'after_or_equal:today',
                'before_or_equal:'.today()->addDays(config('biblioteca.dias_max_reserva'))->toDateString(),
            ],
        ]);

        $user = $request->user();

        try {
            DB::transaction(function () use ($request, $libro, $user) {
                if ($user->tieneVencidos()) {
                    throw new RuntimeException('Tienes préstamos vencidos. Devuélvelos antes de reservar.');
                }

                if ($user->cupoRestante() <= 0) {
                    throw new RuntimeException('Alcanzaste tu límite de préstamos y reservas activas.');
                }

                $yaReservado = Reserva::activas()
                    ->where('user_id', $user->id)
                    ->whereHas('ejemplar', fn ($q) => $q->where('libro_id', $libro->id))
                    ->exists();

                if ($yaReservado) {
                    throw new RuntimeException('Ya tienes una reserva activa de este libro.');
                }

                // lockForUpdate evita que dos personas reserven la misma copia a la vez
                $ejemplar = Ejemplar::where('libro_id', $libro->id)
                    ->where('estado', 'disponible')
                    ->lockForUpdate()
                    ->first();

                if (! $ejemplar) {
                    throw new RuntimeException('No hay ejemplares disponibles de este libro.');
                }

                $ejemplar->update(['estado' => 'reservado']);

                Reserva::create([
                    'user_id' => $user->id,
                    'ejemplar_id' => $ejemplar->id,
                    'fecha_retiro' => $request->fecha_retiro,
                    'estado' => 'activa',
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['reserva' => $e->getMessage()]);
        }

        return redirect()->route('reservas.index')
            ->with('status', 'Reserva creada. Pasa por la biblioteca el día elegido para reclamar tu libro.');
    }

    /** Cancela una reserva (el dueño o un administrador). */
    public function destroy(Request $request, Reserva $reserva)
    {
        abort_unless(
            $reserva->user_id === $request->user()->id || $request->user()->esAdministrador(),
            403
        );

        if ($reserva->estado === 'activa') {
            DB::transaction(function () use ($reserva) {
                $reserva->update(['estado' => 'cancelada']);
                $reserva->ejemplar->update(['estado' => 'disponible']);
            });
        }

        return back()->with('status', 'Reserva cancelada.');
    }
}