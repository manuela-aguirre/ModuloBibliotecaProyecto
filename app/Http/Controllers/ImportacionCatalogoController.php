<?php

namespace App\Http\Controllers;

use App\Services\CatalogoImportService;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportacionCatalogoController extends Controller
{
    public function index()
    {
        return view('importaciones.catalogo');
    }

    public function store(Request $request, CatalogoImportService $importer, InventoryService $inventory)
    {
        $data = $request->validate([
            'archivo' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx,pdf'],
        ]);

        $result = $importer->import($data['archivo'], $inventory);

        return back()->with('import_result', $result)
            ->with('status', "Importación terminada: {$result['creados']} ejemplar(es) creado(s), {$result['libros_creados']} título(s) nuevo(s), {$result['duplicados']} duplicado(s) omitido(s), {$result['invalidos']} fila(s) inválida(s).");
    }

    public function plantillaCsv()
    {
        $headers = "\"Codigo de barras\",Signatura,Titulo,ISBN,Editorial,Coleccion,Edicion,Anio,Tipo,Ubicacion,Estado,\"Fecha adquisicion\",Factura,Valor,\"Numero de entrada\",Paginas,\"Medio adquisicion\",\"Recibido por\",\"Fecha ingreso\",\"Palabras clave\"\r\n";

        return Response::make("\xEF\xBB\xBF".$headers, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla-inventario.csv"',
        ]);
    }

    public function plantillaExcel(): StreamedResponse
    {
        $headers = [
            'Codigo de barras', 'Signatura', 'Titulo', 'ISBN', 'Editorial',
            'Coleccion', 'Edicion', 'Anio', 'Tipo', 'Ubicacion', 'Estado',
            'Fecha adquisicion', 'Factura', 'Valor', 'Numero de entrada',
            'Paginas', 'Medio adquisicion', 'Recibido por', 'Fecha ingreso',
            'Palabras clave',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $spreadsheet = new Spreadsheet;
            $spreadsheet->getActiveSheet()->fromArray($headers, null, 'A1');
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'plantilla-inventario.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function plantillaPdf()
    {
        return Pdf::loadView('importaciones.plantilla-pdf')
            ->setPaper('a4', 'landscape')
            ->download('plantilla-inventario-pdf.pdf');
    }
}
