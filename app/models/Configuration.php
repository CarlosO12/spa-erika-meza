<?php
/**
 * Modelo de Configuración del Sistema
 * SPA Erika Meza
 */
class Configuration {
    private $conn;
    private $table = 'configuracion_sistema';
    private static $cache = null;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todas las configuraciones
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY categoria, clave";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener configuraciones por categoría
     */
    public function getByCategory($categoria) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE categoria = :categoria 
                  ORDER BY clave";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener una configuración específica
     */
    public function get($clave) {
        if (self::$cache !== null && isset(self::$cache[$clave])) {
            return self::$cache[$clave];
        }
        
        $query = "SELECT valor FROM " . $this->table . " 
                  WHERE clave = :clave 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['valor'] : null;
    }
    
    /**
     * Actualizar una configuración
     */
    public function update($clave, $valor) {
        $query = "UPDATE " . $this->table . " 
                  SET valor = :valor 
                  WHERE clave = :clave";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':clave', $clave);
        
        $result = $stmt->execute();
        
        // Limpiar caché
        if ($result) {
            $this->clearCache();
        }
        
        return $result;
    }
    
    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMultiple($configuraciones) {
        $this->conn->beginTransaction();
        
        try {
            foreach ($configuraciones as $clave => $valor) {
                $this->update($clave, $valor);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    /**
     * Crear nueva configuración
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (clave, valor, tipo, categoria, descripcion) 
                  VALUES (:clave, :valor, :tipo, :categoria, :descripcion)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':clave', $data['clave']);
        $stmt->bindParam(':valor', $data['valor']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':categoria', $data['categoria']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        
        return $stmt->execute();
    }
    
    /**
     * Cargar todas las configuraciones en caché
     */
    public function loadCache() {
        if (self::$cache === null) {
            self::$cache = [];
            $configs = $this->getAll();
            
            foreach ($configs as $config) {
                self::$cache[$config['clave']] = $config['valor'];
            }
        }
    }
    
    /**
     * Limpiar caché
     */
    public function clearCache() {
        self::$cache = null;
    }
    
    /**
     * Obtener todas las categorías disponibles
     */
    public function getCategories() {
        $query = "SELECT DISTINCT categoria FROM " . $this->table . " 
                  ORDER BY categoria";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

/**
 * Función helper para obtener configuración
 */
function config($clave, $default = null) {
    static $configModel = null;
    
    if ($configModel === null) {
        $database = new Database();
        $db = $database->getConnection();
        $configModel = new Configuration($db);
        $configModel->loadCache();
    }
    
    $valor = $configModel->get($clave);
    return $valor !== null ? $valor : $default;
}
?>