<?php

namespace Database\Seeders;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Ubicacion;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class EjemplarSeeder extends Seeder
{
    public function run(InventoryService $inventory): void
    {
        $ubicaciones = Ubicacion::pluck('id');

        foreach (Libro::all() as $libro) {
            $cantidad = $libro->stock;

            for ($i = 1; $i <= $cantidad; $i++) {
                $inventory->createExemplar($libro, [
                    'ubicacion_id' => $ubicaciones->random(),
                    'codigo_barras' => sprintf('COT-%04d-%02d', $libro->id, $i),
                    'signatura' => sprintf('SIG-%04d', $libro->id),
                    'tipo_material' => 'Libro',
                    'estado' => 'disponible',
                ]);
            }

            $libro->sincronizarStock(); // DatabaseSeeder usa WithoutModelEvents
        }
    }
}