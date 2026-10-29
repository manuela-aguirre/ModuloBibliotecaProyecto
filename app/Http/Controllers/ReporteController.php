<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reserva;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function prestamos(): StreamedResponse
    {
        return $this->csv('prestamos_'.now()->format('Ymd').'.csv', function ($out) {
            fputcsv($out, ['Prestamo', 'Usuario', 'Identificacion', 'Telefono', 'Carrera', 'Semestre', 'Codigo de barras', 'Titulo',
                'Fecha prestamo', 'Fecha limite', 'Fecha devolucion', 'Multa', 'Multa pagada']);

            Prestamo::with(['user', 'ejemplares.libro'])->chunkById(200, function ($prestamos) use ($out) {
                foreach ($prestamos as $p) {
                    foreach ($p->ejemplares as $e) {
                        fputcsv($out, [
                            $p->id, $p->user->name, $p->user->numero_identificacion, $p->user->telefono, $p->user->carrera, $p->user->semestre,
                            $e->codigo_barras, $e->libro->titulo,
                            $p->fecha_prestamo->toDateString(), $p->fecha_limite->toDateString(),
                            $p->fecha_devolucion?->toDateString(), $p->multa, $p->multa_pagada ? 'Si' : 'No',
                        ]);
                    }
                }
            });
        });
    }

    public function inventario(): StreamedResponse
    {
        return $this->csv('inventario_'.now()->format('Ymd').'.csv', function ($out) {
            fputcsv($out, ['Codigo de barras', 'Signatura', 'Titulo', 'ISBN', 'Editorial', 'Edicion',
                'Anio', 'Tipo', 'Ubicacion', 'Estado', 'Fecha adquisicion', 'Factura', 'Valor',
                'Numero de entrada', 'Paginas', 'Medio adquisicion', 'Recibido por', 'Fecha ingreso', 'Palabras clave']);

            Ejemplar::with(['libro.editorial', 'ubicacion'])->chunkById(200, function ($ejemplares) use ($out) {
                foreach ($ejemplares as $e) {
                    fputcsv($out, [
                        $e->codigo_barras, $e->signatura, $e->libro->titulo, $e->libro->isbn,
                        $e->libro->editorial->nombre, $e->edicion, $e->anio_edicion, $e->tipo_material,
                        $e->ubicacion?->nombre, $e->estado, $e->fecha_adquisicion?->toDateString(),
                        $e->numero_factura, $e->valor,
                        $e->numero_entrada, $e->paginas, $e->medio_adquisicion, $e->recibido_por, $e->fecha_ingreso?->toDateString(), $e->libro->palabras_clave,
                    ]);
                }
            });
        });
    }

    public function reservas(): StreamedResponse
    {
        return $this->csv('reservas_'.now()->format('Ymd').'.csv', function ($out) {
            fputcsv($out, ['Reserva', 'Usuario', 'Identificacion', 'Telefono', 'Carrera', 'Semestre',
                'Codigo de barras', 'Titulo', 'Fecha retiro', 'Estado']);

            Reserva::with(['user', 'ejemplar.libro'])->chunkById(200, function ($reservas) use ($out) {
                foreach ($reservas as $r) {
                    fputcsv($out, [
                        $r->id, $r->user->name, $r->user->numero_identificacion, $r->user->telefono,
                        $r->user->carrera, $r->user->semestre, $r->ejemplar->codigo_barras,
                        $r->ejemplar->libro->titulo, $r->fecha_retiro->toDateString(), $r->estado,
                    ]);
                }
            });
        });
    }

    private function csv(string $nombre, callable $escribir): StreamedResponse
    {
        return response()->streamDownload(function () use ($escribir) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete las tildes
            $escribir($out);
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}