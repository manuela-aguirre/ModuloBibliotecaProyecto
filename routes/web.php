<?php

use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EjemplarController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UbicacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');

// ---------- Público: consultar disponibilidad (sin iniciar sesión) ----------
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{libro}', [CatalogoController::class, 'show'])->name('catalogo.show');
Route::view('/politica-datos', 'politica-datos')->name('politica.datos');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ---------- Usuario autenticado ----------
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/mis-reservas', [ReservaController::class, 'index'])->name('reservas.index');
    Route::post('/catalogo/{libro}/reservar', [ReservaController::class, 'store'])->name('reservas.store');
    Route::delete('/reservas/{reserva}', [ReservaController::class, 'destroy'])->name('reservas.destroy');
    Route::get('/mis-prestamos', [PrestamoController::class, 'mios'])->name('prestamos.mios');
});

// ---------- Solo administrador / bibliotecario ----------
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/usuarios', [DashboardController::class, 'usuarios'])->name('usuarios.index');
    Route::get('/usuarios/historial', [PrestamoController::class, 'historial'])->name('usuarios.historial');
    Route::patch('/usuarios/{usuario}', [DashboardController::class, 'actualizarUsuario'])->name('usuarios.update');

    Route::resource('libros', LibroController::class);

    Route::resource('libros.ejemplares', EjemplarController::class)
        ->shallow()
        ->except(['show'])
        ->parameters(['ejemplares' => 'ejemplar']);

    Route::resource('ubicaciones', UbicacionController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['ubicaciones' => 'ubicacion']);

    Route::get('/reservas', [ReservaController::class, 'todas'])->name('reservas.todas');
    Route::post('/reservas/{reserva}/entregar', [PrestamoController::class, 'entregarReserva'])->name('reservas.entregar');

    Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/nuevo', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::patch('/prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::patch('/prestamos/{prestamo}/multa-pagada', [PrestamoController::class, 'pagarMulta'])->name('prestamos.multa-pagada');
    Route::patch('/prestamos/{prestamo}/condonar', [PrestamoController::class, 'condonar'])->name('prestamos.condonar');

    Route::get('/reportes/prestamos', [ReporteController::class, 'prestamos'])->name('reportes.prestamos');
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('/reportes/reservas', [ReporteController::class, 'reservas'])->name('reportes.reservas');
});

require __DIR__.'/auth.php';