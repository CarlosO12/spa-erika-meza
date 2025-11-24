<?php
requireRole(ROLE_CLIENT);

require_once APP_PATH . '/controllers/UserController.php';
$userController = new UserController();
$dashboardData = $userController->getDashboard();
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-house-door"></i> Mi Dashboard
            </h1>
            <p class="text-muted">Bienvenido, <?php echo e(getUserName()); ?></p>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Citas Programadas</p>
                            <h3 class="fw-bold mb-0"><?php echo count($dashboardData['upcoming']); ?></h3>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total de Citas</p>
                            <h3 class="fw-bold mb-0"><?php echo $dashboardData['total']; ?></h3>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Acciones Rápidas -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=services" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                        <h5 class="fw-bold">Explorar Servicios</h5>
                        <p class="text-muted mb-0">Descubre todos nuestros servicios disponibles</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=specialists" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3 bg-warning">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-bold">Especialistas</h5>
                        <p class="text-muted mb-0">Conoce nuestro equipo y lee opiniones</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3 bg-success">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                        <h5 class="fw-bold">Agendar Cita</h5>
                        <p class="text-muted mb-0">Reserva tu próxima cita fácilmente</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=history" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3 bg-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h5 class="fw-bold">Mi Historial</h5>
                        <p class="text-muted mb-0">Revisa tus servicios anteriores</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Próximas Citas -->
    <?php if (!empty($dashboardData['upcoming'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-calendar-event"></i> Próximas Citas
                        </h5>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=my-appointments" 
                           class="btn btn-sm btn-outline-primary">
                            Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach (array_slice($dashboardData['upcoming'], 0, 3) as $appointment): ?>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="fw-bold"><?php echo e($appointment['nombre_servicio']); ?></h6>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-person"></i> <?php echo e($appointment['nombre_especialista']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-calendar3"></i> 
                                        <?php echo formatDate($appointment['fecha_cita']); ?>
                                    </p>
                                    <p class="mb-3">
                                        <i class="bi bi-clock"></i> 
                                        <?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?>
                                    </p>
                                    <span class="badge bg-success">Confirmada</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No tienes citas programadas</h5>
                    <p class="text-muted">¡Agenda tu primera cita ahora!</p>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" 
                       class="btn btn-primary">
                        <i class="bi bi-calendar-plus"></i> Agendar Cita
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>