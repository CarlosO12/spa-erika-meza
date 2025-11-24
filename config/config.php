<?php
/**
 * Configuración General del Sistema
 * SPA Erika Meza
 */
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once APP_PATH . '/models/Configuration.php';
require_once CONFIG_PATH . '/database.php';

function loadDatabaseConfig() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $configModel = new Configuration($db);
        $configModel->loadCache();
        return true;
    } catch (Exception $e) {
        error_log("Error cargando configuración: " . $e->getMessage());
        return false;
    }
}

loadDatabaseConfig();

date_default_timezone_set('America/Bogota');

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_URL', 'http://localhost:8000');
define('ASSETS_URL', BASE_URL . '/');

define('APP_VERSION', '2.5.6');

define('APP_NAME', config('app_name', 'SPA Erika Meza'));
define('APP_EMAIL', config('app_email', 'spaerikameza@gmail.com'));
define('APP_PHONE', config('app_phone', '+57 301 355 3417'));
define('APP_ADDRESS', config('app_address', 'Medellín, Antioquia, Colombia'));

define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 7200);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900);

define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

define('SMTP_HOST', config('smtp_host', 'smtp.gmail.com'));
define('SMTP_PORT', config('smtp_port', 587));
define('SMTP_USERNAME', config('smtp_username', 'spaerikameza@gmail.com'));
define('SMTP_PASSWORD', config('smtp_password', 'uebzcuoywvqrzjkx'));
define('SMTP_FROM_EMAIL', config('app_email', APP_EMAIL));
define('SMTP_FROM_NAME', config('app_name', APP_NAME));

define('ITEMS_PER_PAGE', config('items_per_page', 10));

spl_autoload_register(function ($class_name) {
    $directories = [
        APP_PATH . '/models/',
        APP_PATH . '/controllers/',
        APP_PATH . '/helpers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once APP_PATH . '/helpers/session.php';
require_once APP_PATH . '/helpers/validation.php';
require_once APP_PATH . '/helpers/sanitize.php';
require_once APP_PATH . '/helpers/email.php';

if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php?page=login&timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();
?>
