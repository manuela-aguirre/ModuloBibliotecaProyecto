<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const UMBRAL_BAJO_STOCK = 2;

    private function formatCompactNumber(int $value): string
    {
        return $value >= 1000 ? number_format($value / 1000, 1, '.', '').'k' : (string) $value;
    }

    public function welcome()
    {
        $totalLibros = Libro::count();
        $totalPrestamos = Prestamo::count();
        $conMulta = Prestamo::where('multa', '>', 0)->count();
        $stockSaludable = Libro::where('stock', '>', self::UMBRAL_BAJO_STOCK)->count();

        return view('welcome', [
            'panelStats' => [
                'registros' => $this->formatCompactNumber($totalLibros),
                'disponibilidad' => ($totalLibros > 0 ? round($stockSaludable / $totalLibros * 100, 1) : 0).'%',
                'acceso' => '24/7',
                'libros' => $this->formatCompactNumber($totalLibros),
                'usuarios' => $this->formatCompactNumber(User::count()),
                'prestamos' => $this->formatCompactNumber($totalPrestamos),
                'multas' => ($totalPrestamos > 0 ? round($conMulta / $totalPrestamos * 100, 1) : 0).'%',
            ],
        ]);
    }

    public function usuarios(\Illuminate\Http\Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'rol' => ['nullable', 'in:usuario,administrador'],
            'verificacion' => ['nullable', 'in:verificados,pendientes'],
        ]);
        $search = trim($filters['q'] ?? '');
        $usuarios = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('numero_identificacion', 'like', "%{$search}%");
            }))
            ->when($filters['rol'] ?? null, fn ($query, $role) => $query->where('rol', $role))
            ->when(($filters['verificacion'] ?? null) === 'verificados', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when(($filters['verificacion'] ?? null) === 'pendientes', fn ($query) => $query->whereNull('email_verified_at'))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'usuariosVerificados' => User::query()->whereNotNull('email_verified_at')->count(),
            'totalUsuarios' => User::count(),
            'search' => $search,
            'role' => $filters['rol'] ?? '',
            'verification' => $filters['verificacion'] ?? '',
        ]);
    }

    public function index()
    {
        // Los usuarios finales no ven el panel administrativo: van al catálogo
        if (! auth()->user()->esAdministrador()) {
            return redirect()->route('catalogo.index');
        }

        $totalLibros = Libro::count();
        $prestamosActivos = Prestamo::abiertos()->count();
        $prestamosVencidos = Prestamo::vencidos()->count();
        $stockSaludable = Libro::where('stock', '>', self::UMBRAL_BAJO_STOCK)->count();
        $desde = today()->subDays(29);
        $hasta = today();
        $multasPendientes = Prestamo::query()
            ->where('multa_pagada', false)
            ->where(function ($query): void {
                $query->where('multa', '>', 0)
                    ->orWhere(function ($openLoans): void {
                        $openLoans->whereNull('fecha_devolucion')
                            ->whereDate('fecha_limite', '<', today());
                    });
            })
            ->with('ejemplares')
            ->get()
            ->sum(fn (Prestamo $prestamo) => $prestamo->multaActual() ?? 0);

        return view('dashboard', [
            'totalLibros' => $totalLibros,
            'totalUsuarios' => User::count(),
            'prestamosActivos' => $prestamosActivos,
            'librosBajoStock' => Libro::where('stock', '<=', self::UMBRAL_BAJO_STOCK)->count(),
            'prestamosVencidos' => $prestamosVencidos,
            'multasPendientes' => $multasPendientes,
            'tasaVencimiento' => $prestamosActivos > 0 ? round($prestamosVencidos / $prestamosActivos * 100, 1) : 0,
            'usoCatalogo' => $totalLibros > 0 ? round($prestamosActivos / $totalLibros * 100, 1) : 0,
            'disponibilidadStock' => $totalLibros > 0 ? round($stockSaludable / $totalLibros * 100, 1) : 0,
            'stockSaludable' => $stockSaludable,
            'reservasActivas' => \App\Models\Reserva::activas()->count(),
            'prestamosUltimos30Dias' => Prestamo::whereBetween('fecha_prestamo', [$desde->toDateString(), $hasta->toDateString()])->count(),
            'masPrestados' => $this->masPrestados($desde, $hasta),
            'demandaPorCarrera' => $this->demandaPorCarrera($desde, $hasta),
            'titulosEnEspera' => $this->titulosEnEspera(),
            'personasEnEspera' => DB::table('lista_espera')->where('estado', 'espera')->count(),
            'periodoDesde' => $desde,
            'periodoHasta' => $hasta,
            'prestamosPorMes' => $this->prestamosPorMes(),
        ]);
    }

    /** Los 5 títulos con más ejemplares prestados en los últimos 30 días. */
    private function masPrestados(\Illuminate\Support\Carbon $desde, \Illuminate\Support\Carbon $hasta)
    {
        return DB::table('prestamo_ejemplar')
            ->join('ejemplares', 'ejemplares.id', '=', 'prestamo_ejemplar.ejemplar_id')
            ->join('libros', 'libros.id', '=', 'ejemplares.libro_id')
            ->join('prestamos', 'prestamos.id', '=', 'prestamo_ejemplar.prestamo_id')
            ->whereBetween('prestamos.fecha_prestamo', [$desde->toDateString(), $hasta->toDateString()])
            ->select('libros.titulo', DB::raw('count(*) as total'))
            ->groupBy('libros.id', 'libros.titulo')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /** Préstamos registrados por carrera durante los últimos 30 días. */
    private function demandaPorCarrera(\Illuminate\Support\Carbon $desde, \Illuminate\Support\Carbon $hasta)
    {
        return DB::table('prestamos')
            ->join('users', 'users.id', '=', 'prestamos.user_id')
            ->join('prestamo_ejemplar', 'prestamo_ejemplar.prestamo_id', '=', 'prestamos.id')
            ->whereBetween('prestamos.fecha_prestamo', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw("COALESCE(NULLIF(TRIM(users.carrera), ''), 'Sin carrera registrada') as carrera, COUNT(DISTINCT prestamos.id) as total")
            ->groupByRaw("COALESCE(NULLIF(TRIM(users.carrera), ''), 'Sin carrera registrada')")
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /** Títulos con personas actualmente en la cola de espera. */
    private function titulosEnEspera()
    {
        return DB::table('lista_espera')
            ->join('libros', 'libros.id', '=', 'lista_espera.libro_id')
            ->where('lista_espera.estado', 'espera')
            ->select('libros.titulo', DB::raw('count(*) as total'))
            ->groupBy('libros.id', 'libros.titulo')
            ->orderByDesc('total')
            ->orderBy('libros.titulo')
            ->limit(10)
            ->get();
    }

    /** Préstamos registrados en cada uno de los últimos 6 meses. */
    private function prestamosPorMes(): array
    {
        $meses = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        $conteo = Prestamo::where('fecha_prestamo', '>=', $meses->first())
            ->get(['fecha_prestamo'])
            ->countBy(fn ($p) => $p->fecha_prestamo->format('Y-m'));

        return $meses->map(fn ($m) => [
            'mes' => $m->translatedFormat('M Y'),
            'total' => $conteo->get($m->format('Y-m'), 0),
        ])->all();
    }

    /** Cambia el rol (usuario o bibliotecario) y el límite de préstamos de un usuario. */
    public function actualizarUsuario(\Illuminate\Http\Request $request, User $usuario)
    {
        $data = $request->validate([
            'rol' => ['required', 'in:usuario,administrador'],
            'limite_prestamos' => ['required', 'integer', 'between:1,10'],
        ]);
        $previousRole = $usuario->rol;

        if ($usuario->id === $request->user()->id && $data['rol'] !== 'administrador') {
            return back()->withErrors(['rol' => 'No puedes quitarte tu propio rol de bibliotecario.']);
        }

        $usuario->rol = $data['rol'];
        $usuario->limite_prestamos = $data['limite_prestamos'];
        $usuario->save();

        if ($previousRole !== $usuario->rol) {
            activity('seguridad')
                ->performedOn($usuario)
                ->causedBy($request->user())
                ->event('updated')
                ->withChanges([
                    'old' => ['rol' => $previousRole],
                    'attributes' => ['rol' => $usuario->rol],
                ])
                ->log('rol de usuario modificado');
        }

        return back()->with('status', "Usuario {$usuario->name} actualizado.");
    }
}
