<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reserva;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function descargar(Request $request): Response
    {
        $data = $request->validate([
            'reporte' => ['required', 'in:prestamos,inventario,reservas'],
            'formato' => ['required', 'in:csv,pdf,excel'],
        ]);
        $generatedAt = now();
        $filename = $this->filename($data['reporte'], $data['formato'], $generatedAt);

        return match ($data['formato']) {
            'csv' => $this->csv($data['reporte'], $filename, $generatedAt),
            'pdf' => $this->pdf($data['reporte'], $filename, $generatedAt),
            'excel' => $this->excel($data['reporte'], $filename, $generatedAt),
        };
    }

    public function prestamos(): StreamedResponse
    {
        return $this->csv('prestamos');
    }

    public function inventario(): StreamedResponse
    {
        return $this->csv('inventario');
    }

    public function reservas(): StreamedResponse
    {
        return $this->csv('reservas');
    }

    private function csv(string $report, ?string $filename = null, ?Carbon $generatedAt = null): StreamedResponse
    {
        $generatedAt ??= now();
        $filename ??= $this->filename($report, 'csv', $generatedAt);

        return response()->streamDownload(function () use ($report, $filename, $generatedAt) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Archivo', $filename]);
            fputcsv($out, ['Generado', $generatedAt->format('Y-m-d H:i:s T')]);
            fputcsv($out, []);
            fputcsv($out, $this->headers($report));

            foreach ($this->rows($report) as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function excel(string $report, string $filename, Carbon $generatedAt): StreamedResponse
    {
        return response()->streamDownload(function () use ($report, $filename, $generatedAt) {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(ucfirst($report));
            $sheet->setCellValue('A1', 'Archivo');
            $sheet->setCellValue('B1', $filename);
            $sheet->setCellValue('A2', 'Generado');
            $sheet->setCellValue('B2', $generatedAt->format('Y-m-d H:i:s T'));
            $sheet->fromArray($this->headers($report), null, 'A4');

            $rowNumber = 5;
            foreach ($this->rows($report) as $row) {
                $sheet->fromArray($row, null, 'A'.$rowNumber);
                $rowNumber++;
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function pdf(string $report, string $filename, Carbon $generatedAt): Response
    {
        return Pdf::loadView('reportes.pdf', [
            'title' => $this->title($report),
            'filename' => $filename,
            'generatedAt' => $generatedAt,
            'headers' => $this->headers($report),
            'rows' => $this->rows($report)->all(),
        ])
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function headers(string $report): array
    {
        return match ($report) {
            'prestamos' => [
                'Prestamo', 'Usuario', 'Identificacion', 'Telefono', 'Carrera', 'Semestre',
                'Codigo de barras', 'Titulo', 'Fecha prestamo', 'Fecha limite',
                'Fecha devolucion', 'Multa', 'Multa pagada',
            ],
            'inventario' => [
                'Codigo de barras', 'Signatura', 'Titulo', 'ISBN', 'Editorial', 'Coleccion', 'Edicion',
                'Anio', 'Tipo', 'Ubicacion', 'Estado', 'Fecha adquisicion', 'Factura', 'Valor',
                'Numero de entrada', 'Paginas', 'Medio adquisicion', 'Recibido por',
                'Fecha ingreso', 'Palabras clave',
            ],
            'reservas' => [
                'Reserva', 'Usuario', 'Identificacion', 'Telefono', 'Carrera', 'Semestre',
                'Codigo de barras', 'Titulo', 'Fecha retiro', 'Estado',
            ],
        };
    }

    private function rows(string $report): \Illuminate\Support\LazyCollection
    {
        return match ($report) {
            'prestamos' => Prestamo::with(['user', 'ejemplares.libro'])
                ->lazyById(200)
                ->flatMap(fn (Prestamo $loan) => $loan->ejemplares->map(fn (Ejemplar $copy) => [
                    $loan->id,
                    $loan->user?->name,
                    $loan->user?->numero_identificacion,
                    $loan->user?->telefono,
                    $loan->user?->carrera,
                    $loan->user?->semestre,
                    $copy->codigo_barras,
                    $copy->libro?->titulo,
                    $loan->fecha_prestamo?->toDateString(),
                    $loan->fecha_limite?->toDateString(),
                    $loan->fecha_devolucion?->toDateString(),
                    $loan->multa,
                    $loan->multa_pagada ? 'Si' : 'No',
                ])),
            'inventario' => Ejemplar::with(['libro.editorial', 'ubicacion'])
                ->lazyById(200)
                ->map(fn (Ejemplar $copy) => [
                    $copy->codigo_barras,
                    $copy->signatura,
                    $copy->libro?->titulo,
                    $copy->libro?->isbn,
                    $copy->libro?->editorial?->nombre,
                    Ejemplar::COLECCIONES[$copy->coleccion] ?? $copy->coleccion,
                    $copy->edicion,
                    $copy->anio_edicion,
                    $copy->tipo_material,
                    $copy->ubicacion?->nombre,
                    $copy->estado,
                    $copy->fecha_adquisicion?->toDateString(),
                    $copy->numero_factura,
                    $copy->valor,
                    $copy->numero_entrada,
                    $copy->paginas,
                    $copy->medio_adquisicion,
                    $copy->recibido_por,
                    $copy->fecha_ingreso?->toDateString(),
                    $copy->libro?->palabras_clave,
                ]),
            'reservas' => Reserva::with(['user', 'ejemplar.libro'])
                ->lazyById(200)
                ->map(fn (Reserva $reservation) => [
                    $reservation->id,
                    $reservation->user?->name,
                    $reservation->user?->numero_identificacion,
                    $reservation->user?->telefono,
                    $reservation->user?->carrera,
                    $reservation->user?->semestre,
                    $reservation->ejemplar?->codigo_barras,
                    $reservation->ejemplar?->libro?->titulo,
                    $reservation->fecha_retiro?->toDateString(),
                    $reservation->estado,
                ]),
        };
    }

    private function filename(string $report, string $extension, Carbon $generatedAt): string
    {
        return $report.'_'.$generatedAt->format('Ymd_His').'.'.$extension;
    }

    private function title(string $report): string
    {
        return match ($report) {
            'prestamos' => 'Reporte de préstamos',
            'inventario' => 'Reporte de inventario',
            'reservas' => 'Reporte de reservas',
        };
    }
}
