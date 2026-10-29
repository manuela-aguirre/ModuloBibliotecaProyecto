<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class RespaldarBaseDeDatos extends Command
{
    protected $signature = 'biblioteca:respaldar {--dias=14 : Días que se conservan los respaldos}';

    protected $description = 'Genera un respaldo .sql de la base de datos y borra los respaldos antiguos';

    public function handle(): int
    {
        $cfg = config('database.connections.'.config('database.default'));

        if (! in_array($cfg['driver'] ?? null, ['mysql', 'mariadb'])) {
            $this->error('El respaldo solo está implementado para MySQL o MariaDB.');

            return self::FAILURE;
        }

        $buscador = new ExecutableFinder();
        $binario = collect(['mysqldump', 'mariadb-dump'])->first(fn ($b) => $buscador->find($b));

        if (! $binario) {
            $this->error('No se encontró mysqldump ni mariadb-dump en el sistema.');

            return self::FAILURE;
        }

        $carpeta = storage_path('app/backups');
        File::ensureDirectoryExists($carpeta);
        $archivo = $carpeta.'/respaldo_'.now()->format('Ymd_His').'.sql';

        $comando = [
            $binario,
            '--host='.$cfg['host'],
            '--port='.$cfg['port'],
            '--user='.$cfg['username'],
            '--single-transaction',
            '--routines',
            '--result-file='.$archivo,
        ];

        if ($binario === 'mysqldump') {
            $comando[] = '--no-tablespaces';
        }

        $comando[] = $cfg['database'];

        $proceso = new Process($comando, null, ['MYSQL_PWD' => $cfg['password']]);
        $proceso->setTimeout(300);
        $proceso->run();

        if (! $proceso->isSuccessful()) {
            File::delete($archivo);
            $this->error('Falló el respaldo: '.trim($proceso->getErrorOutput()));

            return self::FAILURE;
        }

        // Conserva solo los respaldos recientes
        $limite = now()->subDays((int) $this->option('dias'))->timestamp;
        foreach (File::glob($carpeta.'/respaldo_*.sql') as $viejo) {
            if (File::lastModified($viejo) < $limite) {
                File::delete($viejo);
            }
        }

        $this->info('Respaldo creado: '.$archivo);

        return self::SUCCESS;
    }
}
