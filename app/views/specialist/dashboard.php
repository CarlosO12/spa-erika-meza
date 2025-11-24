<?php
requireRole(ROLE_SPECIALIST);

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Specialist.php';
require_once APP_PATH . '/models/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$specialistModel = new Specialist($db);
$appointmentModel = new Appointment($db);

// Obtener información del especialista
$specialist = $specialistModel->findByUserId(getUserId());
$stats = $specialistModel->getStats($specialist['id']);

// Obtener citas de hoy
$today = date('Y-m-d');
$todayAppointments = $appointmentModel->getBySpecialist($specialist['id'], $today);

// Obtener próximas citas (próximos 7 días)
$upcomingAppointments = $appointmentModel->getBySpecialist($specialist['id']);
$upcomingAppointments = array_filter($upcomingAppointments, function($apt) use ($today) {
    return $apt['fecha_cita'] >= $today && $apt['fecha_cita'] <= date('Y-m-d', strtotime('+7 days'));
});
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-person-badge"></i> Dashboard de Especialista
            </h1>
            <p class="text-muted">Bienvenido, <?php echo e(getUserName()); ?></p>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-appointments?date=<?php echo date('Y-m-d'); ?>" 
               class="text-decoration-none">
                <div class="card stats-card shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Citas Hoy</p>
                                <h3 class="fw-bold mb-0"><?php echo count($todayAppointments); ?></h3>
                            </div>
                            <div class="stats-icon">
                                <i class="bi bi-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-appointments" 
               class="text-decoration-none">
                <div class="card stats-card shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Completadas</p>
                                <h3 class="fw-bold mb-0"><?php echo $stats['citas_completadas']; ?></h3>
                            </div>
                            <div class="stats-icon bg-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-schedule" 
               class="text-decoration-none">
                <div class="card stats-card shadow-sm hover-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Calificación</p>
                                <h3 class="fw-bold mb-0">
                                    <?php echo number_format($specialist['evaluacion'], 1); ?>
                                    <i class="bi bi-star-fill text-warning" style="font-size: 1.2rem;"></i>
                                </h3>
                            </div>
                            <div class="stats-icon bg-warning">
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Accesos Rápidos -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-schedule" 
               class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                        <h5 class="fw-bold">Ver Mi Horario</h5>
                        <p class="text-muted mb-0">Revisa tu calendario semanal</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-appointments" 
               class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3 bg-success">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <h5 class="fw-bold">Mis Citas</h5>
                        <p class="text-muted mb-0">Gestiona todas tus citas</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=profile" 
               class="text-decoration-none">
                <div class="card shadow-sm h-100 border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mx-auto mb-3 bg-info">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <h5 class="fw-bold">Mi Perfil</h5>
                        <p class="text-muted mb-0">Actualiza tu información</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Citas de Hoy -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-calendar-event"></i> Citas de Hoy - <?php echo formatDate($today); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($todayAppointments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No tienes citas programadas para hoy</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayAppointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?></strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle"></i>
                                        <?php echo e($appointment['nombre_cliente']); ?>
                                    </td>
                                    <td><?php echo e($appointment['nombre_servicio']); ?></td>
                                    <td><?php echo $appointment['duracion_servicio']; ?> min</td>
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
                                    <td>
                                        <?php if ($appointment['estado'] === 'confirmada'): ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=update-appointment-status" 
                                              style="display: inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="completada">
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    title="Marcar como completada">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewDetails(<?php echo htmlspecialchars(json_encode($appointment)); ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Próximas Citas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-calendar-week"></i> Próximas Citas (7 días)
                        </h5>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-appointments" 
                           class="btn btn-sm btn-outline-primary">
                            Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingAppointments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-check text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No tienes citas próximas programadas</p>
                    </div>
                    <?php else: ?>
                    <div class="row g-3">
                        <?php 
                        $displayCount = 0;
                        foreach ($upcomingAppointments as $appointment): 
                            if ($displayCount >= 6) break;
                            if ($appointment['fecha_cita'] === $today) continue;
                            $displayCount++;
                        ?>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-primary">
                                            <?php echo formatDate($appointment['fecha_cita']); ?>
                                        </span>
                                        <span class="badge bg-success">
                                            <?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?>
                                        </span>
                                    </div>
                                    <h6 class="fw-bold"><?php echo e($appointment['nombre_servicio']); ?></h6>
                                    <p class="text-muted mb-2 small">
                                        <i class="bi bi-person"></i> <?php echo e($appointment['nombre_cliente']); ?>
                                    </p>
                                    <p class="mb-0 small">
                                        <i class="bi bi-clock"></i> <?php echo $appointment['duracion_servicio']; ?> minutos
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles de Cita -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
                <!-- Se llenará con JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(appointment) {
    const detailsHTML = `
        <div class="mb-3">
            <strong>Cliente:</strong><br>
            ${appointment.nombre_cliente}<br>
            <small class="text-muted">${appointment.email_cliente}</small><br>
            <small class="text-muted">${appointment.telefono_cliente || 'N/A'}</small>
        </div>
        <div class="mb-3">
            <strong>Servicio:</strong><br>
            ${appointment.nombre_servicio}<br>
            <small class="text-muted">${appointment.descripcion_servicio}</small>
        </div>
        <div class="mb-3">
            <strong>Fecha y Hora:</strong><br>
            ${appointment.fecha_cita} a las ${appointment.hora_cita}
        </div>
        <div class="mb-3">
            <strong>Duración:</strong> ${appointment.duracion_servicio} minutos<br>
            <strong>Precio:</strong> ${formatPrice(appointment.precio_servicio)}
        </div>
        ${appointment.notas ? `
        <div class="alert-off alert-info">
            <strong>Notas del cliente:</strong><br>
            ${appointment.notas}
        </div>
        ` : ''}
    `;
    
    document.getElementById('appointmentDetails').innerHTML = detailsHTML;
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();
}

function formatPrice(price) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(price);
}
</script>

<style>
.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}
</style>