<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Models\Editorial;
use App\Models\Genero;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LibroController extends Controller
{
    public function index(Request $request)
    {
        $libros = Libro::with(['editorial', 'genero', 'autores'])
            ->buscar($request->get('q'))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('libros.index', ['libros' => $libros, 'q' => $request->get('q')]);
    }

    public function create()
    {
        return view('libros.create', $this->catalogos());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $libro = Libro::create(Arr::except($validated, 'autores'));
        $libro->autores()->sync($validated['autores'] ?? []);

        return redirect()->route('libros.index')->with('status', 'Libro creado correctamente.');
    }

    public function edit(Libro $libro)
    {
        $libro->load('autores');

        return view('libros.edit', ['libro' => $libro] + $this->catalogos());
    }

    public function update(Request $request, Libro $libro)
    {
        $validated = $request->validate($this->rules());

        $libro->update(Arr::except($validated, 'autores'));
        $libro->autores()->sync($validated['autores'] ?? []);

        return redirect()->route('libros.index')->with('status', 'Libro actualizado correctamente.');
    }

    public function destroy(Libro $libro)
    {
        $libro->delete();

        return redirect()->route('libros.index')->with('status', 'Libro eliminado correctamente.');
    }

    private function catalogos(): array
    {
        return [
            'editoriales' => Editorial::orderBy('nombre')->get(),
            'generos' => Genero::orderBy('nombre')->get(),
            'autores' => Autor::orderBy('nombre')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'portada_url' => ['nullable', 'url', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:20'],
            'editorial_id' => ['required', 'exists:editorials,id'],
            'genero_id' => ['required', 'exists:generos,id'],
            'autores' => ['nullable', 'array'],
            'autores.*' => ['exists:autores,id'],
        ];
    }
}