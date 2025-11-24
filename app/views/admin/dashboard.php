<?php
requireAdmin();

require_once APP_PATH . '/controllers/AdminController.php';
$adminController = new AdminController();
$stats = $adminController->getDashboardStats();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-speedometer2"></i> Dashboard Administrativo
            </h1>
            <p class="text-muted">Bienvenido, <?php echo e(getUserName()); ?></p>
        </div>
    </div>
    
    <!-- Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stats-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Usuarios</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_usuarios']; ?></h3>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Especialistas</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_especialistas']; ?></h3>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Servicios Activos</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_servicios']; ?></h3>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Citas Totales</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_citas']; ?></h3>
                        </div>
                        <div class="stats-icon bg-warning">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de Citas por Estado -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-graph-up"></i> Estado de Citas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3">
                                <h2 class="text-warning fw-bold"><?php echo $stats['citas_pendientes']; ?></h2>
                                <p class="text-muted mb-0">Pendientes</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <h2 class="text-success fw-bold"><?php echo $stats['citas_confirmadas']; ?></h2>
                                <p class="text-muted mb-0">Confirmadas</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <h2 class="text-info fw-bold"><?php echo $stats['citas_completadas']; ?></h2>
                                <p class="text-muted mb-0">Completadas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-lightning"></i> Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-users" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-people"></i> Gestionar Usuarios
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-services" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-grid"></i> Gestionar Servicios
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-appointments" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-calendar"></i> Ver Citas
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-reports" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text"></i> Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Últimas Citas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-clock-history"></i> Últimas Citas
                        </h5>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-appointments" 
                           class="btn btn-sm btn-outline-primary">
                            Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    require_once APP_PATH . '/models/Appointment.php';
                    $database = new Database();
                    $db = $database->getConnection();
                    $appointmentModel = new Appointment($db);
                    $recentAppointments = array_slice($appointmentModel->getAll(), 0, 5);
                    ?>
                    
                    <?php if (!empty($recentAppointments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Especialista</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAppointments as $appointment): ?>
                                <tr>
                                    <td><?php echo e($appointment['nombre_cliente']); ?></td>
                                    <td><?php echo e($appointment['nombre_servicio']); ?></td>
                                    <td><?php echo e($appointment['nombre_especialista']); ?></td>
                                    <td><?php echo formatDate($appointment['fecha_cita']); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = [
                                            'pendiente' => 'bg-warning',
                                            'confirmada' => 'bg-success',
                                            'completada' => 'bg-info',
                                            'cancelada' => 'bg-danger'
                                        ];
                                        $class = $badgeClass[$appointment['estado']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $class; ?>">
                                            <?php echo ucfirst($appointment['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-center text-muted py-4">No hay citas registradas</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>