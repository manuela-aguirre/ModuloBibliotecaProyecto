<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Estantería A - Nivel 1', 'Estantería A - Nivel 2', 'Estantería B - Nivel 1',
                  'Estantería B - Nivel 2', 'Colección de reserva'] as $nombre) {
            Ubicacion::create(['nombre' => $nombre]);
        }
    }
}