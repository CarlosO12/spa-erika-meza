<?php
/**
 * Controlador de Administración
 * SPA Erika Meza
 */
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Specialist.php';

class AdminController {
    private $db;
    private $userModel;
    private $serviceModel;
    private $appointmentModel;
    private $specialistModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->serviceModel = new Service($this->db);
        $this->appointmentModel = new Appointment($this->db);
        $this->specialistModel = new Specialist($this->db);
    }

    /**
     * Actualizar configuración del sistema
     */
    public function updateSettings() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-settings');
                exit();
            }
            
            require_once APP_PATH . '/models/Configuration.php';
            $configModel = new Configuration($this->db);
            
            // Obtener todas las claves actuales
            $allConfigs = $configModel->getAll();
            $updates = [];
            
            foreach ($allConfigs as $config) {
                $clave = $config['clave'];
                
                if ($config['tipo'] === 'boolean') {
                    $updates[$clave] = isset($_POST[$clave]) ? '1' : '0';
                } 
                elseif (isset($_POST[$clave])) {
                    $updates[$clave] = cleanString($_POST[$clave]);
                }
            }
            
            // Actualizar todas las configuraciones
            if ($configModel->updateMultiple($updates)) {
                setFlashMessage('success', 'Configuración actualizada exitosamente.');
            } else {
                setFlashMessage('error', 'Error al actualizar la configuración.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-settings');
            exit();
        }
    }
    
    /**
     * Obtener estadísticas del dashboard
     */
    public function getDashboardStats() {
        requireAdmin();
        
        return [
            'total_usuarios' => $this->userModel->countByRole(ROLE_CLIENT),
            'total_especialistas' => $this->userModel->countByRole(ROLE_SPECIALIST),
            'total_servicios' => $this->serviceModel->count(true),
            'total_citas' => $this->appointmentModel->countByStatus(),
            'citas_pendientes' => $this->appointmentModel->countByStatus(APPOINTMENT_PENDING),
            'citas_confirmadas' => $this->appointmentModel->countByStatus(APPOINTMENT_CONFIRMED),
            'citas_completadas' => $this->appointmentModel->countByStatus(APPOINTMENT_COMPLETED)
        ];
    }
    
    /**
     * Crear usuario
     */
    public function createUser() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-users');
                exit();
            }
            
            $data = [
                'nombre' => cleanString($_POST['nombre']),
                'email' => cleanEmail($_POST['email']),
                'telefono' => cleanPhone($_POST['telefono']),
                'password' => $_POST['password'],
                'rol' => cleanString($_POST['rol'])
            ];
            
            // Validar rol
            $validRoles = [ROLE_ADMIN, ROLE_SPECIALIST, ROLE_CLIENT];
            if (!in_array($data['rol'], $validRoles)) {
                setFlashMessage('error', 'Rol inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-users');
                exit();
            }
            
            // Crear usuario
            $userId = $this->userModel->create($data);
            
            if ($userId) {
                // Si es especialista, crear registro en tabla especialistas
                if ($data['rol'] === ROLE_SPECIALIST) {
                    $specialistData = [
                        'usuario_id' => $userId,
                        'especialista' => cleanString($_POST['especialista'] ?? ''),
                        'descripcion' => cleanText($_POST['descripcion'] ?? ''),
                        'experiencia' => $_POST['experiencia'] ?? 0
                    ];
                    $specialistId = $this->specialistModel->create($specialistData);
                    
                    // Guardar horarios si se proporcionaron
                    if ($specialistId && !empty($_POST['dias_activos'])) {
                        $diasActivos = $_POST['dias_activos'];
                        $horasInicio = $_POST['hora_inicio'] ?? [];
                        $horasFin = $_POST['hora_fin'] ?? [];
                        
                        foreach ($diasActivos as $dia) {
                            $horaInicio = $horasInicio[$dia] ?? '09:00';
                            $horaFin = $horasFin[$dia] ?? '18:00';
                            
                            // Validar que hora fin sea mayor que hora inicio
                            if ($horaFin > $horaInicio) {
                                $horarioData = [
                                    'especialista_id' => $specialistId,
                                    'dia_semana' => (int)$dia,
                                    'hora_inicio' => $horaInicio,
                                    'hora_fin' => $horaFin,
                                    'activo' => 1
                                ];
                                $this->specialistModel->addSchedule($horarioData);
                            }
                        }
                    }
                }
                
                setFlashMessage('success', 'Usuario creado exitosamente.');
            } else {
                setFlashMessage('error', 'Error al crear el usuario.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-users');
            exit();
        }
    }

    /**
     * Actualizar horario de especialista
     */
    public function updateSpecialistSchedule() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $specialistId = (int)$_POST['especialista_id'];
            
            if (!$specialistId) {
                setFlashMessage('error', 'ID de especialista inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-users');
                exit();
            }
            
            // Obtener datos del formulario
            $diasActivos = $_POST['dias_activos'] ?? [];
            $horasInicio = $_POST['hora_inicio'] ?? [];
            $horasFin = $_POST['hora_fin'] ?? [];
            
            // Preparar horarios para actualizar
            $horarios = [];
            foreach ($diasActivos as $dia) {
                $horaInicio = $horasInicio[$dia] ?? '09:00';
                $horaFin = $horasFin[$dia] ?? '18:00';
                
                if ($horaFin > $horaInicio) {
                    $horarios[] = [
                        'especialista_id' => $specialistId,
                        'dia_semana' => $dia,
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin,
                        'activo' => 1
                    ];
                }
            }
            
            // Actualizar horarios
            if ($this->specialistModel->updateSchedule($specialistId, $horarios)) {
                setFlashMessage('success', 'Horarios actualizados correctamente.');
            } else {
                setFlashMessage('error', 'Error al actualizar los horarios.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-users');
            exit();
        }
    }

    /**
     * Activar/Desactivar un horario específico
     */
    public function toggleScheduleStatus() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $scheduleId = (int)$_POST['schedule_id'];
            $activo = (int)$_POST['activo'];
            
            if ($this->specialistModel->toggleScheduleStatus($scheduleId, $activo)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit();
        }
    }

    /**
     * Desactivar temporalmente un especialista (todos sus horarios)
     */
    public function deactivateSpecialist() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $specialistId = (int)$_POST['especialista_id'];
            
            if ($this->specialistModel->deactivateAllSchedules($specialistId)) {
                setFlashMessage('success', 'Especialista desactivado temporalmente.');
            } else {
                setFlashMessage('error', 'Error al desactivar el especialista.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-users');
            exit();
        }
    }

    /**
     * Reactivar un especialista (todos sus horarios)
     */
    public function activateSpecialist() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $specialistId = (int)$_POST['especialista_id'];
            
            if ($this->specialistModel->activateAllSchedules($specialistId)) {
                setFlashMessage('success', 'Especialista reactivado correctamente.');
            } else {
                setFlashMessage('error', 'Error al reactivar el especialista.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-users');
            exit();
        }
    }

    /**
     * Obtener datos de un especialista para edición
     */
    public function getSpecialistData() {
        requireAdmin();
        
        $userId = (int)$_GET['user_id'];
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit();
        }
        
        try {
            // Obtener datos del especialista
            $especialista = $this->specialistModel->getByUserId($userId);
            
            if (!$especialista) {
                echo json_encode(['success' => false, 'message' => 'Especialista no encontrado']);
                exit();
            }
            
            // Obtener horarios
            $horarios = $this->specialistModel->getSchedule($especialista['id']);
            
            echo json_encode([
                'success' => true,
                'especialista' => $especialista,
                'horarios' => $horarios
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit();
    }
    
    /**
     * Actualizar usuario
     */
    public function updateUser() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Token de seguridad inválido.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-users');
                exit();
            }
            
            $userId = (int)$_POST['usuario_id'];
            $userRol = $_POST['user_rol'] ?? '';
            
            // Actualizar datos básicos del usuario
            $userData = [
                'nombre' => cleanString($_POST['nombre']),
                'telefono' => cleanPhone($_POST['telefono']),
                'direccion' => cleanText($_POST['direccion'] ?? '')
            ];
            
            if ($this->userModel->update($userId, $userData)) {
                // Si es especialista, actualizar datos adicionales
                if ($userRol === ROLE_SPECIALIST) {
                    $especialistaId = (int)$_POST['especialista_id'];
                    
                    if ($especialistaId) {
                        // Actualizar datos del especialista
                        $specialistData = [
                            'especialista' => cleanString($_POST['especialista'] ?? ''),
                            'descripcion' => cleanText($_POST['descripcion'] ?? '')
                        ];
                        $this->specialistModel->update($especialistaId, $specialistData);
                        
                        // Actualizar horarios si se proporcionaron
                        if (!empty($_POST['dias_activos'])) {
                            $diasActivos = $_POST['dias_activos'];
                            $horasInicio = $_POST['hora_inicio'] ?? [];
                            $horasFin = $_POST['hora_fin'] ?? [];
                            
                            $horarios = [];
                            foreach ($diasActivos as $dia) {
                                $horaInicio = $horasInicio[$dia] ?? '09:00';
                                $horaFin = $horasFin[$dia] ?? '18:00';
                                
                                if ($horaFin > $horaInicio) {
                                    $horarios[] = [
                                        'especialista_id' => $especialistaId,
                                        'dia_semana' => (int)$dia,
                                        'hora_inicio' => $horaInicio,
                                        'hora_fin' => $horaFin,
                                        'activo' => 1
                                    ];
                                }
                            }
                            
                            $this->specialistModel->updateSchedule($especialistaId, $horarios);
                        }
                    }
                }
                
                setFlashMessage('success', 'Usuario actualizado exitosamente.');
            } else {
                setFlashMessage('error', 'Error al actualizar el usuario.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-users');
            exit();
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUser() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)$_POST['usuario_id'];
            
            // No permitir eliminar al usuario actual
            if ($userId === getUserId()) {
                setFlashMessage('error', 'No puedes eliminar tu propia cuenta.');
                header('Location: ' . BASE_URL . '/index.php?page=admin-users');
                exit();
            }
            
            if ($this->userModel->delete($userId)) {
                setFlashMessage('success', MSG_SUCCESS_DELETE);
            } else {
                setFlashMessage('error', 'Error al eliminar el usuario.');
            }
            
            header('Location: ' . BASE_URL . '/index.php?page=admin-users');
            exit();
        }
    }
    
    /**
     * Generar reporte de servicios
     */
    public function generateServicesReport($startDate, $endDate) {
        requireAdmin();

        $query = "
            SELECT 
                s.id,
                s.nombre,
                s.categoria,
                s.precio,
                COUNT(c.id) AS total_citas,
                COALESCE(SUM(s.precio), 0) AS ingresos_estimados,
                s.activo
            FROM servicios s
            LEFT JOIN citas c 
                ON s.id = c.servicio_id
                AND c.fecha_cita BETWEEN :start AND :end
                AND c.estado = 'completada'
            GROUP BY s.id, s.nombre, s.categoria, s.precio, s.activo
            ORDER BY total_citas DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start', $startDate);
        $stmt->bindParam(':end', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generar reporte de ingresos
     */
    public function generateRevenueReport($startDate, $endDate) {
        requireAdmin();
        
        $query = "SELECT 
                    DATE(c.fecha_cita) as fecha,
                    COUNT(*) as total_citas,
                    SUM(s.precio) as ingresos
                  FROM citas c
                  JOIN servicios s ON c.servicio_id = s.id
                  WHERE c.fecha_cita BETWEEN :start AND :end
                  AND c.estado = 'completada'
                  GROUP BY DATE(c.fecha_cita)
                  ORDER BY fecha DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start', $startDate);
        $stmt->bindParam(':end', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Exportar datos a CSV
     */
    public function exportToCSV($type) {
        requireAdmin();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $type . '_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        switch ($type) {
            case 'users':
                fputcsv($output, ['ID', 'Nombre', 'Email', 'Teléfono', 'Rol', 'Verificado', 'Fecha Registro']);
                $users = $this->userModel->getAll();
                foreach ($users as $user) {
                    fputcsv($output, [
                        $user['id'],
                        $user['nombre'],
                        $user['email'],
                        $user['telefono'],
                        $user['rol'],
                        $user['verificado'] ? 'Sí' : 'No',
                        $user['creado']
                    ]);
                }
                break;
                
            case 'appointments':
                fputcsv($output, ['ID', 'Cliente', 'Servicio', 'Especialista', 'Fecha', 'Hora', 'Estado']);
                $appointments = $this->appointmentModel->getAll();
                foreach ($appointments as $appointment) {
                    fputcsv($output, [
                        $appointment['id'],
                        $appointment['nombre_cliente'],
                        $appointment['nombre_servicio'],
                        $appointment['nombre_especialista'],
                        $appointment['fecha_cita'],
                        $appointment['hora_cita'],
                        $appointment['estado']
                    ]);
                }
                break;
        }
        
        fclose($output);
        exit();
    }

    /**
     * Exportar datos a PDF
     */
    public function exportToPDF($type)
    {
        requireAdmin();

        require_once ROOT_PATH . '/vendor/autoload.php';
        mb_internal_encoding("UTF-8");

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Encabezado general
        $pdf->Cell(
            0,
            10,
            mb_convert_encoding('SPA Erika Meza - Reporte de ' . ucfirst($type), 'ISO-8859-1', 'UTF-8'),
            0,
            1,
            'C'
        );
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(
            0,
            10,
            mb_convert_encoding('Fecha de generación: ' . date('d/m/Y'), 'ISO-8859-1', 'UTF-8'),
            0,
            1,
            'C'
        );
        $pdf->Ln(10);

        // Encabezados de tabla
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(135, 155, 241);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(255, 255, 255);

        if ($type === 'appointments') {
            $headers = ['ID', 'Cliente', 'Servicio', 'Especialista', 'Fecha', 'Hora', 'Estado'];
            $widths = [10, 35, 35, 35, 25, 20, 30];

            foreach ($headers as $i => $header) {
                $pdf->Cell($widths[$i], 10, mb_convert_encoding($header, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Datos
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetTextColor(0, 0, 0);

            $appointments = $this->appointmentModel->getAll();
            $fill = false;

            foreach ($appointments as $appointment) {
                $pdf->Cell($widths[0], 8, $appointment['id'], 1, 0, 'C', $fill);
                $pdf->Cell($widths[1], 8, mb_convert_encoding($appointment['nombre_cliente'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell($widths[2], 8, mb_convert_encoding($appointment['nombre_servicio'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell($widths[3], 8, mb_convert_encoding($appointment['nombre_especialista'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell($widths[4], 8, $appointment['fecha_cita'], 1, 0, 'C', $fill);
                $pdf->Cell($widths[5], 8, $appointment['hora_cita'], 1, 0, 'C', $fill);
                $pdf->Cell($widths[6], 8, mb_convert_encoding($appointment['estado'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', $fill);
                $pdf->Ln();
                $fill = !$fill;
            }
        }

        // Pie de página
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(
            0,
            10,
            mb_convert_encoding('© ' . date('Y') . ' SPA Erika Meza - Reporte generado automáticamente.', 'ISO-8859-1', 'UTF-8'),
            0,
            0,
            'C'
        );

        $pdf->Output('D', $type . '_' . date('Y-m-d') . '.pdf');
        exit();
    }
}
?>