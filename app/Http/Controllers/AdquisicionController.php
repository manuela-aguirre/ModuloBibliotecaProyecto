<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Proveedor;
use App\Models\SolicitudCompra;
use App\Models\Ubicacion;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdquisicionController extends Controller
{
    public function index()
    {
        return view('adquisiciones.index', [
            'solicitudes' => SolicitudCompra::with(['libro', 'proveedor', 'solicitante'])
                ->latest()
                ->paginate(20),
            'libros' => Libro::orderBy('titulo')->get(['id', 'titulo', 'isbn']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
            'colecciones' => Ejemplar::COLECCIONES,
            'estados' => SolicitudCompra::ESTADOS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libro_id' => ['required', 'exists:libros,id'],
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('activo', true)],
            'ubicacion_id' => ['nullable', 'exists:ubicaciones,id'],
            'cantidad' => ['required', 'integer', 'between:1,500'],
            'costo_unitario' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'coleccion' => ['required', Rule::in(array_keys(Ejemplar::COLECCIONES))],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['solicitado_por'] = $request->user()->id;
        $solicitud = SolicitudCompra::create($data);

        return redirect()->route('adquisiciones.index')
            ->with('status', "Solicitud #{$solicitud->id} registrada.");
    }

    public function actualizarEstado(
        Request $request,
        SolicitudCompra $solicitud,
        InventoryService $inventory,
    ) {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['aprobada', 'recibida', 'cancelada'])],
        ]);

        DB::transaction(function () use ($request, $solicitud, $data, $inventory): void {
            $solicitud = SolicitudCompra::query()->lockForUpdate()->findOrFail($solicitud->id);
            $estadoAnterior = $solicitud->estado;
            $nuevoEstado = $data['estado'];

            if ($estadoAnterior === 'solicitada' && $nuevoEstado === 'aprobada') {
                $solicitud->update([
                    'estado' => 'aprobada',
                    'aprobado_por' => $request->user()->id,
                    'aprobada_at' => now(),
                ]);
            } elseif ($estadoAnterior === 'aprobada' && $nuevoEstado === 'recibida') {
                $solicitud->update([
                    'estado' => 'recibida',
                    'recibido_por' => $request->user()->id,
                    'recibida_at' => now(),
                ]);

                $libro = Libro::query()->lockForUpdate()->findOrFail($solicitud->libro_id);
                for ($numero = 1; $numero <= $solicitud->cantidad; $numero++) {
                    $inventory->createExemplar($libro, [
                        'ubicacion_id' => $solicitud->ubicacion_id,
                        'codigo_barras' => sprintf('ADQ%d-%03d', $solicitud->id, $numero),
                        'tipo_material' => 'Libro',
                        'coleccion' => $solicitud->coleccion,
                        'numero_entrada' => 'ADQ-'.$solicitud->id,
                        'numero_factura' => null,
                        'valor' => $solicitud->costo_unitario,
                        'fecha_adquisicion' => today()->toDateString(),
                        'fecha_ingreso' => today()->toDateString(),
                        'medio_adquisicion' => 'compra',
                        'recibido_por' => $request->user()->name,
                        'estado' => 'disponible',
                    ], false);
                }
                DB::afterCommit(fn () => app(\App\Services\WaitlistService::class)->assignPending($inventory));
            } elseif ($estadoAnterior === 'solicitada' && $nuevoEstado === 'cancelada'
                || $estadoAnterior === 'aprobada' && $nuevoEstado === 'cancelada') {
                $solicitud->update(['estado' => 'cancelada']);
            } else {
                throw ValidationException::withMessages([
                    'estado' => 'El cambio de estado no está permitido para esta solicitud.',
                ]);
            }

            activity('adquisiciones')
                ->performedOn($solicitud)
                ->causedBy($request->user())
                ->event('updated')
                ->withChanges([
                    'old' => ['estado' => $estadoAnterior],
                    'attributes' => ['estado' => $nuevoEstado],
                ])
                ->log('estado de solicitud de compra modificado');
        });

        $mensaje = $data['estado'] === 'recibida'
            ? "Solicitud #{$solicitud->id} recibida; se registraron {$solicitud->cantidad} ejemplares."
            : 'Estado de la solicitud actualizado.';

        return back()->with('status', $mensaje);
    }
}
