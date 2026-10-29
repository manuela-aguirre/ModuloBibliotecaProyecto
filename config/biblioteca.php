<?php

return [
    // Plazo por defecto del préstamo (días). El reglamento define largo/mediano/corto plazo.
    'dias_prestamo' => (int) env('BIBLIOTECA_DIAS_PRESTAMO', 8),

    // Máximo de días hacia adelante que el usuario puede elegir para reclamar su reserva.
    'dias_max_reserva' => (int) env('BIBLIOTECA_DIAS_MAX_RESERVA', 3),

    // Multa = días de retraso × SMDLV × porcentaje / 100
    'smdlv' => (float) env('BIBLIOTECA_SMDLV', 0),
    'multa_porcentaje_dia' => (float) env('BIBLIOTECA_MULTA_PORCENTAJE', 1),
];