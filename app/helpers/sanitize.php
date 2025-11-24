<?php
/**
 * Helper de Sanitización de Datos
 * SPA Erika Meza
 */

/**
 * Limpiar string general
 */
function cleanString($data) {
    $data = trim($data ?? '');
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Limpiar email
 */
function cleanEmail($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return strtolower($email);
}

/**
 * Limpiar teléfono
 */
function cleanPhone($telefono) {
    return preg_replace('/[^0-9]/', '', $telefono);
}

/**
 * Limpiar número
 */
function cleanNumber($number) {
    return filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Limpiar texto (permite HTML básico)
 */
function cleanText($text, $allowedTags = []) {
    if (empty($allowedTags)) {
        $allowedTags = '<p><br><strong><em><u>';
    } else {
        $allowedTags = '<' . implode('><', $allowedTags) . '>';
    }
    return strip_tags($text, $allowedTags);
}

/**
 * Limpiar URL
 */
function cleanUrl($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * Escapar salida para HTML
 */
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Limpiar array de datos
 */
function cleanArray($data) {
    $cleaned = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $cleaned[$key] = cleanArray($value);
        } else {
            $cleaned[$key] = cleanString($value);
        }
    }
    return $cleaned;
}

/**
 * Sanitizar datos POST
 */
function sanitizePost($keys = null) {
    if ($keys === null) {
        return cleanArray($_POST);
    }
    
    $data = [];
    foreach ($keys as $key) {
        $data[$key] = isset($_POST[$key]) ? cleanString($_POST[$key]) : '';
    }
    return $data;
}

/**
 * Sanitizar datos GET
 */
function sanitizeGet($keys = null) {
    if ($keys === null) {
        return cleanArray($_GET);
    }
    
    $data = [];
    foreach ($keys as $key) {
        $data[$key] = isset($_GET[$key]) ? cleanString($_GET[$key]) : '';
    }
    return $data;
}

/**
 * Generar slug para URLs
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Truncar texto
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . $suffix;
    }
    return $text;
}

/**
 * Formatear precio
 */
function formatPrice($precio) {
    return '$' . number_format($precio, 0, ',', '.');
}

/**
 * Formatear fecha
 */
function formatDate($fecha, $format = 'd/m/Y') {
    return date($format, strtotime($fecha));
}

/**
 * Formatear fecha y hora
 */
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Obtener iniciales de nombre
 */
function getInitials($nombre) {
    $words = explode(' ', $nombre);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>