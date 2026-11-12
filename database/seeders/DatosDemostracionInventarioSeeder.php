<?php

namespace Database\Seeders;

use App\Models\Ejemplar;
use App\Models\Libro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatosDemostracionInventarioSeeder extends Seeder
{
    public function run(): void
    {
        Libro::query()->with('ejemplares')->each(function (Libro $libro): void {
            if (blank($libro->palabras_clave) && filled($keywords = $this->keywordsFor($libro->titulo))) {
                $libro->update(['palabras_clave' => $keywords]);
            }

            foreach ($libro->ejemplares as $ejemplar) {
                $fechaDemo = now()->subDays(30 + ($ejemplar->id % 180))->toDateString();
                $datosDemo = [
                    'edicion' => '1.ª edición (DEMO)',
                    'anio_edicion' => 2016 + ($ejemplar->id % 10),
                    'fecha_adquisicion' => $fechaDemo,
                    'numero_factura' => 'DEMO-FAC-'.str_pad((string) $ejemplar->id, 5, '0', STR_PAD_LEFT),
                    'valor' => 85000 + ($ejemplar->id * 12500),
                    'numero_entrada' => 'DEMO-ENT-'.str_pad((string) $ejemplar->id, 5, '0', STR_PAD_LEFT),
                    'paginas' => 120 + (($ejemplar->id * 17) % 400),
                    'medio_adquisicion' => 'compra',
                    'recibido_por' => 'Bibliotecario Demo',
                    'fecha_ingreso' => $fechaDemo,
                ];

                $cambios = [];
                foreach ($datosDemo as $campo => $valor) {
                    if (blank($ejemplar->{$campo})) {
                        $cambios[$campo] = $valor;
                    }
                }

                if ($cambios !== []) {
                    $ejemplar->update($cambios);
                }
            }
        });
    }

    private function keywordsFor(string $title): string
    {
        $words = collect(explode(' ', Str::lower(Str::ascii($title))))
            ->filter(fn (string $word) => mb_strlen($word) > 3)
            ->unique()
            ->values();

        return $words->implode(', ');
    }
}
