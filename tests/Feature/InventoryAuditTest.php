<?php

namespace Tests\Feature;

use App\Models\Editorial;
use App\Models\Genero;
use App\Models\InventoryMovement;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\Reserva;
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

        $movements = InventoryMovement::query()->where('libro_id', $book->id)->orderBy('id')->get();

        $this->assertSame(['inventario_inicial', 'reserva', 'devolucion'], $movements->pluck('concept')->all());
        $this->assertSame([1, 0, 1], $movements->pluck('balance')->all());
        $this->assertSame(1, $book->fresh()->stock);
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
