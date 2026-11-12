<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class CalendarioBiblioteca
{
    public static function contarDiasServicio(CarbonImmutable $desde, CarbonImmutable $hasta): int
    {
        if ($desde->gt($hasta)) {
            return 0;
        }

        $cierres = config('biblioteca.dias_cierre', []);
        $diasServicio = config('biblioteca.dias_servicio', [1, 2, 3, 4, 5, 6]);
        $festivos = self::festivosColombia($desde->year, $hasta->year);
        $dias = 0;

        for ($fecha = $desde; $fecha->lte($hasta); $fecha = $fecha->addDay()) {
            $fechaTexto = $fecha->toDateString();

            if (
                in_array($fecha->dayOfWeekIso, $diasServicio, true)
                && ! in_array($fechaTexto, $cierres, true)
                && ! isset($festivos[$fechaTexto])
            ) {
                $dias++;
            }
        }

        return $dias;
    }

    /**
     * Festivos nacionales de Colombia: fijos, trasladables por Ley Emiliani y Semana Santa.
     *
     * @return array<string, true>
     */
    private static function festivosColombia(int $desdeYear, int $hastaYear): array
    {
        $festivos = [];

        for ($year = $desdeYear; $year <= $hastaYear; $year++) {
            $fijos = [
                [1, 1, false],
                [1, 6, true],
                [3, 19, true],
                [5, 1, false],
                [6, 29, true],
                [7, 20, false],
                [8, 7, false],
                [8, 15, true],
                [10, 12, true],
                [11, 1, true],
                [11, 11, true],
                [12, 8, false],
                [12, 25, false],
            ];

            foreach ($fijos as [$month, $day, $moveToMonday]) {
                $date = CarbonImmutable::create($year, $month, $day);
                $date = $moveToMonday ? self::nextMonday($date) : $date;
                $festivos[$date->toDateString()] = true;
            }

            $easter = CarbonImmutable::create($year, 3, 21)->addDays(easter_days($year));
            foreach ([-3, -2] as $offset) {
                $festivos[$easter->addDays($offset)->toDateString()] = true;
            }

            foreach ([39, 60, 68] as $offset) {
                $festivoMovible = self::nextMonday($easter->addDays($offset));
                $festivos[$festivoMovible->toDateString()] = true;
            }
        }

        return $festivos;
    }

    private static function nextMonday(CarbonImmutable $date): CarbonImmutable
    {
        return $date->dayOfWeekIso === 1 ? $date : $date->next(CarbonImmutable::MONDAY);
    }
}
