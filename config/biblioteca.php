<?php

return [
    // Plazo por defecto del préstamo (días). El reglamento define largo/mediano/corto plazo.
    'dias_prestamo' => (int) env('BIBLIOTECA_DIAS_PRESTAMO', 8),

    // Máximo de días hacia adelante que el usuario puede elegir para reclamar su reserva.
    'dias_max_reserva' => (int) env('BIBLIOTECA_DIAS_MAX_RESERVA', 3),
    'dias_retiro_lista_espera' => (int) env('BIBLIOTECA_DIAS_RETIRO_LISTA_ESPERA', 2),

    // SMDLV = salario mínimo mensual legal vigente dividido entre 30.
    'smlmv' => (float) env('BIBLIOTECA_SMLMV', 1750905),
    'smdlv' => (float) env('BIBLIOTECA_SMDLV', 0),
    'porcentaje_multa' => [
        'general' => 1,
        'reserva' => 2,
        'otra' => 2,
    ],
    'dias_servicio' => [1, 2, 3, 4, 5, 6],
    'dias_cierre' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('BIBLIOTECA_DIAS_CIERRE', ''))
    ))),
];