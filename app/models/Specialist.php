<?php
/**
 * Modelo de Especialista
 * SPA Erika Meza
 */
class Specialist {
    private $conn;
    private $table = 'especialistas';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nuevo especialista
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (usuario_id, especialista, descripcion, experiencia, evaluacion) 
                  VALUES (:usuario_id, :especialista, :descripcion, :experiencia, :evaluacion)";
        
        $stmt = $this->conn->prepare($query);
        
        $evaluacion = $data['evaluacion'] ?? 5.0;
        
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        $stmt->bindParam(':especialista', $data['especialista']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':experiencia', $data['experiencia']);
        $stmt->bindParam(':evaluacion', $evaluacion);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Obtener todos los especialistas activos
     */
    public function getAll() {
        $query = "SELECT e.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table . " e
                  JOIN usuarios u ON e.usuario_id = u.id
                  WHERE u.rol = 'especialista'
                  ORDER BY u.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener especialista por ID
     */
    public function findById($id) {
        $query = "SELECT e.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table . " e
                  JOIN usuarios u ON e.usuario_id = u.id
                  WHERE e.id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Obtener especialista por ID de usuario
     */
    public function getByUserId($userId) {
        $query = "SELECT * FROM especialistas WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener especialista por usuario_id
     */
    public function findByUserId($userId) {
        $query = "SELECT e.*, u.nombre, u.email, u.telefono 
                  FROM " . $this->table . " e
                  JOIN usuarios u ON e.usuario_id = u.id
                  WHERE e.usuario_id = :usuario_id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $userId);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Actualizar especialista
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET especialista = :especialista, 
                      descripcion = :descripcion
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':especialista', $data['especialista']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        
        return $stmt->execute();
    }
    
    /**
     * Actualizar calificación
     */
    public function updateRating($id, $evaluacion) {
        $query = "UPDATE " . $this->table . " 
                  SET evaluacion = :evaluacion 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':evaluacion', $evaluacion);
        
        return $stmt->execute();
    }
    
    /**
     * Guardar horario del especialista
     */ 
    public function setSchedule($specialistId, $fecha, $horas) {
        $deleteQuery = "DELETE FROM disponibilidad_especialistas 
                       WHERE especialista_id = :especialista_id 
                       AND fecha = :fecha";
        
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':especialista_id', $specialistId);
        $deleteStmt->bindParam(':fecha', $fecha);
        $deleteStmt->execute();
        
        // Insertar nuevos horarios
        $insertQuery = "INSERT INTO disponibilidad_especialistas 
                       (especialista_id, dia_semana, hora_inicio, hora_fin) 
                       VALUES (:especialista_id, :dia_semana, :hora_inicio, :hora_fin)";
        
        $insertStmt = $this->conn->prepare($insertQuery);
        
        foreach ($horas as $hora) {
            $insertStmt->bindParam(':especialista_id', $specialistId);
            $insertStmt->bindParam(':dia_semana', $fecha);
            $insertStmt->bindParam(':hora_inicio', $hora['inicio']);
            $insertStmt->bindParam(':hora_fin', $hora['fin']);
            $insertStmt->execute();
        }
        
        return true;
    }

    public function addSchedule($data) {
        $query = "INSERT INTO disponibilidad_especialistas 
                (especialista_id, dia_semana, hora_inicio, hora_fin, activo) 
                VALUES (:especialista_id, :dia_semana, :hora_inicio, :hora_fin, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':especialista_id', $data['especialista_id'], PDO::PARAM_INT);
        $stmt->bindParam(':dia_semana', $data['dia_semana'], PDO::PARAM_INT);
        $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
        $stmt->bindParam(':hora_fin', $data['hora_fin']);
        
        // Por defecto activo = true
        $activo = $data['activo'] ?? 1;
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Obtener horarios de un especialista (solo activos o todos)
     */
    public function getSchedule($specialistId, $soloActivos = false) {
        $query = "SELECT * FROM disponibilidad_especialistas 
                WHERE especialista_id = :especialista_id";
        
        if ($soloActivos) {
            $query .= " AND activo = 1";
        }
        
        $query .= " ORDER BY dia_semana ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $specialistId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar horarios completos de un especialista
     */
    public function updateSchedule($specialistId, $horarios) {
        // Eliminar horarios existentes
        $deleteQuery = "DELETE FROM disponibilidad_especialistas 
                    WHERE especialista_id = :especialista_id";
        
        $deleteStmt = $this->conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':especialista_id', $specialistId);
        $deleteStmt->execute();
        
        // Insertar nuevos horarios
        foreach ($horarios as $horario) {
            $this->addSchedule($horario);
        }
        
        return true;
    }

    /**
     * Activar/Desactivar un horario específico
     */
    public function toggleScheduleStatus($id, $activo) {
        $query = "UPDATE disponibilidad_especialistas 
                SET activo = :activo 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar un horario específico
     */
    public function deleteSchedule($id) {
        $query = "DELETE FROM disponibilidad_especialistas WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Verificar disponibilidad en un día y hora específicos (solo horarios activos)
     */
    public function checkAvailability($specialistId, $diaSemana, $hora) {
        $query = "SELECT * FROM disponibilidad_especialistas 
                WHERE especialista_id = :especialista_id 
                AND dia_semana = :dia_semana 
                AND hora_inicio <= :hora 
                AND hora_fin >= :hora 
                AND activo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $specialistId);
        $stmt->bindParam(':dia_semana', $diaSemana);
        $stmt->bindParam(':hora', $hora);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener días activos de un especialista
     */
    public function getActiveDays($specialistId) {
        $query = "SELECT DISTINCT dia_semana 
                FROM disponibilidad_especialistas 
                WHERE especialista_id = :especialista_id 
                AND activo = 1
                ORDER BY FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $specialistId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Desactivar todos los horarios de un especialista
     */
    public function deactivateAllSchedules($specialistId) {
        $query = "UPDATE disponibilidad_especialistas 
                SET activo = 0 
                WHERE especialista_id = :especialista_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $specialistId);
        
        return $stmt->execute();
    }

    /**
     * Activar todos los horarios de un especialista
     */
    public function activateAllSchedules($specialistId) {
        $query = "UPDATE disponibilidad_especialistas 
                SET activo = 1 
                WHERE especialista_id = :especialista_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $specialistId);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas del especialista
     */
    public function getStats($specialistId) {
        $query = "SELECT 
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as citas_completadas,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as citas_canceladas
                  FROM citas 
                  WHERE especialista_id = :especialista_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':especialista_id', $specialistId);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Contar especialistas
     */
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
}
?>