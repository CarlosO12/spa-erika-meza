<?php
/**
 * Controlador de Usuario
 * SPA Erika Meza
 */
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Appointment.php';

class UserController {
    private $db;
    private $userModel;
    private $appointmentModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit();
            }
            
            $usuarioId = getUserId();
            
            $data = [
                'nombre' => cleanString($_POST['nombre']),
                'telefono' => cleanPhone($_POST['telefono']),
                'direccion' => cleanString($_POST['direccion'] ?? '')
            ];
            
            // Validar datos
            $errors = [];
            
            if (!required($data['nombre'])) {
                $errors[] = 'El nombre es requerido.';
            }
            
            if (!empty($data['telefono']) && !validatePhone($data['telefono'])) {
                $errors[] = 'Teléfono inválido.';
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit();
            }
            
            // Actualizar perfil
            if ($this->userModel->update($usuarioId, $data)) {
                $_SESSION['nombre'] = $data['nombre'];
                setFlashMessage('success', MSG_SUCCESS_UPDATE);
            } else {
                setFlashMessage('error', 'Error al actualizar el perfil.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=profile');
            exit();
        }
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit();
            }
            
            $usuarioId = getUserId();
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Obtener usuario
            $usuario = $this->userModel->findById($usuarioId);
            
            // Verificar contraseña actual
            if (!$this->userModel->verifyPassword($currentPassword, $usuario['password_hash'])) {
                setFlashMessage('error', 'La contraseña actual es incorrecta.');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit();
            }
            
            // Validar nueva contraseña
            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                setFlashMessage('error', $passwordValidation['message']);
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit();
            }
            
            if ($newPassword !== $confirmPassword) {
                setFlashMessage('error', 'Las contraseñas no coinciden.');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit();
            }
            
            // Cambiar contraseña
            if ($this->userModel->changePassword($usuarioId, $newPassword)) {
                setFlashMessage('success', 'Contraseña actualizada exitosamente.');
            } else {
                setFlashMessage('error', 'Error al cambiar la contraseña.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=profile');
            exit();
        }
    }
    
    /**
     * Obtener dashboard del usuario
     */
    public function getDashboard() {
        requireRole(ROLE_CLIENT);
        
        $usuarioId = getUserId();
        $upcomingAppointments = $this->appointmentModel->getByUser($usuarioId, APPOINTMENT_CONFIRMED);
        $totalAppointments = count($this->appointmentModel->getByUser($usuarioId));
        
        return [
            'upcoming' => $upcomingAppointments,
            'total' => $totalAppointments
        ];
    }
    
    /**
     * Cancelar cita
     */
    public function cancelAppointment() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentId = (int)$_POST['id'];
            $reason = cleanString($_POST['razon_cancelacion'] ?? '');
            
            // Verificar que la cita pertenece al usuario
            $appointment = $this->appointmentModel->findById($appointmentId);
            
            if (!$appointment || $appointment['usuario_id'] != getUserId()) {
                setFlashMessage('error', MSG_ERROR_UNAUTHORIZED);
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            // Intentar cancelar
            if ($this->appointmentModel->cancel($appointmentId, $reason)) {
                setFlashMessage('success', MSG_SUCCESS_CANCEL);
            } else {
                setFlashMessage('error', MSG_ERROR_CANCEL_TIME);
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
            exit();
        }
    }
}
?>