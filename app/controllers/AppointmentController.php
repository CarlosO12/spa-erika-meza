<?php
/**
 * Controlador de Citas
 * SPA Erika Meza
 */
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Specialist.php';

class AppointmentController {
    private $db;
    private $appointmentModel;
    private $serviceModel;
    private $specialistModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->appointmentModel = new Appointment($this->db);
        $this->serviceModel = new Service($this->db);
        $this->specialistModel = new Specialist($this->db);
    }
    
    /**
     * Crear nueva cita
     */
    public function create() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=book-appointment');
                exit();
            }
            
            $data = [
                'usuario_id' => getUserId(),
                'especialista_id' => (int)$_POST['especialista_id'],
                'servicio_id' => (int)$_POST['servicio_id'],
                'fecha_cita' => cleanString($_POST['fecha_cita']),
                'hora_cita' => cleanString($_POST['hora_cita']),
                'estado' => APPOINTMENT_PENDING,
                'notas' => cleanText($_POST['notas'] ?? '')
            ];
            
            // Validar datos
            $errors = [];
            
            if (!validateDate($data['fecha_cita'])) {
                $errors[] = 'Fecha inválida.';
            }
            
            if (!isFutureDate($data['fecha_cita'])) {
                $errors[] = 'La fecha debe ser futura.';
            }
            
            if (!validateTime($data['hora_cita'])) {
                $errors[] = 'Hora inválida.';
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_data'] = $data;
                header('Location: ' . BASE_URL . '/index.php?page=book-appointment');
                exit();
            }
            
            // Crear cita
            $appointmentId = $this->appointmentModel->create($data);
            
            if ($appointmentId) {
                // Limpiar carrito
                if (isset($_SESSION['cart'][$data['servicio_id']])) {
                    unset($_SESSION['cart'][$data['servicio_id']]);
                }
                
                setFlashMessage('success', MSG_SUCCESS_APPOINTMENT);
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
            } else {
                setFlashMessage('error', MSG_ERROR_APPOINTMENT);
                header('Location: ' . BASE_URL . '/index.php?page=book-appointment');
            }
            exit();
        }
    }
    
    /**
     * Actualizar estado de cita (Admin/Especialista)
     */
    public function updateStatus() {
        if (!isAdmin() && !isSpecialist()) {
            setFlashMessage('error', MSG_ERROR_UNAUTHORIZED);
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentId = (int)$_POST['id'];
            $status = cleanString($_POST['estado']);
            
            $validStatuses = [
                APPOINTMENT_PENDING,
                APPOINTMENT_CONFIRMED,
                APPOINTMENT_COMPLETED,
                APPOINTMENT_CANCELLED
            ];
            
            if (!in_array($status, $validStatuses)) {
                setFlashMessage('error', 'Estado inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-appointments');
                exit();
            }
            
            if ($this->appointmentModel->updateStatus($appointmentId, $status)) {
                setFlashMessage('success', 'Estado actualizado correctamente.');
                
                // Enviar notificación si se confirma
                if ($status === APPOINTMENT_CONFIRMED) {
                    $appointmentData = $this->appointmentModel->getFullAppointmentData($appointmentId);
                    if ($appointmentData) {
                        sendAppointmentConfirmation($appointmentData);
                    }
                }

                if ($status === APPOINTMENT_CANCELLED) {
                    $appointmentData = $this->appointmentModel->getFullAppointmentData($appointmentId);
                    if ($appointmentData) {
                        sendCancellationEmail($appointmentData);
                    }
                }
            } else {
                setFlashMessage('error', 'Error al actualizar el estado.');
            }
            
            if (isAdmin()) {
                header('Location: ' . BASE_URL . '/index.php?page=admin-appointments');
            } else {
                header('Location: ' . BASE_URL . '/index.php?page=specialist-appointments');
            }
            exit();
        }
    }
    
    /**
     * Obtener horarios disponibles (AJAX)
     */
    public function getAvailableSlots() {
        header('Content-Type: application/json');
        
        try {
            $specialistId = (int)($_GET['especialista_id'] ?? 0);
            $date = cleanString($_GET['fecha_cita'] ?? '');
            $serviceId = (int)($_GET['servicio_id'] ?? 0);
            
            // Validaciones
            if (!$specialistId || !$serviceId) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Datos incompletos'
                ]);
                exit();
            }
            
            if (!validateDate($date)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Fecha inválida'
                ]);
                exit();
            }
            
            if (!isFutureDate($date) && $date !== date('Y-m-d')) {
                echo json_encode([
                    'success' => false,
                    'error' => 'La fecha debe ser actual o futura'
                ]);
                exit();
            }
            
            // Obtener servicio
            $service = $this->serviceModel->findById($serviceId);
            if (!$service) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Servicio no encontrado'
                ]);
                exit();
            }
            
            // Obtener slots disponibles
            $slots = $this->appointmentModel->getAvailableTimeSlots(
                $specialistId,
                $date,
                $service['duracion']
            );
            
            echo json_encode([
                'success' => true,
                'slots' => $slots,
                'serviceName' => $service['nombre'],
                'duration' => $service['duracion']
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener horarios: ' . $e->getMessage()
            ]);
        }
        
        exit();
    }
    
    /**
     * Reprogramar cita
     */
    public function reschedule() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentId = (int)$_POST['cita_id'];
            $newDate = cleanString($_POST['fecha_cita']);
            $newTime = cleanString($_POST['hora_cita']);
            
            // Verificar que la cita pertenece al usuario
            $appointment = $this->appointmentModel->findById($appointmentId);
            
            if (!$appointment || $appointment['usuario_id'] != getUserId()) {
                setFlashMessage('error', MSG_ERROR_UNAUTHORIZED);
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            // Verificar disponibilidad
            if (!$this->appointmentModel->isTimeSlotAvailable(
                $appointment['especialista_id'],
                $newDate,
                $newTime
            )) {
                setFlashMessage('error', MSG_ERROR_TIME_SLOT);
                header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
                exit();
            }
            
            // Actualizar cita
            $query = "UPDATE citas SET fecha_cita = :fecha, hora_cita = :hora WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':fecha', $newDate);
            $stmt->bindParam(':hora', $newTime);
            $stmt->bindParam(':id', $appointmentId);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Cita reprogramada exitosamente.');
            } else {
                setFlashMessage('error', 'Error al reprogramar la cita.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=my-appointments');
            exit();
        }
    }
    
    /**
     * Enviar recordatorios de citas
     */
    public function sendReminders() {
        $appointments = $this->appointmentModel->getUpcomingAppointments(24);
        
        foreach ($appointments as $appointment) {
            sendAppointmentReminder($appointment);
        }
        
        echo "Recordatorios enviados: " . count($appointments);
    }
}
?>