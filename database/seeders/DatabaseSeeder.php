<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Personal de la biblioteca
    User::factory()->create([
        'name' => 'Bibliotecario Demo',
        'email' => 'test@example.com',
        'tipo_identificacion' => 'CC',
        'numero_identificacion' => '1234567890',
        'rol' => 'administrador',
    ]);

    // Usuario final para probar reservas
    User::factory()->create([
        'name' => 'Estudiante Demo',
        'email' => 'estudiante@example.com',
        'tipo_identificacion' => 'CC',
        'numero_identificacion' => '1000000001',
    ]);

    // Orden importa por las llaves foráneas
    $this->call([
        EditorialSeeder::class,
        GeneroSeeder::class,
        LibroSeeder::class,
        UbicacionSeeder::class,
        AutorSeeder::class,
        EjemplarSeeder::class,
    ]);
}
}