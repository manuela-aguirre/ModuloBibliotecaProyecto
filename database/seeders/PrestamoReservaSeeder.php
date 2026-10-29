<?php

namespace Database\Seeders;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Datos de prueba de préstamos y reservas, uno por cada situación que maneja el sistema.
 * Todos los usuarios creados tienen la contraseña "password".
 */
class PrestamoReservaSeeder extends Seeder
{
    private $pool;
    private User $admin;

    public function run(): void
    {
        if (Prestamo::exists() || Reserva::exists()) {
            $this->command?->warn('Ya hay préstamos o reservas; no se crean datos de prueba.');

            return;
        }

        $this->admin = User::where('rol', 'administrador')->firstOrFail();

        // Primero un ejemplar por libro distinto (casos más variados); si faltan, se completa con otros
        $todos = Ejemplar::disponibles()->orderBy('id')->get();
        $unicos = $todos->unique('libro_id');
        $resto = $todos->reject(fn ($e) => $unicos->contains('id', $e->id));
        $this->pool = $unicos->concat($resto)->values();

        if ($this->pool->count() < 13) {
            throw new RuntimeException('Se necesitan al menos 13 ejemplares disponibles (hay '.$this->pool->count().').');
        }

        $sistemas = 'Tecnología en Sistemas de Información';
        $programacion = 'Técnico Profesional en Programación de Aplicaciones Informáticas';

        $demo = User::where('email', 'estudiante@example.com')->first()
            ?? $this->usuario('Estudiante Demo', 'estudiante@example.com', '1000000001', $sistemas, 4);

        $vencido = $this->usuario('Laura Vencida', 'laura.vencido@example.com', '1000000002', $sistemas, 5);
        $puntual = $this->usuario('Pedro Puntual', 'pedro.puntual@example.com', '1000000003', $programacion, 2);
        $multa = $this->usuario('Marta Multa', 'marta.multa@example.com', '1000000004', $programacion, 3);
        $pagada = $this->usuario('Pablo Pagó', 'pablo.pago@example.com', '1000000005', $sistemas, 6);
        $danado = $this->usuario('Daniela Daño', 'daniela.dano@example.com', '1000000006', $sistemas, 1);
        $reservas = $this->usuario('Rosa Reservas', 'rosa.reservas@example.com', '1000000007', $programacion, 4);
        $porVencer = $this->usuario('Víctor Vence Pronto', 'victor.porvencer@example.com', '1000000008', $sistemas, 7);

        // Estudiante demo: un préstamo en plazo y una reserva activa (usa 2 de 3 cupos)
        $this->prestamo($demo, today()->subDays(3), 8);
        $this->reserva($demo, 'activa', today()->addDays(2));

        // Préstamo vencido sin devolver (bloquea nuevas reservas y préstamos)
        $this->prestamo($vencido, today()->subDays(15), 8, null, 'devuelto', false, 'Vencido: sin devolver');

        // Devueltos a tiempo
        $this->prestamo($puntual, today()->subDays(20), 8, today()->subDays(14));
        $this->prestamo($puntual, today()->subDays(40), 15, today()->subDays(30));

        // Devuelto tarde: multa pendiente y multa pagada
        $this->prestamo($multa, today()->subDays(25), 8, today()->subDays(10), 'devuelto', false, 'Devuelto con 7 días de retraso');
        $this->prestamo($pagada, today()->subDays(45), 8, today()->subDays(30), 'devuelto', true, 'Multa pagada en caja');

        // Devuelto con daños (el ejemplar queda en estado "danado")
        $this->prestamo($danado, today()->subDays(12), 8, today()->subDays(6), 'danado', false, 'Páginas rotas');

        // Por vencer: la fecha límite es mañana
        $this->prestamo($porVencer, today()->subDays(7), 8);

        // Un usuario con las cuatro situaciones de reserva
        $this->reserva($reservas, 'activa', today()->addDay());
        $this->reserva($reservas, 'cancelada', today()->subDays(2));
        $this->reserva($reservas, 'caducada', today()->subDays(5));

        $ejemplar = $this->tomar();
        $retirada = $this->reserva($reservas, 'retirada', today()->subDays(4), $ejemplar);
        $this->prestamo($reservas, today()->subDays(4), 8, null, 'devuelto', false, 'Entrega de reserva #'.$retirada->id, $ejemplar);

        // DatabaseSeeder usa WithoutModelEvents: se recalcula el stock a mano
        Libro::all()->each->sincronizarStock();

        $this->command?->info('Datos de prueba de préstamos y reservas creados.');
    }

    private function usuario(string $nombre, string $email, string $doc, string $carrera, int $semestre): User
    {
        return User::factory()->create([
            'name' => $nombre,
            'email' => $email,
            'tipo_identificacion' => 'CC',
            'numero_identificacion' => $doc,
            'telefono' => '300'.substr($doc, -7),
            'carrera' => $carrera,
            'semestre' => $semestre,
        ]);
    }

    private function tomar(): Ejemplar
    {
        return $this->pool->shift();
    }

    private function prestamo(
        User $user,
        Carbon $inicio,
        int $dias,
        ?Carbon $devuelto = null,
        string $condicion = 'devuelto',
        bool $multaPagada = false,
        ?string $observaciones = null,
        ?Ejemplar $ejemplar = null,
    ): Prestamo {
        $ejemplar ??= $this->tomar();
        $limite = $inicio->copy()->addDays($dias);

        $multa = 0;
        if ($devuelto && $devuelto->gt($limite)) {
            $smdlv = config('biblioteca.smdlv') ?: 47450;
            $multa = round($limite->diffInDays($devuelto) * $smdlv * config('biblioteca.multa_porcentaje_dia') / 100, 2);
        }

        $prestamo = Prestamo::create([
            'user_id' => $user->id,
            'registrado_por' => $this->admin->id,
            'fecha_prestamo' => $inicio,
            'fecha_limite' => $limite,
            'fecha_devolucion' => $devuelto,
            'multa' => $multa,
            'multa_pagada' => $multa > 0 && $multaPagada,
            'observaciones' => $observaciones,
        ]);

        $prestamo->ejemplares()->attach($ejemplar->id, ['estado' => $devuelto ? $condicion : 'prestado']);

        $ejemplar->update(['estado' => match (true) {
            ! $devuelto => 'prestado',
            $condicion === 'danado' => 'danado',
            default => 'disponible',
        }]);

        return $prestamo;
    }

private function reserva(User $user, string $estado, Carbon $fechaRetiro, ?Ejemplar $ejemplar = null): Reserva
    {
        $ejemplar ??= $this->tomar();

        $reserva = Reserva::create([
            'user_id' => $user->id,
            'ejemplar_id' => $ejemplar->id,
            'fecha_retiro' => $fechaRetiro,
            'estado' => $estado,
        ]);

        $nuevoEstado = match ($estado) {
            'activa' => 'reservado',
            'retirada' => 'prestado',
            default => 'disponible',
        };

        $ejemplar->update(['estado' => $nuevoEstado]);

        return $reserva;
    }
}
