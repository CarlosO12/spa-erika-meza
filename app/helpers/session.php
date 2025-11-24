<?php
/**
 * Helper de Gestión de Sesiones
 * SPA Erika Meza
 */

/**
 * Verificar si el usuario está autenticado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function hasRole($rol) {
    return isLoggedIn() && isset($_SESSION['rol']) && $_SESSION['rol'] === $rol;
}

/**
 * Verificar si el usuario es administrador
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Verificar si el usuario es especialista
 */
function isSpecialist() {
    return hasRole(ROLE_SPECIALIST);
}

/**
 * Verificar si el usuario es cliente
 */
function isClient() {
    return hasRole(ROLE_CLIENT);
}

/**
 * Obtener ID del usuario actual
 */
function getUserId() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtener nombre del usuario actual
 */
function getUserName() {
    return $_SESSION['nombre_usuario'] ?? null;
}

/**
 * Obtener email del usuario actual
 */
function getUserEmail() {
    return $_SESSION['email_usuario'] ?? null;
}

/**
 * Obtener rol del usuario actual
 */
function getUserRole() {
    return $_SESSION['rol'] ?? null;
}

/**
 * Establecer mensaje flash
 */
function setFlashMessage($tipo, $mensaje) {
    $_SESSION['flash_message'] = [
        'type' => $tipo,
        'message' => $mensaje
    ];
}

/**
 * Obtener y eliminar mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $mensaje = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $mensaje;
    }
    return null;
}

/**
 * Mostrar mensaje flash en HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($flash['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    return '';
}

/**
 * Iniciar sesión de usuario
 */
function loginUser($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre_usuario'] = $usuario['nombre'];
    $_SESSION['email_usuario'] = $usuario['email'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['verificado'] = $usuario['verificado'];
    $_SESSION['last_activity'] = time();
    session_regenerate_id(true);
}

/**
 * Cerrar sesión de usuario
 */
function logoutUser() {
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Requerir autenticación
 * Redirige al login si no está autenticado
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit();
    }
}

/**
 * Requerir rol específico
 */
function requireRole($rol) {
    requireAuth();
    if (!hasRole($rol)) {
        setFlashMessage('error', MSG_ERROR_UNAUTHORIZED);
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

/**
 * Requerir que sea administrador
 */
function requireAdmin() {
    requireRole(ROLE_ADMIN);
}

/**
 * Requerir que sea especialista
 */
function requireSpecialist() {
    requireRole(ROLE_SPECIALIST);
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtener campo hidden con token CSRF
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?>