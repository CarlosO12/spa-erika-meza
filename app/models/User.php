<?php
/**
 * Modelo de Usuario
 * SPA Erika Meza
 */

class User {
    private $conn;
    private $table = 'usuarios';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (email, password_hash, rol, nombre, telefono, codigo_verificacion, expira_verificacion) 
                  VALUES (:email, :password_hash, :rol, :nombre, :telefono, :codigo_verificacion, :expira_verificacion)";
        
        $stmt = $this->conn->prepare($query);
        
        // Generar código de verificación
        $codigoVerificacion = sprintf("%06d", mt_rand(1, 999999));
        $expiraVerificacion = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Hash de contraseña
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':rol', $data['rol']);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':codigo_verificacion', $codigoVerificacion);
        $stmt->bindParam(':expira_verificacion', $expiraVerificacion);
        
        if ($stmt->execute()) {
            sendVerificationEmail($data['email'], $data['nombre'], $codigoVerificacion);
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Buscar usuario por ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Verificar contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Verificar cuenta con código
     */
    public function verifyAccount($email, $code) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE email = :email 
                  AND codigo_verificacion = :code 
                  AND expira_verificacion > NOW() 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user) {
            $updateQuery = "UPDATE " . $this->table . " 
                           SET verificado = 1, 
                               codigo_verificacion = NULL, 
                               expira_verificacion = NULL 
                           WHERE id = :id";
            
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':id', $user['id']);
            
            if ($updateStmt->execute()) {
                sendWelcomeEmail($user['email'], $user['nombre']);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre, 
                      telefono = :telefono, 
                      direccion = :direccion 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        
        return $stmt->execute();
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($id, $nuevaPassword) {
        $query = "UPDATE " . $this->table . " 
                  SET password_hash = :password_hash 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password_hash', $passwordHash);
        
        return $stmt->execute();
    }
    
    /**
     * Generar token de recuperación de contraseña
     */
    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "UPDATE " . $this->table . " 
                  SET token_restablecido = :token, 
                      expira_restablecido = :expires 
                  WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            sendPasswordResetEmail($email, $user['nombre'], $token);
            return $token;
        }
        
        return false;
    }
    
    /**
     * Verificar token de recuperación
     */
    public function verifyResetToken($token) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE token_restablecido = :token 
                  AND expira_restablecido > NOW() 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Restablecer contraseña con token
     */
    public function resetPassword($token, $nuevaPassword) {
        $user = $this->verifyResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table . " 
                  SET password_hash = :password_hash, 
                      token_restablecido = NULL, 
                      expira_restablecido = NULL 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':id', $user['id']);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function getAll($rol = null, $busqueda = null, $verificado = null) {
        $query = "SELECT id, email, nombre, telefono, rol, verificado, creado, direccion 
                FROM " . $this->table . " 
                WHERE 1=1";
        
        $params = [];
        
        if ($rol !== null && $rol !== '') {
            $query .= " AND rol = ?";
            $params[] = $rol;
        }
        
        if ($busqueda !== null && $busqueda !== '') {
            $busqueda_param = '%' . trim($busqueda) . '%';
            $query .= " AND (nombre LIKE ? OR email LIKE ?)";
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
        }
        
        if ($verificado !== null && $verificado !== '') {
            $query .= " AND verificado = ?";
            $params[] = (int)$verificado;
        }
        
        $query .= " ORDER BY creado DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Eliminar usuario
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si email existe
     */
    public function emailExists($email) {
        return $this->findByEmail($email) !== false;
    }
    
    /**
     * Contar usuarios por rol
     */
    public function countByRole($rol = null) {
        if ($rol) {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE rol = :rol";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':rol', $rol);
        } else {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    /**
     * Obtener usuarios con paginación
     */
    public function paginate($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT id, email, nombre, telefono, rol, verificado, creado 
                  FROM " . $this->table . " 
                  ORDER BY creado DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>