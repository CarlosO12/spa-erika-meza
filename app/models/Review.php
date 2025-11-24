<?php
/**
 * Modelo de Reseñas (Reviews/Historial de Servicios)
 * SPA Erika Meza
 */

class Review {
    private $conn;
    private $table = 'historial_servicios';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nueva reseña
     */
    public function create($data) {
        // Verificar que la cita esté completada
        $query = "SELECT estado FROM citas WHERE id = :cita_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cita_id', $data['cita_id']);
        $stmt->execute();
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cita || $cita['estado'] !== APPOINTMENT_COMPLETED) {
            return false;
        }
        
        // Verificar que no exista ya una reseña para esta cita
        if ($this->existsForAppointment($data['cita_id'])) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (cita_id, evaluacion, opinion) 
                  VALUES (:cita_id, :evaluacion, :opinion)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':cita_id', $data['cita_id'], PDO::PARAM_INT);
        $stmt->bindParam(':evaluacion', $data['evaluacion'], PDO::PARAM_INT);
        $stmt->bindParam(':opinion', $data['opinion']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Verificar si existe reseña para una cita
     */
    public function existsForAppointment($citaId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE cita_id = :cita_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cita_id', $citaId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener reseña por ID de cita
     */
    public function getByAppointment($citaId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE cita_id = :cita_id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cita_id', $citaId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reseñas de un especialista
     */
    public function getBySpecialist($especialistaId, $limit = null) {
        $query = "SELECT hs.*, 
                         c.fecha_cita,
                         u.nombre as nombre_cliente,
                         s.nombre as nombre_servicio
                  FROM " . $this->table . " hs
                  INNER JOIN citas c ON hs.cita_id = c.id
                  INNER JOIN usuarios u ON c.usuario_id = u.id
                  INNER JOIN servicios s ON c.servicio_id = s.id
                  WHERE c.especialista_id = :especialista_id
                  ORDER BY hs.creado DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $especialistaId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reseñas de un cliente
     */
    public function getByClient($usuarioId) {
        $query = "SELECT hs.*, 
                         c.fecha_cita,
                         ue.nombre as nombre_especialista,
                         s.nombre as nombre_servicio
                  FROM " . $this->table . " hs
                  INNER JOIN citas c ON hs.cita_id = c.id
                  INNER JOIN servicios s ON c.servicio_id = s.id
                  INNER JOIN especialistas e ON c.especialista_id = e.id
                  INNER JOIN usuarios ue ON e.usuario_id = ue.id
                  WHERE c.usuario_id = :usuario_id
                  ORDER BY hs.creado DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todas las reseñas (admin)
     */
    public function getAll($limit = null) {
        $query = "SELECT hs.*, 
                         c.fecha_cita,
                         u.nombre as nombre_cliente,
                         ue.nombre as nombre_especialista,
                         s.nombre as nombre_servicio
                  FROM " . $this->table . " hs
                  INNER JOIN citas c ON hs.cita_id = c.id
                  INNER JOIN usuarios u ON c.usuario_id = u.id
                  INNER JOIN servicios s ON c.servicio_id = s.id
                  INNER JOIN especialistas e ON c.especialista_id = e.id
                  INNER JOIN usuarios ue ON e.usuario_id = ue.id
                  ORDER BY hs.creado DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener reseñas recientes
     */
    public function getRecent($limit = 5) {
        return $this->getAll($limit);
    }
    
    /**
     * Actualizar reseña
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET evaluacion = :evaluacion, 
                      opinion = :opinion 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':evaluacion', $data['evaluacion'], PDO::PARAM_INT);
        $stmt->bindParam(':opinion', $data['opinion']);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar reseña
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas de reseñas de un especialista
     */
    public function getSpecialistStats($especialistaId) {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(hs.evaluacion) as avg_rating,
                    SUM(CASE WHEN hs.evaluacion = 5 THEN 1 ELSE 0 END) as five_stars,
                    SUM(CASE WHEN hs.evaluacion = 4 THEN 1 ELSE 0 END) as four_stars,
                    SUM(CASE WHEN hs.evaluacion = 3 THEN 1 ELSE 0 END) as three_stars,
                    SUM(CASE WHEN hs.evaluacion = 2 THEN 1 ELSE 0 END) as two_stars,
                    SUM(CASE WHEN hs.evaluacion = 1 THEN 1 ELSE 0 END) as one_star
                  FROM " . $this->table . " hs
                  INNER JOIN citas c ON hs.cita_id = c.id
                  WHERE c.especialista_id = :especialista_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $especialistaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener citas completadas sin reseña de un usuario
     */
    public function getAppointmentsWithoutReview($usuarioId) {
        $query = "SELECT c.*, 
                         s.nombre as nombre_servicio,
                         ue.nombre as nombre_especialista
                  FROM citas c
                  INNER JOIN servicios s ON c.servicio_id = s.id
                  INNER JOIN especialistas e ON c.especialista_id = e.id
                  INNER JOIN usuarios ue ON e.usuario_id = ue.id
                  LEFT JOIN " . $this->table . " hs ON c.id = hs.cita_id
                  WHERE c.usuario_id = :usuario_id
                  AND c.estado = :estado
                  AND hs.id IS NULL
                  ORDER BY c.fecha_cita DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $estado = APPOINTMENT_COMPLETED;
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>