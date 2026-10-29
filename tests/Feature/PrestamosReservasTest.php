<?php

namespace Tests\Feature;

use App\Models\Editorial;
use App\Models\Ejemplar;
use App\Models\Genero;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestamosReservasTest extends TestCase
{
    use RefreshDatabase;

    private function libroConEjemplares(int $cantidad): Libro
    {
        $editorial = Editorial::create(['nombre' => 'Editorial Prueba']);
        $genero = Genero::create(['nombre' => 'Género Prueba']);

        $libro = Libro::create([
            'titulo' => 'Libro de prueba',
            'stock' => 0,
            'editorial_id' => $editorial->id,
            'genero_id' => $genero->id,
        ]);

        for ($i = 1; $i <= $cantidad; $i++) {
            Ejemplar::create(['libro_id' => $libro->id, 'codigo_barras' => "TEST-$i"]);
        }

        return $libro->fresh();
    }

    public function test_usuario_reserva_un_ejemplar_disponible(): void
    {
        $user = User::factory()->create();
        $libro = $this->libroConEjemplares(1);

        $this->actingAs($user)
            ->post(route('reservas.store', $libro), ['fecha_retiro' => today()->toDateString()])
            ->assertRedirect(route('reservas.index'));

        $this->assertDatabaseHas('reservas', ['user_id' => $user->id, 'estado' => 'activa']);
        $this->assertSame('reservado', Ejemplar::first()->estado);
        $this->assertSame(0, $libro->fresh()->stock);
    }

    public function test_no_se_puede_reservar_sin_ejemplares_disponibles(): void
    {
        $user = User::factory()->create();
        $libro = $this->libroConEjemplares(0);

        $this->actingAs($user)
            ->post(route('reservas.store', $libro), ['fecha_retiro' => today()->toDateString()])
            ->assertSessionHasErrors('reserva');

        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_usuario_comun_no_accede_a_la_gestion_de_prestamos(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('prestamos.index'))
            ->assertForbidden();
    }

    public function test_devolucion_tardia_calcula_la_multa(): void
    {
        config(['biblioteca.smdlv' => 1000, 'biblioteca.multa_porcentaje_dia' => 1]);

        $admin = User::factory()->create(['rol' => 'administrador']);
        $usuario = User::factory()->create();
        $ejemplar = $this->libroConEjemplares(1)->ejemplares()->first();
        $ejemplar->update(['estado' => 'prestado']);

        $prestamo = Prestamo::create([
            'user_id' => $usuario->id,
            'fecha_prestamo' => today()->subDays(10),
            'fecha_limite' => today()->subDays(3),
        ]);
        $prestamo->ejemplares()->attach($ejemplar->id, ['estado' => 'prestado']);

        $this->actingAs($admin)
            ->patch(route('prestamos.devolver', $prestamo), ['condicion' => 'devuelto'])
            ->assertSessionHasNoErrors();

        // 3 días × $1.000 × 1 % = $30
        $this->assertEquals(30.0, (float) $prestamo->fresh()->multa);
        $this->assertSame('disponible', $ejemplar->fresh()->estado);
    }

    public function test_reserva_vencida_se_caduca_y_libera_el_ejemplar(): void
    {
        $user = User::factory()->create();
        $ejemplar = $this->libroConEjemplares(1)->ejemplares()->first();
        $ejemplar->update(['estado' => 'reservado']);

        Reserva::create([
            'user_id' => $user->id,
            'ejemplar_id' => $ejemplar->id,
            'fecha_retiro' => today()->subDay(),
            'estado' => 'activa',
        ]);

        $this->artisan('reservas:caducar')->assertSuccessful();

        $this->assertSame('caducada', Reserva::first()->estado);
        $this->assertSame('disponible', $ejemplar->fresh()->estado);
    }
}