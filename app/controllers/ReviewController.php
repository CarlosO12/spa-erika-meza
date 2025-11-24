<?php
/**
 * Controlador de Reseñas
 * SPA Erika Meza
 */

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Review.php';
require_once APP_PATH . '/models/Appointment.php';

class ReviewController {
    private $db;
    private $reviewModel;
    private $appointmentModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reviewModel = new Review($this->db);
        $this->appointmentModel = new Appointment($this->db);
    }
    
    /**
     * Crear nueva reseña
     */
    public function create() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            $citaId = (int)$_POST['cita_id'];
            $evaluacion = (int)$_POST['evaluacion'];
            $opinion = cleanText($_POST['opinion'] ?? '');
            
            // Validar que la cita pertenece al usuario
            $cita = $this->appointmentModel->findById($citaId);
            
            if (!$cita || $cita['usuario_id'] != getUserId()) {
                setFlashMessage('error', 'No tienes permiso para calificar esta cita.');
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            // Validar calificación
            if ($evaluacion < 1 || $evaluacion > 5) {
                setFlashMessage('error', 'Calificación inválida.');
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            // Crear reseña
            $data = [
                'cita_id' => $citaId,
                'evaluacion' => $evaluacion,
                'opinion' => $opinion
            ];
            
            $reviewId = $this->reviewModel->create($data);
            
            if ($reviewId) {
                setFlashMessage('success', '¡Gracias por tu reseña! Tu opinión es muy importante para nosotros.');
            } else {
                setFlashMessage('error', 'No se pudo guardar la reseña. Es posible que ya hayas calificado este servicio.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
            exit();
        }
    }
    
    /**
     * Actualizar reseña
     */
    public function update() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reviewId = (int)$_POST['review_id'];
            $evaluacion = (int)$_POST['evaluacion'];
            $opinion = cleanText($_POST['opinion'] ?? '');
            
            // Verificar que la reseña pertenece al usuario
            $review = $this->reviewModel->getByAppointment((int)$_POST['cita_id']);
            $cita = $this->appointmentModel->findById($review['cita_id']);
            
            if (!$cita || $cita['usuario_id'] != getUserId()) {
                setFlashMessage('error', 'No tienes permiso para editar esta reseña.');
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            $data = [
                'evaluacion' => $evaluacion,
                'opinion' => $opinion
            ];
            
            if ($this->reviewModel->update($reviewId, $data)) {
                setFlashMessage('success', 'Reseña actualizada correctamente.');
            } else {
                setFlashMessage('error', 'Error al actualizar la reseña.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
            exit();
        }
    }
    
    /**
     * Eliminar reseña (Admin)
     */
    public function delete() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reviewId = (int)$_POST['review_id'];
            
            if ($this->reviewModel->delete($reviewId)) {
                setFlashMessage('success', 'Reseña eliminada correctamente.');
            } else {
                setFlashMessage('error', 'Error al eliminar la reseña.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-reviews');
            exit();
        }
    }
    
    /**
     * Ver reseñas de un especialista (Público)
     */
    public function viewSpecialistReviews() {
        $especialistaId = (int)$_GET['especialista_id'];
        
        $reviews = $this->reviewModel->getBySpecialist($especialistaId);
        $stats = $this->reviewModel->getSpecialistStats($especialistaId);
        
        return [
            'reviews' => $reviews,
            'stats' => $stats
        ];
    }
}
?>