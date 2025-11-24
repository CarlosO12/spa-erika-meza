<?php
/**
 * Controlador para mensajes de contacto
 * SPA Erika Meza
 */
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/ContactMessage.php';
require_once APP_PATH . '/helpers/email.php';

class ContactController {
    private $db;
    private $contactModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->contactModel = new ContactMessage($this->db);
    }

    /**
     * Enviar mensaje de contacto
     */
    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Protección anti-spam: honeypot
            if (!empty($_POST['website'])) {
                setFlashMessage('error', 'Solicitud inválida.');
                header('Location: ' . BASE_URL . '/index.php?page=contact');
                exit();
            }

            // Sanitizar y validar entradas
            $nombre = cleanString($_POST['nombre'] ?? ''); 
            $email = cleanEmail($_POST['email'] ?? '');
            $telefono = cleanPhone($_POST['telefono'] ?? '');
            $asunto = cleanString($_POST['subject'] ?? '');
            $mensaje = cleanText($_POST['mensaje'] ?? '');

            // Validación
            $errors = [];

            if (!required($nombre)) {
                $errors[] = 'El nombre es requerido.';
            }

            if (!required($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Correo electrónico no válido.';
            }

            if (!required($asunto)) {
                $errors[] = 'El asunto es requerido.';
            }

            if (!required($mensaje)) {
                $errors[] = 'El mensaje es requerido.';
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . BASE_URL . '/index.php?page=contact');
                exit();
            }

            // Preparar datos para guardar en BD
            $data = [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'asunto' => $asunto,
                'mensaje' => $mensaje
            ];

            // GUARDAR EN LA BASE DE DATOS
            if (!$this->contactModel->create($data)) {
                setFlashMessage('error', 'Error al guardar el mensaje. Por favor intenta nuevamente.');
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . BASE_URL . '/index.php?page=contact');
                exit();
            }

            // Preparar datos para emails
            $emailData = [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'asunto' => $asunto,
                'mensaje' => $mensaje
            ];

            // Enviar correos
            sendContactNotification($emailData);
            sendContactConfirmation($emailData);

            // Limpiar datos del formulario
            unset($_SESSION['form_data']);
            
            setFlashMessage('success', 'Tu mensaje ha sido enviado correctamente. Te responderemos pronto.');
            header('Location: ' . BASE_URL . '/index.php?page=contact');
            exit();
        }
    }

    /**
     * Obtener todos los mensajes (para panel admin)
     */
    public function getMessages($estado = null, $busqueda = null) {
        requireRole(ROLE_ADMIN);
        return $this->contactModel->getAll($estado, $busqueda);
    }

    /**
     * Ver detalle de un mensaje
     */
    public function viewMessage($id) {
        requireRole(ROLE_ADMIN);
        
        $mensaje = $this->contactModel->findById($id);
        
        if (!$mensaje) {
            setFlashMessage('error', 'Mensaje no encontrado.');
            header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
            exit();
        }

        // Marcar como leído si es nuevo
        if ($mensaje['estado'] === 'nuevo') {
            $this->contactModel->updateStatus($id, 'leido');
        }

        return $mensaje;
    }

    /**
     * Actualizar estado del mensaje
     */
    public function updateStatus() {
        requireRole(ROLE_ADMIN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $estado = cleanString($_POST['estado']);

            // Validar estado
            $estadosValidos = ['nuevo', 'leido', 'respondido'];
            if (!in_array($estado, $estadosValidos)) {
                setFlashMessage('error', 'Estado no válido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
                exit();
            }

            if ($this->contactModel->updateStatus($id, $estado)) {
                setFlashMessage('success', 'Estado actualizado correctamente.');
            } else {
                setFlashMessage('error', 'Error al actualizar el estado.');
            }

            header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
            exit();
        }
    }

    /**
     * Eliminar un mensaje
     */
    public function deleteMessage() {
        requireRole(ROLE_ADMIN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];

            if ($this->contactModel->delete($id)) {
                setFlashMessage('success', 'Mensaje eliminado correctamente.');
            } else {
                setFlashMessage('error', 'Error al eliminar el mensaje.');
            }

            header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
            exit();
        }
    }

    /**
     * Obtener estadísticas de mensajes
     */
    public function getStats() {
        requireRole(ROLE_ADMIN);

        return [
            'total' => $this->contactModel->countByStatus(),
            'nuevos' => $this->contactModel->countByStatus('nuevo'),
            'leidos' => $this->contactModel->countByStatus('leido'),
            'respondidos' => $this->contactModel->countByStatus('respondido')
        ];
    }

    /**
     * Obtener mensajes recientes
     */
    public function getRecent($limit = 5) {
        requireRole(ROLE_ADMIN);
        return $this->contactModel->getRecent($limit);
    }

    /**
     * Responder mensaje al cliente
     */
    public function replyMessage() {
        requireRole(ROLE_ADMIN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $messageId = (int)$_POST['message_id'];
            $subject = cleanString($_POST['subject']);
            $message = cleanText($_POST['message']);

            // Validación
            if (empty($subject) || empty($message)) {
                setFlashMessage('error', 'El asunto y el mensaje son requeridos.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
                exit();
            }

            // Obtener información del mensaje original
            $originalMessage = $this->contactModel->findById($messageId);
            
            if (!$originalMessage) {
                setFlashMessage('error', 'Mensaje no encontrado.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
                exit();
            }

            // Preparar datos para el email
            $emailData = [
                'to_name' => $originalMessage['nombre'],
                'to_email' => $originalMessage['email'],
                'subject' => $subject,
                'message' => $message,
                'original_message' => $originalMessage['mensaje'],
                'admin_name' => getUserName()
            ];

            // Enviar respuesta
            if (sendMessageReply($emailData)) {
                // Actualizar estado a "respondido"
                $this->contactModel->updateStatus($messageId, 'respondido');
                setFlashMessage('success', 'Respuesta enviada correctamente.');
            } else {
                setFlashMessage('error', 'Error al enviar la respuesta. Por favor intenta nuevamente.');
            }

            header('Location: ' . BASE_URL . '/index.php?page=admin-messages');
            exit();
        }
    }
}
?>