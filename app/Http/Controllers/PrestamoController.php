<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PrestamoController extends Controller
{
    /** Listado para el bibliotecario. ?estado=abiertos|vencidos|cerrados */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:abiertos,vencidos,cerrados'],
            'q' => ['nullable', 'string', 'max:150'],
        ]);
        $estado = $filters['estado'] ?? null;
        $search = trim($filters['q'] ?? '');
        $baseQuery = Prestamo::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('numero_identificacion', 'like', "%{$search}%"))
                    ->orWhereHas('ejemplares.libro', fn ($book) => $book->where('titulo', 'like', "%{$search}%"));
            }));
        $counts = (clone $baseQuery)
            ->selectRaw(
                'COUNT(*) as total,
                SUM(CASE WHEN fecha_devolucion IS NULL THEN 1 ELSE 0 END) as abiertos,
                SUM(CASE WHEN fecha_devolucion IS NULL AND fecha_limite < ? THEN 1 ELSE 0 END) as vencidos,
                SUM(CASE WHEN fecha_devolucion IS NOT NULL THEN 1 ELSE 0 END) as cerrados',
                [today()->toDateString()],
            )
            ->first();

        $prestamos = (clone $baseQuery)->with(['user', 'ejemplares.libro'])
            ->when($estado === 'abiertos', fn ($q) => $q->abiertos())
            ->when($estado === 'vencidos', fn ($q) => $q->vencidos())
            ->when($estado === 'cerrados', fn ($q) => $q->whereNotNull('fecha_devolucion'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('prestamos.index', [
            'prestamos' => $prestamos,
            'estado' => $estado,
            'search' => $search,
            'counts' => [
                'total' => (int) $counts->total,
                'abiertos' => (int) $counts->abiertos,
                'vencidos' => (int) $counts->vencidos,
                'cerrados' => (int) $counts->cerrados,
            ],
        ]);
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
    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'numero_identificacion' => ['required', 'exists:users,numero_identificacion'],
            'codigo_barras' => ['required', 'exists:ejemplares,codigo_barras'],
            'dias' => ['required', 'integer', 'min:1', 'max:30'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::where('numero_identificacion', $data['numero_identificacion'])->firstOrFail();

        try {
            $this->procesar($user, $data['codigo_barras'], (int) $data['dias'], $data['observaciones'] ?? null, $request->user()->id, $inventory);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['prestamo' => $e->getMessage()]);
        }

        return redirect()->route('prestamos.index')->with('status', 'Préstamo registrado.');
    }

    /** Convierte una reserva activa en préstamo (botón "Entregar"). */
    public function entregarReserva(Request $request, Reserva $reserva, InventoryService $inventory)
    {
        abort_unless($reserva->estado === 'activa', 422, 'La reserva ya no está activa.');

        try {
            $this->procesar(
                $reserva->user,
                $reserva->ejemplar->codigo_barras,
                config('biblioteca.dias_prestamo'),
                'Entrega de reserva #'.$reserva->id,
                $request->user()->id,
                $inventory,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['prestamo' => $e->getMessage()]);
        }

        return redirect()->route('prestamos.index')->with('status', 'Reserva entregada como préstamo.');
    }

    /** Registra la devolución y calcula la multa si hay retraso. */
    public function devolver(Request $request, Prestamo $prestamo, InventoryService $inventory)
    {
        abort_if($prestamo->fecha_devolucion !== null, 422, 'Este préstamo ya fue cerrado.');

        $request->validate(['condicion' => ['required', 'in:devuelto,danado']]);

        DB::transaction(function () use ($request, $prestamo, $inventory) {
            $copiesBefore = $prestamo->ejemplares
                ->mapWithKeys(fn (Ejemplar $ejemplar) => [$ejemplar->codigo_barras => $ejemplar->pivot->estado])
                ->all();

            foreach ($prestamo->ejemplares as $ejemplar) {
                if ($ejemplar->pivot->estado !== 'prestado') {
                    continue;
                }

                $prestamo->ejemplares()->updateExistingPivot($ejemplar->id, ['estado' => $request->condicion]);
                $inventory->changeStatus(
                    $ejemplar,
                    $request->condicion === 'danado' ? 'danado' : 'disponible',
                    $prestamo,
                    $request->condicion === 'danado' ? 'Ejemplar devuelto dañado.' : 'Ejemplar devuelto.',
                );
            }

            $copiesAfter = $prestamo->ejemplares()
                ->get()
                ->mapWithKeys(fn (Ejemplar $ejemplar) => [$ejemplar->codigo_barras => $ejemplar->pivot->estado])
                ->all();
            if ($copiesBefore !== $copiesAfter) {
                $this->logLoanCopyChanges($prestamo, $copiesBefore, $copiesAfter, $request->user(), 'updated');
            }

            $hoy = today();
            $multa = $prestamo->calcularMultaHasta(\Carbon\CarbonImmutable::today());
            if ($multa === null) {
                throw new RuntimeException('Configure el salario mínimo mensual vigente antes de calcular multas.');
            }

            $prestamo->update(['fecha_devolucion' => $hoy, 'multa' => $multa]);
        });

        $mensaje = 'Devolución registrada.';
        if ($prestamo->fresh()->multa > 0) {
            $mensaje .= ' Multa generada: $'.number_format($prestamo->fresh()->multa, 0, ',', '.');
        }

        return back()->with('status', $mensaje);
    }

    /** Motivos de condonación del Art. 11 del Reglamento: clave => [porcentaje, texto]. */
    public const MOTIVOS_CONDONACION = [
        'incapacidad' => [100, 'Incapacidad médica'],
        'calamidad' => [100, 'Calamidad'],
        'reposicion' => [100, 'Reposición del material'],
        'capacitacion' => [50, 'Asistencia a capacitación programada por la Biblioteca'],
        'material_apoyo' => [50, 'Desarrollo de material de apoyo que promueva el uso de la Biblioteca'],
    ];

    /** Condona total (100 %) o parcialmente (50 %) una multa pendiente. */
    public function condonar(Request $request, Prestamo $prestamo)
    {
        $request->validate([
            'motivo' => ['required', \Illuminate\Validation\Rule::in(array_keys(self::MOTIVOS_CONDONACION))],
        ]);

        abort_if(
            $prestamo->multa <= 0 || $prestamo->multa_pagada || $prestamo->condonacion_porcentaje > 0,
            422,
            'Esta multa no se puede condonar.'
        );

        [$porcentaje, $texto] = self::MOTIVOS_CONDONACION[$request->motivo];
        $original = (float) $prestamo->multa;

        $prestamo->update([
            'multa_original' => $original,
            'multa' => round($original * (100 - $porcentaje) / 100, 2),
            'condonacion_porcentaje' => $porcentaje,
            'condonacion_motivo' => $texto,
        ]);

        return back()->with('status', "Multa condonada al {$porcentaje} %: {$texto}.");
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
    private function procesar(
        User $user,
        string $codigoBarras,
        int $dias,
        ?string $observaciones,
        int $adminId,
        InventoryService $inventory,
    ): void
    {
        DB::transaction(function () use ($user, $codigoBarras, $dias, $observaciones, $adminId, $inventory) {
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

            $copiesBefore = $prestamo->ejemplares()
                ->get()
                ->mapWithKeys(fn (Ejemplar $copy) => [$copy->codigo_barras => $copy->pivot->estado])
                ->all();
            $prestamo->ejemplares()->attach($ejemplar->id, ['estado' => 'prestado']);
            $inventory->changeStatus($ejemplar, 'prestado', $prestamo);
            $reserva?->update(['estado' => 'retirada']);
            if ($reserva) {
                app(\App\Services\WaitlistService::class)->fulfillForReservation($reserva);
            }

            $copiesAfter = $prestamo->ejemplares()
                ->get()
                ->mapWithKeys(fn (Ejemplar $copy) => [$copy->codigo_barras => $copy->pivot->estado])
                ->all();
            $this->logLoanCopyChanges($prestamo, $copiesBefore, $copiesAfter, User::findOrFail($adminId), 'updated');
        });
    }

    private function logLoanCopyChanges(Prestamo $prestamo, array $before, array $after, User $actor, string $event): void
    {
        activity('prestamos')
            ->performedOn($prestamo)
            ->causedBy($actor)
            ->event($event)
            ->withChanges([
                'old' => ['ejemplares' => $before],
                'attributes' => ['ejemplares' => $after],
            ])
            ->log('ejemplares asociados al préstamo modificados');
    }
}