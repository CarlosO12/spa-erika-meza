<?php
/**
 * Modelo de Cita (Appointment)
 * SPA Erika Meza
 */
class Appointment {
    private $conn;
    private $table = 'citas';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nueva cita
     */
    public function create($data) {
        if (!$this->isTimeSlotAvailable($data['especialista_id'], $data['fecha_cita'], $data['hora_cita'])) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (usuario_id, especialista_id, servicio_id, fecha_cita, hora_cita, estado, notas) 
                  VALUES (:usuario_id, :especialista_id, :servicio_id, :fecha_cita, :hora_cita, :estado, :notas)";
        
        $stmt = $this->conn->prepare($query);
        
        $estado = $data['estado'] ?? APPOINTMENT_PENDING;
        $notas = $data['notas'] ?? null;
        
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        $stmt->bindParam(':especialista_id', $data['especialista_id']);
        $stmt->bindParam(':servicio_id', $data['servicio_id']);
        $stmt->bindParam(':fecha_cita', $data['fecha_cita']);
        $stmt->bindParam(':hora_cita', $data['hora_cita']);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':notas', $notas);
        
        if ($stmt->execute()) {
            $appointmentId = $this->conn->lastInsertId();
            
            $appointmentData = $this->getFullAppointmentData($appointmentId);
            if ($appointmentData) {
                sendAppointmentConfirmation($appointmentData);
                sendSpecialistNewAppointmentNotification($appointmentData);
            }
            
            return $appointmentId;
        }
        
