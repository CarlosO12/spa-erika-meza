<?php
/**
 * Controlador de Autenticación
 * SPA Erika Meza
 */
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/User.php';

class AuthController {
    private $db;
    private $userModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }
    
    /**
     * Procesar registro de usuario
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=register');
                exit();
            }
            
            $data = [
                'nombre' => cleanString($_POST['nombre']),
                'email' => cleanEmail($_POST['email']),
                'telefono' => cleanPhone($_POST['telefono']),
                'password' => $_POST['password'],
                'password_confirm' => $_POST['password_confirm'],
                'rol' => ROLE_CLIENT
            ];
            
            // Validar datos
            $errors = [];
            
            if (!required($data['nombre'])) {
                $errors[] = 'El nombre es requerido.';
            }
            
            if (!validateEmail($data['email'])) {
                $errors[] = 'Email inválido.';
            }
            
            if ($this->userModel->emailExists($data['email'])) {
                $errors[] = MSG_ERROR_EMAIL_EXISTS;
            }
            
            if (!empty($data['telefono']) && !validatePhone($data['telefono'])) {
                $errors[] = 'Teléfono inválido.';
            }
            
            $passwordValidation = validatePassword($data['password']);
            if (!$passwordValidation['valid']) {
                $errors[] = $passwordValidation['message'];
            }
            
            if ($data['password'] !== $data['password_confirm']) {
                $errors[] = 'Las contraseñas no coinciden.';
            }
            
            // Si hay errores, regresar
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_data'] = $data;
                header('Location: ' . BASE_URL . '/index.php?page=register');
                exit();
            }
            
            // Crear usuario
            $userId = $this->userModel->create($data);
            
            if ($userId) {
                setFlashMessage('success', MSG_SUCCESS_REGISTER);
                header('Location: ' . BASE_URL . '/index.php?page=verify&email=' . urlencode($data['email']));
                exit();
            } else {
                setFlashMessage('error', MSG_ERROR_REGISTER);
                header('Location: ' . BASE_URL . '/index.php?page=register');
                exit();
            }
        }
    }
    
    /**
     * Procesar inicio de sesión
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=login');
                exit();
            }
            
            $email = cleanEmail($_POST['email']);
            $password = $_POST['password'];
            
            // Validar datos
            if (empty($email) || empty($password)) {
                setFlashMessage('error', MSG_ERROR_INVALID_DATA);
                header('Location: ' . BASE_URL . '/index.php?page=login');
                exit();
            }
            
            // Buscar usuario
            $usuario = $this->userModel->findByEmail($email);
            
            if (!$usuario) {
                setFlashMessage('error', MSG_ERROR_LOGIN);
                header('Location: ' . BASE_URL . '/index.php?page=login');
                exit();
            }
            
            // Verificar contraseña
            if (!$this->userModel->verifyPassword($password, $usuario['password_hash'])) {
                setFlashMessage('error', MSG_ERROR_LOGIN);
                header('Location: ' . BASE_URL . '/index.php?page=login');
                exit();
            }
            
            // Verificar que la cuenta esté verificada
            if (!$usuario['verificado']) {
                setFlashMessage('warning', 'Por favor verifica tu cuenta antes de iniciar sesión.');
                header('Location: ' . BASE_URL . '/index.php?page=verify&email=' . urlencode($email));
                exit();
            }
            
            // Iniciar sesión
            loginUser($usuario);
            
            setFlashMessage('success', MSG_SUCCESS_LOGIN);
            
            // Redirigir según rol
            switch ($usuario['rol']) {
                case ROLE_ADMIN:
                    header('Location: ' . BASE_URL . '/index.php?page=admin-dashboard');
                    break;
                case ROLE_SPECIALIST:
                    header('Location: ' . BASE_URL . '/index.php?page=specialist-dashboard');
                    break;
                case ROLE_CLIENT:
                default:
                    header('Location: ' . BASE_URL . '/index.php?page=user-dashboard');
                    break;
            }
            exit();
        }
    }
    
    /**
     * Verificar cuenta con código
     */
    public function verify() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = cleanEmail($_POST['email']);
            $code = cleanString($_POST['code']);
            
            if (empty($email) || empty($code)) {
                setFlashMessage('error', MSG_ERROR_INVALID_DATA);
                header('Location: ' . BASE_URL . '/index.php?page=verify&email=' . urlencode($email));
                exit();
            }
            
            if ($this->userModel->verifyAccount($email, $code)) {
                setFlashMessage('success', '¡Cuenta verificada exitosamente! Ya puedes iniciar sesión.');
                header('Location: ' . BASE_URL . '/index.php?page=login');
                exit();
            } else {
                setFlashMessage('error', MSG_ERROR_VERIFICATION);
                header('Location: ' . BASE_URL . '/index.php?page=verify&email=' . urlencode($email));
                exit();
            }
        }
    }
    
    /**
     * Solicitar recuperación de contraseña
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = cleanEmail($_POST['email']);
            
            if (empty($email) || !validateEmail($email)) {
                setFlashMessage('error', 'Email inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=forgot-password');
                exit();
            }
            
            $token = $this->userModel->generateResetToken($email);
            
            if ($token) {
                setFlashMessage('success', 'Se ha enviado un enlace de recuperación a tu email.');
            } else {
                setFlashMessage('info', 'Si el email existe, recibirás instrucciones de recuperación.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=login');
            exit();
        }
    }
    
    /**
     * Restablecer contraseña
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = cleanString($_POST['token']);
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];
            
            // Validar contraseña
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                setFlashMessage('error', $passwordValidation['message']);
                header('Location: ' . BASE_URL . '/index.php?page=reset-password&token=' . urlencode($token));
                exit();
            }
            
            if ($password !== $passwordConfirm) {
                setFlashMessage('error', 'Las contraseñas no coinciden.');
                header('Location: ' . BASE_URL . '/index.php?page=reset-password&token=' . urlencode($token));
                exit();
            }
            
            if ($this->userModel->resetPassword($token, $password)) {
                setFlashMessage('success', 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.');
                header('Location: ' . BASE_URL . '/index.php?page=login');
                exit();
            } else {
                setFlashMessage('error', 'Token inválido o expirado.');
                header('Location: ' . BASE_URL . '/index.php?page=forgot-password');
                exit();
            }
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        logoutUser();
        setFlashMessage('success', MSG_SUCCESS_LOGOUT);
        header('Location: ' . BASE_URL . '/index.php?page=home');
        exit();
    }
}
?>