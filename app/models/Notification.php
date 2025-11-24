<?php
/**
 * Modelo de Notificación
 * SPA Erika Meza
 */
class Notification {
    private $conn;
    private $table = 'notificaciones';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nueva notificación
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (usuario_id, tipo, sujeto, mensaje, enviado) 
                  VALUES (:usuario_id, :tipo, :sujeto, :mensaje, :enviado)";
        
        $stmt = $this->conn->prepare($query);
        
        $enviado = $data['enviado'] ?? 0;
        
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':sujeto', $data['sujeto']);
        $stmt->bindParam(':mensaje', $data['mensaje']);
        $stmt->bindParam(':enviado', $enviado);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Obtener notificaciones de un usuario
     */
    public function getByUser($userId, $soloNoLeidas = false) {
        if ($soloNoLeidas) {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE usuario_id = :usuario_id 
                      AND enviado = 0 
                      ORDER BY creado DESC";
        } else {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE usuario_id = :usuario_id 
                      ORDER BY creado DESC 
                      LIMIT 50";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead($id) {
        $query = "UPDATE " . $this->table . " 
                  SET enviado = 1 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead($userId) {
        $query = "UPDATE " . $this->table . " 
                  SET enviado = 1 
                  WHERE usuario_id = :usuario_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar notificación
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public function countUnread($userId) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE usuario_id = :usuario_id 
                  AND enviado = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    /**
     * Crear notificación de cita
     */
    public function createAppointmentNotification($userId, $appointmentInfo, $tipo = 'cita') {
        $data = [
            'usuario_id' => $userId,
            'tipo' => $tipo,
            'sujeto' => 'Actualización de Cita',
            'mensaje' => $appointmentInfo,
            'enviado' => 0
        ];
        
        return $this->create($data);
    }
    
    /**
     * Limpiar notificaciones antiguas
     */
    public function cleanOldNotifications($dias = 30) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE enviado = 1 
                  AND creado < DATE_SUB(NOW(), INTERVAL :dias DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
?>