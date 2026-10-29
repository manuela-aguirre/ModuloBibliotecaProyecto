<?php

namespace Tests\Feature;

use App\Models\Prestamo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglamentoYSeguridadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'administrador']);
    }

    private function prestamoConMulta(float $multa = 100): Prestamo
    {
        return Prestamo::create([
            'user_id' => User::factory()->create()->id,
            'fecha_prestamo' => today()->subDays(20),
            'fecha_limite' => today()->subDays(10),
            'fecha_devolucion' => today(),
            'multa' => $multa,
        ]);
    }

    /** RNF-03 */
    public function test_registro_guarda_la_fecha_de_autorizacion_de_datos(): void
    {
        $this->post('/register', [
            'name' => 'Ana Prueba', 'tipo_identificacion' => 'CC', 'numero_identificacion' => '999',
            'carrera' => 'Sistemas', 'semestre' => 3, 'acepta_datos' => '1',
            'email' => 'ana@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertNotNull(User::where('email', 'ana@test.com')->first()->datos_aceptados_at);
    }

    public function test_politica_de_datos_es_publica(): void
    {
        $this->get(route('politica.datos'))->assertOk()->assertSee('Ley 1581 de 2012');
    }

    /** Art. 11 del Reglamento */
    public function test_condonacion_total_deja_la_multa_en_cero(): void
    {
        $p = $this->prestamoConMulta(100);

        $this->actingAs($this->admin())
            ->patch(route('prestamos.condonar', $p), ['motivo' => 'calamidad'])
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertEquals(0.0, (float) $p->multa);
        $this->assertEquals(100.0, (float) $p->multa_original);
        $this->assertSame(100, (int) $p->condonacion_porcentaje);
    }

    public function test_condonacion_parcial_reduce_la_multa_a_la_mitad(): void
    {
        $p = $this->prestamoConMulta(100);

        $this->actingAs($this->admin())->patch(route('prestamos.condonar', $p), ['motivo' => 'capacitacion']);

        $this->assertEquals(50.0, (float) $p->fresh()->multa);
    }

    public function test_una_multa_no_se_condona_dos_veces(): void
    {
        $p = $this->prestamoConMulta(100);
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('prestamos.condonar', $p), ['motivo' => 'capacitacion']);
        $this->actingAs($admin)->patch(route('prestamos.condonar', $p), ['motivo' => 'calamidad'])->assertStatus(422);

        $this->assertEquals(50.0, (float) $p->fresh()->multa);
    }

    public function test_usuario_comun_no_puede_condonar(): void
    {
        $p = $this->prestamoConMulta();

        $this->actingAs(User::factory()->create())
            ->patch(route('prestamos.condonar', $p), ['motivo' => 'calamidad'])
            ->assertForbidden();
    }

    /** Gestión de usuarios */
    public function test_administrador_cambia_rol_y_limite_de_un_usuario(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('usuarios.update', $usuario), ['rol' => 'administrador', 'limite_prestamos' => 5])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'rol' => 'administrador', 'limite_prestamos' => 5]);
    }

    public function test_el_administrador_no_puede_quitarse_su_propio_rol(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('usuarios.update', $admin), ['rol' => 'usuario', 'limite_prestamos' => 3])
            ->assertSessionHasErrors('rol');

        $this->assertSame('administrador', $admin->fresh()->rol);
    }

    public function test_usuario_comun_no_puede_cambiar_roles(): void
    {
        $otro = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('usuarios.update', $otro), ['rol' => 'administrador', 'limite_prestamos' => 3])
            ->assertForbidden();
    }
}
