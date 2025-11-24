<?php
/**
 * Modelo de Mensajes de Contacto
 * SPA Erika Meza
 */
class ContactMessage {
    private $conn;
    private $table = 'contacto_mensajes';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Guardar un nuevo mensaje de contacto
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (nombre, email, telefono, asunto, mensaje)
                  VALUES (:nombre, :email, :telefono, :asunto, :mensaje)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':asunto', $data['asunto']);
        $stmt->bindParam(':mensaje', $data['mensaje']);

        return $stmt->execute();
    }

    /**
     * Obtener todos los mensajes (para el panel admin)
     */
    public function getAll($estado = null, $busqueda = null) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($estado !== null && $estado !== '') {
            $query .= " AND estado = ?";
            $params[] = $estado;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $busqueda_param = '%' . trim($busqueda) . '%';
            $query .= " AND (nombre LIKE ? OR email LIKE ?)";
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
        }

        $query .= " ORDER BY fecha_envio DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un mensaje por ID
     */
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado del mensaje
     */
    public function updateStatus($id, $estado) {
        $query = "UPDATE {$this->table} SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Eliminar mensaje
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar mensajes por estado
     */
    public function countByStatus($estado = null) {
        if ($estado) {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE estado = :estado";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
        } else {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Obtener mensajes con paginación
     */
    public function paginate($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM {$this->table} 
                  ORDER BY fecha_envio DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener mensajes recientes (últimos 5)
     */
    public function getRecent($limit = 5) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE estado = 'nuevo'
                  ORDER BY fecha_envio DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>