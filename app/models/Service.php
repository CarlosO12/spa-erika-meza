<?php
/**
 * Modelo de Servicio
 * SPA Erika Meza
 */
class Service {
    private $conn;
    private $table = 'servicios';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nuevo servicio
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (nombre, descripcion, precio, duracion, categoria, imagen, activo) 
                  VALUES (:nombre, :descripcion, :precio, :duracion, :categoria, :imagen, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $active = $data['activo'] ?? 1;
        
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':precio', $data['precio']);
        $stmt->bindParam(':duracion', $data['duracion']);
        $stmt->bindParam(':categoria', $data['categoria']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':activo', $active);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Obtener todos los servicios activos
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE activo = 1 
                  ORDER BY categoria, nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener todos los servicios (incluyendo inactivos)
     */
    public function getAllAdmin() {
        $query = "SELECT * FROM " . $this->table . " 
                  ORDER BY categoria, nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener servicio por ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Obtener servicios por categoría
     */
    public function getByCategory($categoria) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE categoria = :categoria AND activo = 1 
                  ORDER BY nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Actualizar servicio
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre, 
                      descripcion = :descripcion, 
                      precio = :precio, 
                      duracion = :duracion, 
                      categoria = :categoria, 
                      activo = :activo";
        
        if (isset($data['imagen']) && !empty($data['imagen'])) {
            $query .= ", imagen = :imagen";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':precio', $data['precio']);
        $stmt->bindParam(':duracion', $data['duracion']);
        $stmt->bindParam(':categoria', $data['categoria']);
        $stmt->bindParam(':activo', $data['activo']);
        
        if (isset($data['imagen']) && !empty($data['imagen'])) {
            $stmt->bindParam(':imagen', $data['imagen']);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar servicio
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActive($id, $activo) {
        $query = "UPDATE " . $this->table . " SET activo = :activo WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':activo', $activo);
        
        return $stmt->execute();
    }
    
    /**
     * Buscar servicios
     */
    public function search($search) {
    $query = "SELECT * FROM {$this->table}
              WHERE (nombre LIKE :nombre OR descripcion LIKE :descripcion)
              AND activo = 1
              ORDER BY nombre";

    $stmt = $this->conn->prepare($query);
    $searchTerm = "%{$search}%";
    $stmt->bindParam(':nombre', $searchTerm);
    $stmt->bindParam(':descripcion', $searchTerm);
    $stmt->execute();

    return $stmt->fetchAll();
}
    
    /**
     * Obtener categorías únicas
     */
    public function getCategories() {
        $query = "SELECT DISTINCT categoria FROM " . $this->table . " 
                  WHERE activo = 1 AND categoria IS NOT NULL 
                  ORDER BY categoria";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Contar servicios
     */
    public function count($activeOnly = true) {
        if ($activeOnly) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activo = 1";
        } else {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    /**
     * Obtener servicios más populares
     */
    public function getMostPopular($limit = 5) {
        $query = "SELECT s.*, COUNT(c.id) as total_citas
                  FROM " . $this->table . " s 
                  LEFT JOIN citas c ON s.id = c.servicio_id 
                  WHERE s.activo = 1 
                  GROUP BY s.id 
                  ORDER BY total_citas DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Verificar si un servicio tiene citas asociadas
     */
    public function hasAppointments($id) {
        $query = "SELECT COUNT(*) as total FROM citas WHERE servicio_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'] > 0;
    }
}
?>