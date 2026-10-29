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

/**
 * Una prueba por requerimiento. Sirve de evidencia para la matriz de trazabilidad.
 */
class RequerimientosTest extends TestCase
{
    use RefreshDatabase;

    private function libro(string $titulo = 'Libro de prueba', int $ejemplares = 1): Libro
    {
        $editorial = Editorial::firstOrCreate(['nombre' => 'Editorial Prueba']);
        $genero = Genero::firstOrCreate(['nombre' => 'Género Prueba']);

        $libro = Libro::create([
            'titulo' => $titulo,
            'stock' => 0,
            'editorial_id' => $editorial->id,
            'genero_id' => $genero->id,
        ]);

        for ($i = 1; $i <= $ejemplares; $i++) {
            Ejemplar::create(['libro_id' => $libro->id, 'codigo_barras' => "BC-{$libro->id}-{$i}"]);
        }

        return $libro->fresh();
    }

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'administrador']);
    }

    /** RF-02 */
    public function test_busca_por_codigo_de_barras_signatura_y_palabras_clave(): void
    {
        $libro = $this->libro();
        $libro->ejemplares()->first()->update(['codigo_barras' => 'BC-777', 'signatura' => '005.1 ABC']);
        $libro->update(['palabras_clave' => 'algoritmos, programación']);

        foreach (['BC-777', '005.1', 'algoritmos'] as $q) {
            $this->get(route('catalogo.index', ['q' => $q]))->assertOk()->assertSee('Libro de prueba');
        }

        $this->get(route('catalogo.index', ['q' => 'inexistente-xyz']))->assertDontSee('Libro de prueba');
    }

    /** RF-04 */
    public function test_registro_guarda_telefono_carrera_y_semestre(): void
    {
        $this->post('/register', [
            'name' => 'Ana Prueba', 'tipo_identificacion' => 'CC', 'numero_identificacion' => '999',
            'telefono' => '3001112233', 'carrera' => 'Sistemas', 'semestre' => 3,
            'email' => 'ana@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'ana@test.com', 'telefono' => '3001112233', 'carrera' => 'Sistemas', 'semestre' => 3,
        ]);
    }

    public function test_registro_exige_carrera_y_semestre(): void
    {
        $this->post('/register', [
            'name' => 'Ana Prueba', 'tipo_identificacion' => 'CC', 'numero_identificacion' => '999',
            'email' => 'ana@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['carrera', 'semestre']);
    }

    public function test_perfil_actualiza_datos_academicos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name, 'email' => $user->email,
            'telefono' => '3110000000', 'carrera' => 'Programación', 'semestre' => 2,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'carrera' => 'Programación', 'semestre' => 2]);
    }

    /** RF-06 */
    public function test_ejemplar_guarda_datos_de_adquisicion(): void
    {
        $libro = $this->libro('Otro libro', 0);

        $this->actingAs($this->admin())->post(route('libros.ejemplares.store', $libro), [
            'codigo_barras' => 'NEW-1', 'tipo_material' => 'Libro', 'numero_entrada' => 'E-10',
            'paginas' => 320, 'medio_adquisicion' => 'donacion', 'recibido_por' => 'Luisa',
            'fecha_ingreso' => '2026-10-01',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ejemplares', [
            'codigo_barras' => 'NEW-1', 'numero_entrada' => 'E-10', 'paginas' => 320,
            'medio_adquisicion' => 'donacion', 'recibido_por' => 'Luisa',
        ]);
    }

    /** RF-06 y RF-07 */
    public function test_reportes_csv_incluyen_datos_del_usuario_y_del_ejemplar(): void
    {
        $usuario = User::factory()->create([
            'name' => 'Ana Prueba', 'carrera' => 'Sistemas', 'semestre' => 3, 'telefono' => '3001112233',
        ]);
        $libro = $this->libro('Libro de prueba', 2);
        [$uno, $dos] = $libro->ejemplares;

        $prestamo = Prestamo::create([
            'user_id' => $usuario->id, 'fecha_prestamo' => today(), 'fecha_limite' => today()->addDays(8),
        ]);
        $prestamo->ejemplares()->attach($uno->id, ['estado' => 'prestado']);

        Reserva::create([
            'user_id' => $usuario->id, 'ejemplar_id' => $dos->id,
            'fecha_retiro' => today()->addDay(), 'estado' => 'activa',
        ]);

        $admin = $this->admin();

        foreach (['reportes.prestamos', 'reportes.reservas'] as $ruta) {
            $csv = $this->actingAs($admin)->get(route($ruta))->assertOk()->streamedContent();
            $this->assertStringContainsString('Carrera', $csv);
            $this->assertStringContainsString('Sistemas', $csv);
            $this->assertStringContainsString('3001112233', $csv);
        }

        $inventario = $this->actingAs($admin)->get(route('reportes.inventario'))->assertOk()->streamedContent();
        $this->assertStringContainsString('Numero de entrada', $inventario);
        $this->assertStringContainsString('Palabras clave', $inventario);
    }

    public function test_reportes_solo_para_administrador(): void
    {
        $this->actingAs(User::factory()->create())->get(route('reportes.reservas'))->assertForbidden();
    }

    /** RF-09 */
    public function test_administrador_consulta_historial_por_numero_de_identificacion(): void
    {
        $usuario = User::factory()->create(['name' => 'Ana Prueba', 'numero_identificacion' => '555123']);
        $libro = $this->libro('Libro de prueba');

        $prestamo = Prestamo::create([
            'user_id' => $usuario->id, 'fecha_prestamo' => today()->subDays(5),
            'fecha_limite' => today()->addDays(3), 'fecha_devolucion' => today(),
        ]);
        $prestamo->ejemplares()->attach($libro->ejemplares->first()->id, ['estado' => 'devuelto']);

        $this->actingAs($this->admin())
            ->get(route('usuarios.historial', ['numero_identificacion' => '555123']))
            ->assertOk()->assertSee('Ana Prueba')->assertSee('Libro de prueba');

        $this->actingAs($this->admin())
            ->get(route('usuarios.historial', ['numero_identificacion' => '000']))
            ->assertOk()->assertSee('No se encontró ningún usuario');
    }

    public function test_usuario_comun_no_puede_consultar_historial_de_otros(): void
    {
        $this->actingAs(User::factory()->create())->get(route('usuarios.historial'))->assertForbidden();
    }

    /** Estadísticas de uso (ficha del proyecto) */
    public function test_dashboard_muestra_libros_mas_prestados(): void
    {
        $usuario = User::factory()->create();
        $libro = $this->libro('Libro de prueba');

        $prestamo = Prestamo::create([
            'user_id' => $usuario->id, 'fecha_prestamo' => today(), 'fecha_limite' => today()->addDays(8),
        ]);
        $prestamo->ejemplares()->attach($libro->ejemplares->first()->id, ['estado' => 'prestado']);

        $this->actingAs($this->admin())->get(route('dashboard'))
            ->assertOk()->assertSee('Libros más prestados')->assertSee('Libro de prueba');
    }
}
