<?php
/**
 * Constantes del Sistema
 * SPA Erika Meza
 */

// Roles de usuario
define('ROLE_ADMIN', 'administrador');
define('ROLE_SPECIALIST', 'especialista');
define('ROLE_CLIENT', 'cliente');

// Estados de citas
define('APPOINTMENT_PENDING', 'pendiente');
define('APPOINTMENT_CONFIRMED', 'confirmada');
define('APPOINTMENT_COMPLETED', 'completada');
define('APPOINTMENT_CANCELLED', 'cancelada');

// Tipos de notificación
define('NOTIFICATION_EMAIL', 'email');
define('NOTIFICATION_SMS', 'sms');
define('NOTIFICATION_SYSTEM', 'system');

// Días de la semana
define('DAYS_OF_WEEK', [
    0 => 'Domingo',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
]);

// Categorías de servicios
define('SERVICE_CATEGORIES', [
    'Manos' => 'Manos',
    'Pies' => 'Pies',
    'Faciales' => 'Faciales',
    'Masajes' => 'Masajes',
    'Depilación' => 'Depilación',
    'Tratamientos' => 'Tratamientos Especiales'
]);

// Mensajes del sistema
define('MSG_SUCCESS_REGISTER', 'Registro exitoso. Por favor verifica tu correo electrónico.');
define('MSG_SUCCESS_LOGIN', 'Inicio de sesión exitoso.');
define('MSG_SUCCESS_LOGOUT', 'Sesión cerrada correctamente.');
define('MSG_SUCCESS_UPDATE', 'Información actualizada correctamente.');
define('MSG_SUCCESS_DELETE', 'Registro eliminado correctamente.');
define('MSG_SUCCESS_APPOINTMENT', 'Cita agendada exitosamente.');
define('MSG_SUCCESS_CANCEL', 'Cita cancelada correctamente.');

define('MSG_ERROR_LOGIN', 'Email o contraseña incorrectos.');
define('MSG_ERROR_REGISTER', 'Error al registrar usuario.');
define('MSG_ERROR_EMAIL_EXISTS', 'El email ya está registrado.');
define('MSG_ERROR_VERIFICATION', 'Código de verificación inválido.');
define('MSG_ERROR_UNAUTHORIZED', 'No tienes permisos para acceder a esta página.');
define('MSG_ERROR_NOT_FOUND', 'Registro no encontrado.');
define('MSG_ERROR_INVALID_DATA', 'Datos inválidos o incompletos.');
define('MSG_ERROR_APPOINTMENT', 'Error al agendar la cita.');
define('MSG_ERROR_TIME_SLOT', 'El horario seleccionado no está disponible.');
define('MSG_ERROR_CANCEL_TIME', 'No se puede cancelar con menos de 24 horas de anticipación.');

// Configuración de horarios
define('BUSINESS_HOURS', [
    'start' => config('business_hours_start', '08:00'),
    'end' => config('business_hours_end', '18:00'),
    'slot_duration' => (int) config('slot_duration', 30)
]);

// Rating
define('MIN_RATING', 1);
define('MAX_RATING', 5);
?>