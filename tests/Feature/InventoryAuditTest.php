<?php

namespace Tests\Feature;

use App\Models\Editorial;
use App\Models\Genero;
use App\Models\InventoryMovement;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Ubicacion;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class InventoryAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_kardex_records_availability_changes_without_double_counting_reservation_delivery(): void
    {
        $inventory = app(InventoryService::class);
        $book = $this->createBook();
        $copy = $inventory->createExemplar($book, ['codigo_barras' => 'AUD-001']);
        $user = User::factory()->create();

        $reservation = Reserva::create([
            'user_id' => $user->id,
            'ejemplar_id' => $copy->id,
            'fecha_retiro' => today(),
            'estado' => 'activa',
        ]);
        $inventory->changeStatus($copy, 'reservado', $reservation);

        $loan = Prestamo::create([
            'user_id' => $user->id,
            'fecha_prestamo' => today(),
            'fecha_limite' => today()->addDays(7),
        ]);
        $inventory->changeStatus($copy, 'prestado', $loan);
        $inventory->changeStatus($copy, 'disponible', $loan, 'Ejemplar devuelto.');

        $reservationEvent = $copy->historyEvents()->where('tipo', 'reserva')->firstOrFail();
        $this->assertStringContainsString($user->name, $reservationEvent->descripcion);
        $this->assertStringContainsString($user->numero_identificacion, $reservationEvent->descripcion);
        $this->assertSame($reservation->id, $reservationEvent->referencia_id);

        $loanEvent = $copy->historyEvents()->where('tipo', 'prestamo')->firstOrFail();
        $this->assertStringContainsString('Fecha límite: '.today()->addDays(7)->format('d/m/Y'), $loanEvent->descripcion);
        $this->assertSame($loan->id, $loanEvent->referencia_id);

        $movements = InventoryMovement::query()->where('libro_id', $book->id)->orderBy('id')->get();

        $this->assertSame(['inventario_inicial', 'reserva', 'devolucion'], $movements->pluck('concept')->all());
        $this->assertSame([1, 0, 1], $movements->pluck('balance')->all());
        $this->assertSame(1, $book->fresh()->stock);
    }

    public function test_ejemplar_history_records_initial_details_and_metadata_changes(): void
    {
        $inventory = app(InventoryService::class);
        $book = $this->createBook();
        $location = Ubicacion::create(['nombre' => 'Estante A']);
        $copy = $inventory->createExemplar($book, [
            'codigo_barras' => 'HIST-001',
            'signatura' => '005.1 MAR',
            'ubicacion_id' => $location->id,
            'coleccion' => 'general',
        ]);

        $createdEvent = $copy->historyEvents()->where('tipo', 'alta')->firstOrFail();
        $this->assertStringContainsString('Signatura: 005.1 MAR', $createdEvent->descripcion);
        $this->assertStringContainsString('Ubicación: Estante A', $createdEvent->descripcion);
        $this->assertSame('disponible', $createdEvent->estado_nuevo);

        $inventory->updateExemplar($copy, [
            'signatura' => '005.2 MAR',
            'numero_entrada' => 'INV-204',
            'fecha_adquisicion' => '2024-03-10',
        ]);

        $updatedEvent = $copy->historyEvents()->where('tipo', 'actualizacion_datos')->firstOrFail();
        $this->assertStringContainsString('Signatura: 005.1 MAR → 005.2 MAR', $updatedEvent->descripcion);
        $this->assertStringContainsString('Número de entrada: Sin dato → INV-204', $updatedEvent->descripcion);
        $this->assertStringContainsString('Fecha de adquisición: Sin dato → 10/03/2024', $updatedEvent->descripcion);
    }

    public function test_adjustments_that_change_available_stock_require_a_reason(): void
    {
        $inventory = app(InventoryService::class);
        $book = $this->createBook();
        $copy = $inventory->createExemplar($book, ['codigo_barras' => 'AUD-002']);

        try {
            $inventory->changeStatus($copy, 'danado');
            $this->fail('An adjustment without a reason should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('motivo', $exception->errors());
        }

        $this->assertSame('disponible', $copy->fresh()->estado);
        $this->assertSame(1, $book->fresh()->stock);
        $this->assertDatabaseCount('inventory_movements', 1);
    }

    public function test_audit_logs_exclude_derived_stock_and_user_password(): void
    {
        $book = $this->createBook();
        $book->update(['stock' => 99, 'titulo' => 'Título actualizado']);

        $user = User::factory()->create();
        $userActivity = Activity::query()
            ->where('subject_type', $user->getMorphClass())
            ->where('subject_id', $user->id)
            ->where('log_name', 'usuarios')
            ->firstOrFail();
        $bookActivity = Activity::query()
            ->where('subject_type', $book->getMorphClass())
            ->where('subject_id', $book->id)
            ->where('event', 'updated')
            ->firstOrFail();

        $this->assertArrayNotHasKey('password', $userActivity->attribute_changes->get('attributes', []));
        $this->assertSame(['titulo' => 'Título actualizado'], $bookActivity->attribute_changes->get('attributes'));
    }

    private function createBook(): Libro
    {
        $editorial = Editorial::create(['nombre' => 'Editorial Auditoría']);
        $genre = Genero::create(['nombre' => 'Género Auditoría']);

        return Libro::create([
            'titulo' => 'Libro de auditoría',
            'stock' => 0,
            'editorial_id' => $editorial->id,
            'genero_id' => $genre->id,
        ]);
    }
}
