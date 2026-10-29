<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CaducarReservas extends Command
{
    protected $signature = 'reservas:caducar';

    protected $description = 'Marca como caducadas las reservas no retiradas y libera sus ejemplares';

    public function handle(): int
    {
        $total = 0;

        // get() y no each(): cambiamos "estado" mientras recorremos
        Reserva::activas()->whereDate('fecha_retiro', '<', today())->with('ejemplar')->get()
            ->each(function (Reserva $reserva) use (&$total) {
                DB::transaction(function () use ($reserva) {
                    $reserva->update(['estado' => 'caducada']);
                    $reserva->ejemplar->update(['estado' => 'disponible']);
                });
                $total++;
            });

        $this->info("Reservas caducadas: {$total}");

        return self::SUCCESS;
    }
}