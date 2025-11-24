<?php
/**
 * Controlador de Servicios
 * SPA Erika Meza
 */
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Service.php';

class ServiceController {
    private $db;
    private $serviceModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->serviceModel = new Service($this->db);
    }
    
    /**
     * Crear nuevo servicio (Admin)
     */
    public function create() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-services');
                exit();
            }
            
            $data = [
                'nombre' => cleanString($_POST['nombre']),
                'descripcion' => cleanText($_POST['descripcion']),
                'precio' => cleanNumber($_POST['precio']),
                'duracion' => (int)$_POST['duracion'],
                'categoria' => cleanString($_POST['categoria']),
                'imagen' => '',
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];
            
            // Validar datos
            $errors = [];
            
            if (!required($data['nombre'])) {
                $errors[] = 'El nombre es requerido.';
            }
            
            if (!isPositive($data['precio'])) {
                $errors[] = 'El precio debe ser mayor a 0.';
            }
            
            if (!isPositive($data['duracion'])) {
                $errors[] = 'La duración debe ser mayor a 0.';
            }
            
            // Procesar imagen si existe
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImage($_FILES['imagen']);
                if ($uploadResult['success']) {
                    $data['imagen'] = $uploadResult['filename'];
                } else {
                    $errors[] = $uploadResult['message'];
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_data'] = $data;
                header('Location: ' . BASE_URL . '/index.php?page=admin-services');
                exit();
            }
            
            // Crear servicio
            if ($this->serviceModel->create($data)) {
                setFlashMessage('success', 'Servicio creado exitosamente.');
            } else {
                setFlashMessage('error', 'Error al crear el servicio.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-services');
            exit();
        }
    }
    
    /**
     * Actualizar servicio (Admin)
     */
    public function update() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-services');
                exit();
            }
            
            $serviceId = (int)$_POST['service_id'];
            
            $data = [
                'nombre' => cleanString($_POST['nombre']),
                'descripcion' => cleanText($_POST['descripcion']),
                'precio' => cleanNumber($_POST['precio']),
                'duracion' => (int)$_POST['duracion'],
                'categoria' => cleanString($_POST['categoria']),
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];
            
            // Procesar imagen si existe
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImage($_FILES['imagen']);
                if ($uploadResult['success']) {
                    $data['imagen'] = $uploadResult['filename'];
                }
            }
            
            // Actualizar servicio
            if ($this->serviceModel->update($serviceId, $data)) {
                setFlashMessage('success', MSG_SUCCESS_UPDATE);
            } else {
                setFlashMessage('error', 'Error al actualizar el servicio.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-services');
            exit();
        }
    }
    
    /**
     * Eliminar servicio (Admin)
     */
    public function delete() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceId = (int)$_POST['servicio_id'];
            
            // Verificar si tiene citas asociadas
            if ($this->serviceModel->hasAppointments($serviceId)) {
                setFlashMessage('error', 'No se puede eliminar un servicio con citas asociadas.');
            } else {
                if ($this->serviceModel->delete($serviceId)) {
                    setFlashMessage('success', MSG_SUCCESS_DELETE);
                } else {
                    setFlashMessage('error', 'Error al eliminar el servicio.');
                }
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-services');
            exit();
        }
    }
    
    /**
     * Agregar servicio al carrito
     */
    public function addToCart() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceId = (int)$_POST['servicio_id'];
            $service = $this->serviceModel->findById($serviceId);
            
            if (!$service) {
                setFlashMessage('error', MSG_ERROR_NOT_FOUND);
                header('Location: ' . BASE_URL . '/index.php?page=services');
                exit();
            }
            
            // Inicializar carrito si no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            
            // Agregar al carrito
            $_SESSION['carrito'][$serviceId] = [
                'id' => $service['id'],
                'nombre' => $service['nombre'],
                'precio' => $service['precio'],
                'duracion' => $service['duracion']
            ];
            
            setFlashMessage('success', 'Servicio agregado al carrito.');
            header('Location: ' . BASE_URL . '/index.php?page=services');
            exit();
        }
    }
    
    /**
     * Remover servicio del carrito
     */
    public function removeFromCart() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceId = (int)$_POST['servicio_id'];
            
            if (isset($_SESSION['carrito'][$serviceId])) {
                unset($_SESSION['carrito'][$serviceId]);
                setFlashMessage('success', 'Servicio removido del carrito.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=cart');
            exit();
        }
    }
    
    /**
     * Limpiar carrito
     */
    public function clearCart() {
        requireAuth();
        $_SESSION['carrito'] = [];
        setFlashMessage('success', 'Carrito vaciado.');
        header('Location: ' . BASE_URL . '/index.php?page=cart');
        exit();
    }
    
    /**
     * Subir imagen de servicio
     */
    private function uploadImage($file) {
        if (!validateImage($file)) {
            return ['success' => false, 'message' => 'Tipo de archivo no válido.'];
        }
        
        if (!validateFileSize($file['size'])) {
            return ['success' => false, 'message' => 'El archivo es demasiado grande.'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'service_' . uniqid() . '.' . $extension;
        $targetPath = UPLOAD_PATH . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
    
    /**
     * Buscar servicios (AJAX)
     */
    public function search() {
        header('Content-Type: application/json');
        
        $query = cleanString($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            exit();
        }
        
        $services = $this->serviceModel->search($query);
        
        $results = array_map(function($service) {
            return [
                'id' => $service['id'],
                'title' => $service['nombre'],
                'description' => truncate($service['descripcion'], 80),
                'price' => formatPrice($service['precio']),
                'url' => BASE_URL . '/index.php?page=service-detail&id=' . $service['id']
            ];
        }, $services);
        
        echo json_encode($results);
        exit();
    }
}
?>