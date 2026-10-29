<?php

namespace App\Http\Controllers;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class EjemplarController extends Controller
{
    public function index(Libro $libro)
    {
        $libro->load('ejemplares.ubicacion');

        return view('ejemplares.index', compact('libro'));
    }

    public function create(Libro $libro)
    {
        return view('ejemplares.create', [
            'libro' => $libro,
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, Libro $libro)
    {
        $data = $request->validate(Arr::except($this->rules(), 'estado'));

        $libro->ejemplares()->create($data);

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

    public function update(Request $request, Ejemplar $ejemplar)
    {
        $rules = $this->rules($ejemplar);

        // Si está prestado o reservado, el estado lo controla el flujo de préstamos/reservas.
        if (in_array($ejemplar->estado, ['prestado', 'reservado'])) {
            unset($rules['estado']);
        }

        $ejemplar->update($request->validate($rules));

        return redirect()->route('libros.ejemplares.index', $ejemplar->libro)->with('status', 'Ejemplar actualizado.');
    }

    public function destroy(Ejemplar $ejemplar)
    {
        if (in_array($ejemplar->estado, ['prestado', 'reservado']) || $ejemplar->prestamos()->exists()) {
            return back()->withErrors([
                'estado' => 'Este ejemplar está en uso o tiene historial de préstamos. Márcalo como "baja" en lugar de eliminarlo.',
            ]);
        }

        $libro = $ejemplar->libro;
        $ejemplar->delete();

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
            'numero_factura' => ['nullable', 'string', 'max:50'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['disponible', 'danado', 'baja'])],
        ];
    }
}