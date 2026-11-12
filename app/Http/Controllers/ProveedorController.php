<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ]);
        $search = trim($filters['q'] ?? '');
        $estado = $filters['estado'] ?? '';
        $query = Proveedor::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('contacto', 'like', "%{$search}%")
                    ->orWhere('correo', 'like', "%{$search}%");
            }))
            ->when($estado !== '', fn ($query) => $query->where('activo', $estado === 'activo'));

        return view('adquisiciones.proveedores', [
            'proveedores' => $query->withCount('solicitudesCompra')
                ->orderBy('nombre')
                ->paginate(25)
                ->withQueryString(),
            'search' => $search,
            'estado' => $estado,
        ]);
    }

    public function store(Request $request)
    {
        Proveedor::create($this->validated($request));

        return back()->with('status', 'Proveedor registrado.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $proveedor->update($this->validated($request));

        return back()->with('status', 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        if ($proveedor->solicitudesCompra()->exists()) {
            throw ValidationException::withMessages([
                'proveedor' => 'Este proveedor ya tiene solicitudes asociadas; desactívalo en lugar de eliminarlo.',
            ]);
        }

        $proveedor->delete();

        return back()->with('status', 'Proveedor eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'contacto' => ['nullable', 'string', 'max:120'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]) + ['activo' => $request->boolean('activo')];
    }
}
