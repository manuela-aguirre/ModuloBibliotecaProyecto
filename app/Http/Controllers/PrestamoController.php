<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PrestamoController extends Controller
{
    /** Listado para el bibliotecario. ?estado=abiertos|vencidos|cerrados */
    public function index(Request $request)
    {
        $estado = $request->get('estado');

        $prestamos = Prestamo::with(['user', 'ejemplares.libro'])
            ->when($estado === 'abiertos', fn ($q) => $q->abiertos())
            ->when($estado === 'vencidos', fn ($q) => $q->vencidos())
            ->when($estado === 'cerrados', fn ($q) => $q->whereNotNull('fecha_devolucion'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('prestamos.index', compact('prestamos', 'estado'));
    }

    /** Historial del usuario autenticado. */
    public function mios(Request $request)
    {
        $prestamos = $request->user()->prestamos()
            ->with('ejemplares.libro')
            ->latest()
            ->get();

        return view('prestamos.mios', compact('prestamos'));
    }

    /** Historial de préstamos y reservas de cualquier usuario, buscado por su número de identificación (bibliotecario). */
    public function historial(Request $request)
    {
        $numero = trim((string) $request->get('numero_identificacion'));
        $usuario = null;

        if ($numero !== '') {
            $usuario = User::with([
                'prestamos' => fn ($q) => $q->latest('fecha_prestamo')->with('ejemplares.libro'),
                'reservas' => fn ($q) => $q->latest('fecha_retiro')->with('ejemplar.libro'),
            ])->where('numero_identificacion', $numero)->first();
        }

        return view('usuarios.historial', ['usuario' => $usuario, 'buscado' => $numero !== '']);
    }

    public function create()
    {
        return view('prestamos.create');
    }

    /** Préstamo directo en mostrador (con o sin reserva previa). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_identificacion' => ['required', 'exists:users,numero_identificacion'],
            'codigo_barras' => ['required', 'exists:ejemplares,codigo_barras'],
            'dias' => ['required', 'integer', 'min:1', 'max:30'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::where('numero_identificacion', $data['numero_identificacion'])->firstOrFail();

        try {
            $this->procesar($user, $data['codigo_barras'], (int) $data['dias'], $data['observaciones'] ?? null, $request->user()->id);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['prestamo' => $e->getMessage()]);
        }

        return redirect()->route('prestamos.index')->with('status', 'Préstamo registrado.');
    }

    /** Convierte una reserva activa en préstamo (botón "Entregar"). */
    public function entregarReserva(Request $request, Reserva $reserva)
    {
        abort_unless($reserva->estado === 'activa', 422, 'La reserva ya no está activa.');

        try {
            $this->procesar(
                $reserva->user,
                $reserva->ejemplar->codigo_barras,
                config('biblioteca.dias_prestamo'),
                'Entrega de reserva #'.$reserva->id,
                $request->user()->id
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['prestamo' => $e->getMessage()]);
        }

        return redirect()->route('prestamos.index')->with('status', 'Reserva entregada como préstamo.');
    }

    /** Registra la devolución y calcula la multa si hay retraso. */
    public function devolver(Request $request, Prestamo $prestamo)
    {
        abort_if($prestamo->fecha_devolucion !== null, 422, 'Este préstamo ya fue cerrado.');

        $request->validate(['condicion' => ['required', 'in:devuelto,danado']]);

        DB::transaction(function () use ($request, $prestamo) {
            foreach ($prestamo->ejemplares as $ejemplar) {
                if ($ejemplar->pivot->estado !== 'prestado') {
                    continue;
                }

                $prestamo->ejemplares()->updateExistingPivot($ejemplar->id, ['estado' => $request->condicion]);
                $ejemplar->update(['estado' => $request->condicion === 'danado' ? 'danado' : 'disponible']);
            }

            $hoy = today();
            $diasRetraso = max(0, (int) $prestamo->fecha_limite->diffInDays($hoy, false));
            $multa = round(
                $diasRetraso * config('biblioteca.smdlv') * config('biblioteca.multa_porcentaje_dia') / 100,
                2
            );

            $prestamo->update(['fecha_devolucion' => $hoy, 'multa' => $multa]);
        });

        $mensaje = 'Devolución registrada.';
        if ($prestamo->fresh()->multa > 0) {
            $mensaje .= ' Multa generada: $'.number_format($prestamo->fresh()->multa, 0, ',', '.');
        }

        return back()->with('status', $mensaje);
    }

    public function pagarMulta(Prestamo $prestamo)
    {
        $prestamo->update(['multa_pagada' => true]);

        return back()->with('status', 'Multa marcada como pagada.');
    }

    /**
     * Reglas de negocio del préstamo. Todo ocurre en una transacción
     * y el ejemplar se bloquea para evitar que dos bibliotecarios lo presten a la vez.
     */
    private function procesar(User $user, string $codigoBarras, int $dias, ?string $observaciones, int $adminId): void
    {
        DB::transaction(function () use ($user, $codigoBarras, $dias, $observaciones, $adminId) {
            $ejemplar = Ejemplar::where('codigo_barras', $codigoBarras)->lockForUpdate()->firstOrFail();

            if ($user->tieneVencidos()) {
                throw new RuntimeException('El usuario tiene préstamos vencidos y no puede recibir más material.');
            }

            $reserva = null;

            if ($ejemplar->estado === 'reservado') {
                $reserva = $ejemplar->reservaActiva;

                if (! $reserva || $reserva->user_id !== $user->id) {
                    throw new RuntimeException('Ese ejemplar está reservado por otro usuario.');
                }
                // La reserva ya ocupaba un cupo del usuario, no se vuelve a validar.
            } elseif ($ejemplar->estado !== 'disponible') {
                throw new RuntimeException("El ejemplar no está disponible (estado: {$ejemplar->estado}).");
            } elseif ($user->cupoRestante() <= 0) {
                throw new RuntimeException('El usuario alcanzó su límite de préstamos y reservas.');
            }

            $prestamo = Prestamo::create([
                'user_id' => $user->id,
                'registrado_por' => $adminId,
                'fecha_prestamo' => today(),
                'fecha_limite' => today()->addDays($dias),
                'observaciones' => $observaciones,
            ]);

            $prestamo->ejemplares()->attach($ejemplar->id, ['estado' => 'prestado']);
            $ejemplar->update(['estado' => 'prestado']);
            $reserva?->update(['estado' => 'retirada']);
        });
    }
}