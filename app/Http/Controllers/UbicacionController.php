<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Services\EjemplarHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UbicacionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');

        return view('ubicaciones.index', [
            'ubicaciones' => Ubicacion::withCount('ejemplares')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                }))
                ->orderBy('nombre')
                ->paginate(25)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        Ubicacion::create($request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Ubicación creada.');
    }

    public function destroy(Ubicacion $ubicacion, EjemplarHistoryService $history)
    {
        DB::transaction(function () use ($ubicacion, $history): void {
            $ejemplares = $ubicacion->ejemplares()->with('libro')->get();
            foreach ($ejemplares as $ejemplar) {
                $history->record(
                    $ejemplar,
                    'cambio_ubicacion',
                    "La ubicación {$ubicacion->nombre} fue eliminada.",
                    $ejemplar->estado,
                    $ejemplar->estado,
                    $ubicacion->id,
                    null,
                );
            }

            $ubicacion->delete();
        });

        return back()->with('status', 'Ubicación eliminada.');
    }
}