<?php

namespace App\Console\Commands;

use App\Models\Libro;
use App\Services\InventoryService;
use Illuminate\Console\Command;

class InitializeKardex extends Command
{
    protected $signature = 'kardex:inicializar';

    protected $description = 'Registra el saldo disponible actual de cada libro como inventario inicial del kardex';

    public function handle(InventoryService $inventory): int
    {
        $count = 0;

        Libro::query()->orderBy('id')->each(function (Libro $libro) use ($inventory, &$count) {
            if ($inventory->initializeOpening($libro)) {
                $count++;
            }
        });

        $this->info("Libros inicializados en el kardex: {$count}");

        return self::SUCCESS;
    }
}
