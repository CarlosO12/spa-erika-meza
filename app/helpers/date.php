<?php
/**
 * Helper para manejo de días de la semana
 * Agregar a app/helpers/date.php o crear nuevo archivo
 */

/**
 * Obtener nombre del día desde número
 */
function getDayName($dayNumber) {
    $dias = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado'
    ];
    
    return $dias[$dayNumber] ?? 'Desconocido';
}

/**
 * Obtener número del día desde nombre
 */
function getDayNumber($dayName) {
    $dias = [
        'domingo' => 0,
        'lunes' => 1,
        'martes' => 2,
        'miercoles' => 3,
        'miércoles' => 3,
        'jueves' => 4,
        'viernes' => 5,
        'sabado' => 6,
        'sábado' => 6
    ];
    
    return $dias[strtolower($dayName)] ?? null;
}

/**
 * Obtener todos los días de la semana
 */
function getAllDays() {
    return [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        0 => 'Domingo'
    ];
}

/**
 * Obtener número del día desde fecha
 */
function getDayNumberFromDate($date) {
    return (int)date('w', strtotime($date));
}

/**
 * Obtener nombre del día desde fecha en español
 */
function getDayNameFromDate($date) {
    $dayNumber = getDayNumberFromDate($date);
    return getDayName($dayNumber);
}
?>