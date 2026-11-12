<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Ubicacion;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class EjemplarController extends Controller
{
    public function index(Libro $libro)
    {
        $ejemplares = $libro->ejemplares()
            ->with('ubicacion')
            ->orderBy('codigo_barras')
            ->paginate(30)
            ->withQueryString();

        return view('ejemplares.index', compact('libro', 'ejemplares'));
    }

    public function create(Libro $libro)
    {
        return view('ejemplares.create', [
            'libro' => $libro,
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, Libro $libro, InventoryService $inventory)
    {
        $data = $request->validate(Arr::except($this->rules(), 'estado'));

        $inventory->createExemplar($libro, $data);

        return redirect()->route('libros.ejemplares.index', $libro)->with('status', 'Ejemplar registrado.');
    }

    public function edit(Ejemplar $ejemplar)
    {
        return view('ejemplares.edit', [
            'ejemplar' => $ejemplar,
            'libro' => $ejemplar->libro,
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Ejemplar $ejemplar, InventoryService $inventory)
    {
        $rules = $this->rules($ejemplar);

        // Si está prestado o reservado, el estado lo controla el flujo de préstamos/reservas.
        if (in_array($ejemplar->estado, ['prestado', 'reservado'])) {
            unset($rules['estado']);
        }

        if (isset($rules['estado']) && $request->input('estado') !== $ejemplar->estado) {
            $rules['motivo'] = ['required', 'string', 'min:10', 'max:255'];
        } else {
            $rules['motivo'] = ['nullable', 'string', 'max:255'];
        }

        $data = $request->validate($rules);
        $reason = $data['motivo'] ?? null;
        $eventType = $data['tipo_evento'] ?? null;
        unset($data['motivo']);
        unset($data['tipo_evento']);

        $ejemplar = $inventory->updateExemplar($ejemplar, $data, $reason, $eventType);

        return redirect()->route('libros.ejemplares.index', $ejemplar->libro)->with('status', 'Ejemplar actualizado.');
    }

    public function destroy(Ejemplar $ejemplar, InventoryService $inventory)
    {
        try {
            $libro = $ejemplar->libro;
            $inventory->deleteExemplar($ejemplar);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('libros.ejemplares.index', $libro)->with('status', 'Ejemplar eliminado.');
    }

    private function rules(?Ejemplar $ejemplar = null): array
    {
        return [
            'ubicacion_id' => ['nullable', 'exists:ubicaciones,id'],
            'codigo_barras' => ['required', 'string', 'max:50', Rule::unique('ejemplares', 'codigo_barras')->ignore($ejemplar?->id)],
            'signatura' => ['nullable', 'string', 'max:50'],
            'edicion' => ['nullable', 'string', 'max:50'],
            'anio_edicion' => ['nullable', 'integer', 'between:1400,'.date('Y')],
            'tipo_material' => ['required', 'string', 'max:30'],
            'coleccion' => ['required', Rule::in(array_keys(Ejemplar::COLECCIONES))],
            'numero_factura' => ['nullable', 'string', 'max:50'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'numero_entrada' => ['nullable', 'string', 'max:30'],
            'paginas' => ['nullable', 'integer', 'min:1'],
            'medio_adquisicion' => ['nullable', Rule::in(['compra', 'donacion', 'canje'])],
            'recibido_por' => ['nullable', 'string', 'max:100'],
            'fecha_ingreso' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['disponible', 'danado', 'baja'])],
            'tipo_evento' => ['nullable', Rule::in(['cambio_estado', 'reparacion', 'perdida'])],
        ];
    }
}