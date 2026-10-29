<?php

namespace Tests\Feature;

use App\Models\Editorial;
use App\Models\Genero;
use App\Models\Libro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_books_index_handles_missing_relations(): void
    {
        $user = User::factory()->create();
        $editorial = Editorial::create(['nombre' => 'Editorial de prueba']);
        $genero = Genero::create(['nombre' => 'Género de prueba']);

        $libro = Libro::create([
            'titulo' => 'Libro sin editorial ni género',
            'descripcion' => 'Debe renderizar sin romper la vista.',
            'stock' => 4,
            'isbn' => '9780000000000',
            'editorial_id' => $editorial->id,
            'genero_id' => $genero->id,
        ]);

        $libro->setRelation('editorial', null);
        $libro->setRelation('genero', null);

        $this->actingAs($user)
            ->view('libros.index', [
                'libros' => new LengthAwarePaginator([$libro], 1, 15),
                'q' => null,
            ])
            ->assertSee('Sin editorial')
            ->assertSee('Sin género');
    }

   public function test_dashboard_shows_practical_kpis_for_analysis(): void
{
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Préstamos activos')
        ->assertSee('Stock crítico')
        ->assertSee('Tasa de vencimiento')
        ->assertSee('Uso del catálogo')
        ->assertSee('Multas pendientes');
}

public function test_usuario_comun_es_redirigido_del_dashboard_al_catalogo(): void
{
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertRedirect(route('catalogo.index'));
}
}
