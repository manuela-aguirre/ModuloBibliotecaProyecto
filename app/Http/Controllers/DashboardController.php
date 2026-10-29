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

    public function usuarios()
    {
        $usuarios = User::query()->latest('created_at')->get();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'usuariosVerificados' => User::query()->whereNotNull('email_verified_at')->count(),
            'totalUsuarios' => $usuarios->count(),
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

        return view('dashboard', [
            'totalLibros' => $totalLibros,
            'totalUsuarios' => User::count(),
            'prestamosActivos' => $prestamosActivos,
            'librosBajoStock' => Libro::where('stock', '<=', self::UMBRAL_BAJO_STOCK)->count(),
            'prestamosVencidos' => $prestamosVencidos,
            'multasPendientes' => (float) Prestamo::where('multa', '>', 0)->where('multa_pagada', false)->sum('multa'),
            'tasaVencimiento' => $prestamosActivos > 0 ? round($prestamosVencidos / $prestamosActivos * 100, 1) : 0,
            'usoCatalogo' => $totalLibros > 0 ? round($prestamosActivos / $totalLibros * 100, 1) : 0,
            'disponibilidadStock' => $totalLibros > 0 ? round($stockSaludable / $totalLibros * 100, 1) : 0,
            'stockSaludable' => $stockSaludable,
            'reservasActivas' => \App\Models\Reserva::activas()->count(),
            'masPrestados' => $this->masPrestados(),
            'prestamosPorMes' => $this->prestamosPorMes(),
        ]);
    }

    /** Los 5 títulos con más préstamos. */
    private function masPrestados()
    {
        return DB::table('prestamo_ejemplar')
            ->join('ejemplares', 'ejemplares.id', '=', 'prestamo_ejemplar.ejemplar_id')
            ->join('libros', 'libros.id', '=', 'ejemplares.libro_id')
            ->select('libros.titulo', DB::raw('count(*) as total'))
            ->groupBy('libros.id', 'libros.titulo')
            ->orderByDesc('total')
            ->limit(5)
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

        if ($usuario->id === $request->user()->id && $data['rol'] !== 'administrador') {
            return back()->withErrors(['rol' => 'No puedes quitarte tu propio rol de bibliotecario.']);
        }

        $usuario->rol = $data['rol'];
        $usuario->limite_prestamos = $data['limite_prestamos'];
        $usuario->save();

        return back()->with('status', "Usuario {$usuario->name} actualizado.");
    }
}
