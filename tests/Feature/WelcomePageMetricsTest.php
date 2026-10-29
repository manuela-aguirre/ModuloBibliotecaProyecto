<?php

namespace Tests\Feature;

use App\Models\Editorial;
use App\Models\Genero;
use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_uses_data_driven_metrics(): void
    {
        $editorial = Editorial::create(['nombre' => 'Editorial Prueba']);
        $genero = Genero::create(['nombre' => 'Género Prueba']);

        // Dos libros con stock saludable (> 2) y uno con stock crítico
        foreach ([5, 5, 1] as $i => $stock) {
            Libro::create([
                'titulo' => 'Libro '.($i + 1),
                'stock' => $stock,
                'editorial_id' => $editorial->id,
                'genero_id' => $genero->id,
            ]);
        }

        // 3 registros; 2 de 3 con stock saludable = 66.7 %
        $this->get('/')
            ->assertOk()
            ->assertSee('<strong>3</strong>Registros', false)
            ->assertSee('<strong>66.7%</strong>Disponibilidad', false)
            ->assertDontSee('12.4k');
    }
}