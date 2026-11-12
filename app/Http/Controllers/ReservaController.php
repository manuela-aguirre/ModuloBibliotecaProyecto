<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\ListaEspera;
use App\Models\Libro;
use App\Models\Reserva;
use App\Services\InventoryService;
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
        $listaEspera = $request->user()->listaEspera()
            ->with(['libro', 'reserva'])
            ->latest()
            ->get();

        return view('reservas.index', compact('reservas', 'listaEspera'));
    }

    /** Todas las reservas (administrador). */
    public function todas(Request $request)
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:activa,retirada,caducada,cancelada,todas'],
            'q' => ['nullable', 'string', 'max:150'],
        ]);
        $estado = $filters['estado'] ?? 'activa';
        $search = trim($filters['q'] ?? '');
        $baseQuery = Reserva::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('numero_identificacion', 'like', "%{$search}%"))
                    ->orWhereHas('ejemplar', fn ($copy) => $copy
                        ->where('codigo_barras', 'like', "%{$search}%")
                        ->orWhereHas('libro', fn ($book) => $book->where('titulo', 'like', "%{$search}%")));
            }));
        $counts = (clone $baseQuery)->selectRaw('estado, COUNT(*) as aggregate')->groupBy('estado')->pluck('aggregate', 'estado');

        $reservas = (clone $baseQuery)->with('user', 'ejemplar.libro', 'ejemplar.ubicacion')
            ->when($estado !== 'todas', fn ($q) => $q->where('estado', $estado))
            ->orderBy('fecha_retiro')
            ->paginate(20)
            ->withQueryString();

        return view('reservas.todas', [
            'reservas' => $reservas,
            'estado' => $estado,
            'search' => $search,
            'counts' => [
                'todas' => $counts->sum(),
                'activa' => (int) $counts->get('activa', 0),
                'retirada' => (int) $counts->get('retirada', 0),
                'caducada' => (int) $counts->get('caducada', 0),
                'cancelada' => (int) $counts->get('cancelada', 0),
            ],
        ]);
    }

    /** Reserva un ejemplar disponible del libro. El sistema elige la copia. */
    public function store(Request $request, Libro $libro, InventoryService $inventory)
    {
        $request->validate([
            'fecha_retiro' => [
                'required', 'date', 'after_or_equal:today',
                'before_or_equal:'.today()->addDays(config('biblioteca.dias_max_reserva'))->toDateString(),
            ],
        ]);

        $user = $request->user();

        try {
            DB::transaction(function () use ($request, $libro, $user, $inventory) {
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
                if (ListaEspera::where('libro_id', $libro->id)->where('estado', 'espera')->exists()) {
                    throw new RuntimeException('Hay personas esperando este libro. Únete a la lista para respetar el orden de llegada.');
                }

                // lockForUpdate evita que dos personas reserven la misma copia a la vez
                $ejemplar = Ejemplar::where('libro_id', $libro->id)
                    ->where('estado', 'disponible')
                    ->lockForUpdate()
                    ->first();

                if (! $ejemplar) {
                    throw new RuntimeException('No hay ejemplares disponibles de este libro.');
                }

                $reserva = Reserva::create([
                    'user_id' => $user->id,
                    'ejemplar_id' => $ejemplar->id,
                    'fecha_retiro' => $request->fecha_retiro,
                    'estado' => 'activa',
                ]);

                $inventory->changeStatus($ejemplar, 'reservado', $reserva);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['reserva' => $e->getMessage()]);
        }

        return redirect()->route('reservas.index')
            ->with('status', 'Reserva creada. Pasa por la biblioteca el día elegido para reclamar tu libro.');
    }

    /** Cancela una reserva (el dueño o un administrador). */
    public function destroy(Request $request, Reserva $reserva, InventoryService $inventory)
    {
        abort_unless(
            $reserva->user_id === $request->user()->id || $request->user()->esAdministrador(),
            403
        );

        if ($reserva->estado === 'activa') {
            DB::transaction(function () use ($reserva, $inventory) {
                $reserva->update(['estado' => 'cancelada']);
                app(\App\Services\WaitlistService::class)->cancelForReservation($reserva);
                $inventory->changeStatus($reserva->ejemplar, 'disponible', $reserva);
            });
        }

        return back()->with('status', 'Reserva cancelada.');
    }

    public function eliminar(Request $request, Reserva $reserva, InventoryService $inventory)
    {
        abort_unless(
            $reserva->user_id === $request->user()->id || $request->user()->esAdministrador(),
            403
        );

        DB::transaction(function () use ($reserva, $inventory) {
            if ($reserva->estado === 'activa') {
                app(\App\Services\WaitlistService::class)->cancelForReservation($reserva);
                $inventory->changeStatus($reserva->ejemplar, 'disponible', $reserva, 'Reserva eliminada.');
            }

            $reserva->delete();
        });

        return back()->with('status', 'Reserva eliminada.');
    }

    /** Agrega o actualiza una observación interna de la reserva. */
    public function actualizarObservacion(Request $request, Reserva $reserva)
    {
        abort_unless($request->user()->esAdministrador(), 403);

        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $reserva->update($data);

        return back()->with('status', 'Observación de la reserva guardada.');
    }
}