        return false;
    }
    
    /**
     * Verificar si un horario está disponible
     */
    public function isTimeSlotAvailable($especialistaId, $fecha, $hora) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE especialista_id = :especialista_id 
                  AND fecha_cita = :fecha 
                  AND hora_cita = :hora 
                  AND estado NOT IN ('cancelada')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $especialistaId);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] == 0;
    }
    
    /**
     * Obtener cita por ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Obtener datos completos de cita para emails
     */
    public function getFullAppointmentData($id) {
        $query = "SELECT * FROM vw_citas_full WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Obtener citas de un usuario
     */
    public function getByUser($usuarioId, $estado = null) {
        if ($estado) {
            $query = "SELECT * FROM vw_citas_full 
                      WHERE email_cliente = (SELECT email FROM usuarios WHERE id = :usuario_id) 
                      AND estado = :estado 
                      ORDER BY fecha_cita DESC, hora_cita DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':estado', $estado);
        } else {
            $query = "SELECT * FROM vw_citas_full 
                      WHERE email_cliente = (SELECT email FROM usuarios WHERE id = :usuario_id) 
                      ORDER BY fecha_cita DESC, hora_cita DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener citas de un especialista
     */
    public function getBySpecialist($especialistaId, $fecha = null) {
        if ($fecha) {
            $query = "SELECT * FROM vw_citas_full 
                      WHERE nombre_especialista = (SELECT nombre FROM usuarios WHERE id = (SELECT usuario_id FROM especialistas WHERE id = :especialista_id)) 
                      AND fecha_cita = :fecha 
                      AND estado NOT IN ('cancelada') 
                      ORDER BY hora_cita";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':especialista_id', $especialistaId);
            $stmt->bindParam(':fecha', $fecha);
        } else {
            $query = "SELECT * FROM vw_citas_full 
                      WHERE nombre_especialista = (SELECT nombre FROM usuarios WHERE id = (SELECT usuario_id FROM especialistas WHERE id = :especialista_id)) 
                      AND estado NOT IN ('cancelada') 
                      ORDER BY fecha_cita DESC, hora_cita";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':especialista_id', $especialistaId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Actualizar estado de cita
     */
    public function updateStatus($id, $estado) {
        $query = "UPDATE " . $this->table . " SET estado = :estado WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        
        return $stmt->execute();
    }
    
    /**
     * Cancelar cita
     */
    public function cancel($id, $razon = null) {
        $appointment = $this->findById($id);
        
        if (!$appointment) {
            return false;
        }

        $appointmentDateTime = strtotime($appointment['fecha_cita'] . ' ' . $appointment['hora_cita']);
        $now = time();
        $hoursUntilAppointment = ($appointmentDateTime - $now) / 3600;
        
        if ($hoursUntilAppointment < 24) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET estado = 'cancelada', 
                      razon_cancelacion = :razon 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':razon', $razon);
        
        if ($stmt->execute()) {
            $appointmentData = $this->getFullAppointmentData($id);
            if ($appointmentData) {
                sendCancellationEmail($appointmentData);
                sendSpecialistCancellationNotification($appointmentData);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtener citas próximas para recordatorios
     */
    public function getUpcomingAppointments($horas = 24) {
        $query = "SELECT * FROM vw_citas_full 
                  WHERE estado = 'confirmada' 
                  AND CONCAT(fecha_cita, ' ', hora_cita) 
                  BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :horas HOUR)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener horarios ocupados de un especialista en una fecha
     */
    public function getOccupiedSlots($especialistaId, $fecha) {
        $query = "SELECT c.hora_cita, s.duracion 
                FROM " . $this->table . " c
                INNER JOIN servicios s ON c.servicio_id = s.id
                WHERE c.especialista_id = :especialista_id 
                AND c.fecha_cita = :fecha 
                AND c.estado NOT IN ('cancelada')
                ORDER BY c.hora_cita";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $especialistaId);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener horarios disponibles mejorado
     */
    public function getAvailableTimeSlots($especialistaId, $fecha, $duracionServicio) {
        // Obtener el día de la semana (0=domingo, 1=lunes, etc.)
        $diaSemana = date('w', strtotime($fecha));
        
        // Verificar si el especialista trabaja ese día
        $query = "SELECT hora_inicio, hora_fin 
                FROM disponibilidad_especialistas 
                WHERE especialista_id = :especialista_id 
                AND dia_semana = :dia_semana 
                AND activo = 1
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $especialistaId);
        $stmt->bindParam(':dia_semana', $diaSemana);
        $stmt->execute();
        
        $horario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no hay horario configurado, retornar vacío
        if (!$horario) {
            return [];
        }
        
        // Obtener citas ocupadas
        $ocupados = $this->getOccupiedSlots($especialistaId, $fecha);
        
        $availableSlots = [];
        $startTime = strtotime($horario['hora_inicio']);
        $endTime = strtotime($horario['hora_fin']);
        $slotDuration = 30 * 60;
        
        // Generar todos los slots posibles
        for ($time = $startTime; $time < $endTime; $time += $slotDuration) {
            $timeSlot = date('H:i', $time);
            $slotEnd = $time + ($duracionServicio * 60);
            
            // Verificar si el slot cabe en el horario de trabajo
            if ($slotEnd > $endTime) {
                break;
            }
            
            // Verificar si el slot está disponible
            $isAvailable = true;
            
            foreach ($ocupados as $ocupado) {
                $ocupadoStart = strtotime($ocupado['hora_cita']);
                $ocupadoEnd = $ocupadoStart + ($ocupado['duracion'] * 60);
                
                // Verificar si hay solapamiento
                if (($time >= $ocupadoStart && $time < $ocupadoEnd) || 
                    ($slotEnd > $ocupadoStart && $slotEnd <= $ocupadoEnd) ||
                    ($time <= $ocupadoStart && $slotEnd >= $ocupadoEnd)) {
                    $isAvailable = false;
                    break;
                }
            }
            
            // Verificar si el horario ya pasó (solo para el día actual)
            if ($fecha === date('Y-m-d')) {
                $currentTime = time();
                if ($time <= $currentTime) {
                    $isAvailable = false;
                }
            }
            
            if ($isAvailable) {
                $availableSlots[] = [
                    'time' => $timeSlot,
                    'formatted' => date('g:i A', $time)
                ];
            }
        }
        
        return $availableSlots;
    }
    
    /**
     * Obtener todas las citas (admin)
     */
    public function getAll($estado = null, $fecha_cita = null, $busqueda = null) {
        $query = "SELECT * FROM vw_citas_full WHERE 1=1";
        $params = [];
        
        if ($estado !== null && $estado !== '') {
            $query .= " AND estado = ?";
            $params[] = $estado;
        }
        
        if ($fecha_cita !== null && $fecha_cita !== '') {
            $query .= " AND fecha_cita = ?";
            $params[] = $fecha_cita;
        }
        
        if ($busqueda !== null && $busqueda !== '') {
            $busqueda_param = '%' . trim($busqueda) . '%';
            $query .= " AND (nombre_cliente LIKE ? OR nombre_servicio LIKE ?)";
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
        }
        
        $query .= " ORDER BY fecha_cita DESC, hora_cita DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar citas por estado
     */
    public function countByStatus($estado = null) {
        if ($estado) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE estado = :estado";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
        } else {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
?>