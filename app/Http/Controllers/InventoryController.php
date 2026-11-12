<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdjustmentRequest;
use App\Models\Ejemplar;
use App\Models\InventoryMovement;
use App\Models\Libro;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function kardex(Request $request)
    {
        $filters = $request->validate([
            'libro_id' => ['nullable', 'integer', 'exists:libros,id'],
            'concepto' => ['nullable', 'string', 'max:40'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $libros = Libro::query()->orderBy('titulo')->get(['id', 'titulo', 'stock']);
        $libro = isset($filters['libro_id']) ? $libros->firstWhere('id', (int) $filters['libro_id']) : null;
        $movimientos = null;
        $saldoKardex = null;

        if ($libro) {
            $query = InventoryMovement::query()
                ->with(['ejemplar', 'user', 'reference'])
                ->where('libro_id', $libro->id)
                ->when($filters['concepto'] ?? null, fn ($q, $concept) => $q->where('concept', $concept))
                ->when($filters['desde'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                ->when($filters['hasta'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));

            $movimientos = (clone $query)->latest('id')->paginate(25)->withQueryString();
            $saldoKardex = $libro->movimientosInventario()->latest('id')->value('balance') ?? 0;
        }

        return view('inventory.kardex', [
            'libros' => $libros,
            'libro' => $libro,
            'movimientos' => $movimientos,
            'saldoKardex' => $saldoKardex,
            'conceptos' => InventoryMovement::CONCEPTS,
        ]);
    }

    public function adjust()
    {
        $libros = Libro::query()
            ->with(['ejemplares' => fn ($query) => $query->whereIn('estado', ['disponible', 'danado', 'baja'])->orderBy('codigo_barras')])
            ->orderBy('titulo')
            ->get();

        return view('inventory.adjust', compact('libros'));
    }

    public function storeAdjustment(StoreAdjustmentRequest $request, InventoryService $inventory)
    {
        $data = $request->validated();
        $ejemplar = Ejemplar::query()->findOrFail($data['ejemplar_id']);

        if (in_array($ejemplar->estado, ['prestado', 'reservado'], true)) {
            throw ValidationException::withMessages([
                'ejemplar_id' => 'No se puede ajustar un ejemplar prestado o reservado.',
            ]);
        }

        $inventory->changeStatus($ejemplar, $data['estado'], null, $data['motivo']);

        return redirect()
            ->route('inventory.kardex', ['libro_id' => $ejemplar->libro_id])
            ->with('status', 'Ajuste de inventario registrado.');
    }
}
