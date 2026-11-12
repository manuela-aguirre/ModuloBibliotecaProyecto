<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CaducarReservas extends Command
{
    protected $signature = 'reservas:caducar';

    protected $description = 'Marca como caducadas las reservas no retiradas y libera sus ejemplares';

    public function handle(InventoryService $inventory): int
    {
        $total = 0;

        // get() y no each(): cambiamos "estado" mientras recorremos
        Reserva::activas()->whereDate('fecha_retiro', '<', today())->with('ejemplar')->get()
            ->each(function (Reserva $reserva) use (&$total, $inventory) {
                DB::transaction(function () use ($reserva, $inventory) {
                    $reserva->update(['estado' => 'caducada']);
                    app(\App\Services\WaitlistService::class)->cancelForReservation($reserva);
                    $inventory->changeStatus($reserva->ejemplar, 'disponible', $reserva, 'Reserva vencida.');
                });
                $total++;
            });

        $this->info("Reservas caducadas: {$total}");

        return self::SUCCESS;
    }
}