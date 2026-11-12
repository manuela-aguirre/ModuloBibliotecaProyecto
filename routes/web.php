<?php

use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EjemplarController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\EjemplarHistoryController;
use App\Http\Controllers\ImportacionCatalogoController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ListaEsperaController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
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
    Route::post('/catalogo/{libro}/lista-espera', [ListaEsperaController::class, 'store'])->name('lista-espera.store');
    Route::delete('/lista-espera/{entrada}', [ListaEsperaController::class, 'destroy'])->name('lista-espera.destroy');
    Route::delete('/reservas/{reserva}', [ReservaController::class, 'destroy'])->name('reservas.destroy');
    Route::delete('/reservas/{reserva}/eliminar', [ReservaController::class, 'eliminar'])->name('reservas.eliminar');
    Route::get('/mis-prestamos', [PrestamoController::class, 'mios'])->name('prestamos.mios');
});

// ---------- Solo administrador / bibliotecario ----------
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/inventario/kardex', [InventoryController::class, 'kardex'])->name('inventory.kardex');
    Route::get('/inventario/ajustes', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventario/ajustes', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjust.store');
    Route::get('/auditoria', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/auditoria/pdf', [AuditController::class, 'pdf'])->name('audit.pdf');
    Route::get('/adquisiciones', [AdquisicionController::class, 'index'])->name('adquisiciones.index');
    Route::post('/adquisiciones', [AdquisicionController::class, 'store'])->name('adquisiciones.store');
    Route::patch('/adquisiciones/{solicitud}/estado', [AdquisicionController::class, 'actualizarEstado'])->name('adquisiciones.estado');
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::patch('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    Route::get('/importaciones/catalogo', [ImportacionCatalogoController::class, 'index'])->name('importaciones.catalogo');
    Route::post('/importaciones/catalogo', [ImportacionCatalogoController::class, 'store'])->name('importaciones.catalogo.store');
    Route::get('/importaciones/catalogo/plantilla.csv', [ImportacionCatalogoController::class, 'plantillaCsv'])->name('importaciones.catalogo.plantilla-csv');
    Route::get('/importaciones/catalogo/plantilla.xlsx', [ImportacionCatalogoController::class, 'plantillaExcel'])->name('importaciones.catalogo.plantilla-excel');
    Route::get('/importaciones/catalogo/plantilla.pdf', [ImportacionCatalogoController::class, 'plantillaPdf'])->name('importaciones.catalogo.plantilla-pdf');

    Route::get('/usuarios', [DashboardController::class, 'usuarios'])->name('usuarios.index');
    Route::get('/usuarios/historial', [PrestamoController::class, 'historial'])->name('usuarios.historial');
    Route::patch('/usuarios/{usuario}', [DashboardController::class, 'actualizarUsuario'])->name('usuarios.update');

    Route::resource('libros', LibroController::class);

    Route::resource('libros.ejemplares', EjemplarController::class)
        ->shallow()
        ->except(['show'])
        ->parameters(['ejemplares' => 'ejemplar']);
    Route::get('/ejemplares/{ejemplar}/historial', [EjemplarHistoryController::class, 'show'])->name('ejemplares.historial');

    Route::resource('ubicaciones', UbicacionController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['ubicaciones' => 'ubicacion']);

    Route::get('/reservas', [ReservaController::class, 'todas'])->name('reservas.todas');
    Route::post('/reservas/{reserva}/entregar', [PrestamoController::class, 'entregarReserva'])->name('reservas.entregar');
    Route::patch('/reservas/{reserva}/observacion', [ReservaController::class, 'actualizarObservacion'])->name('reservas.observacion');

    Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/nuevo', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::patch('/prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::patch('/prestamos/{prestamo}/multa-pagada', [PrestamoController::class, 'pagarMulta'])->name('prestamos.multa-pagada');
    Route::patch('/prestamos/{prestamo}/condonar', [PrestamoController::class, 'condonar'])->name('prestamos.condonar');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/descargar', [ReporteController::class, 'descargar'])->name('reportes.descargar');
    Route::get('/reportes/prestamos', [ReporteController::class, 'prestamos'])->name('reportes.prestamos');
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('/reportes/reservas', [ReporteController::class, 'reservas'])->name('reportes.reservas');
});

require __DIR__.'/auth.php';