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

// Filtros - CORREGIDO: usar los nombres correctos de parámetros
$dateFilter = $_GET['date'] ?? null;
$statusFilter = $_GET['status'] ?? null;

// Obtener citas
if ($dateFilter) {
    $appointments = $appointmentModel->getBySpecialist($specialist['id'], $dateFilter);
} else {
    $appointments = $appointmentModel->getBySpecialist($specialist['id']);
}

// Aplicar filtro de estado
if ($statusFilter) {
    $appointments = array_filter($appointments, function($apt) use ($statusFilter) {
        return $apt['estado'] === $statusFilter;
    });
}

// Agrupar por fecha
$groupedAppointments = [];
foreach ($appointments as $appointment) {
    $date = $appointment['fecha_cita'];
    if (!isset($groupedAppointments[$date])) {
        $groupedAppointments[$date] = [];
    }
    $groupedAppointments[$date][] = $appointment;
}
ksort($groupedAppointments);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-calendar-event"></i> Mis Citas
            </h1>
            <p class="text-muted">Gestiona tus citas programadas</p>
        </div>
    </div>
    
    <!-- Estadísticas Rápidas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Citas</p>
                            <h3 class="fw-bold mb-0"><?php echo count($appointments); ?></h3>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Hoy</p>
                            <h3 class="fw-bold mb-0 text-primary">
                                <?php 
                                $today = date('Y-m-d');
                                echo isset($groupedAppointments[$today]) ? count($groupedAppointments[$today]) : 0;
                                ?>
                            </h3>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Confirmadas</p>
                            <h3 class="fw-bold mb-0 text-success">
                                <?php 
                                $confirmed = array_filter($appointments, function($a) {
                                    return $a['estado'] === APPOINTMENT_CONFIRMED;
                                });
                                echo count($confirmed);
                                ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Pendientes</p>
                            <h3 class="fw-bold mb-0 text-warning">
                                <?php 
                                $pending = array_filter($appointments, function($a) {
                                    return $a['estado'] === APPOINTMENT_PENDING;
                                });
                                echo count($pending);
                                ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <input type="hidden" name="page" value="specialist-appointments">
                
                <div class="col-md-4">
                    <label for="date" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos los estados</option>
                        <option value="<?php echo APPOINTMENT_PENDING; ?>" 
                                <?php echo $statusFilter === APPOINTMENT_PENDING ? 'selected' : ''; ?>>
                            Pendientes
                        </option>
                        <option value="<?php echo APPOINTMENT_CONFIRMED; ?>" 
                                <?php echo $statusFilter === APPOINTMENT_CONFIRMED ? 'selected' : ''; ?>>
                            Confirmadas
                        </option>
                        <option value="<?php echo APPOINTMENT_COMPLETED; ?>" 
                                <?php echo $statusFilter === APPOINTMENT_COMPLETED ? 'selected' : ''; ?>>
                            Completadas
                        </option>
                        <option value="<?php echo APPOINTMENT_CANCELLED; ?>" 
                                <?php echo $statusFilter === APPOINTMENT_CANCELLED ? 'selected' : ''; ?>>
                            Canceladas
                        </option>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=specialist-appointments" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Citas Agrupadas por Fecha -->
    <?php if (empty($groupedAppointments)): ?>
    <div class="card shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No tienes citas programadas</h4>
            <p class="text-muted">
                <?php if ($dateFilter || $statusFilter): ?>
                    No se encontraron citas con los filtros aplicados
                <?php else: ?>
                    Las citas aparecerán aquí cuando los clientes las reserven
                <?php endif; ?>
            </p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($groupedAppointments as $date => $dateAppointments): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-calendar3"></i> 
                    <?php echo formatDate($date); ?>
                    <?php if ($date === date('Y-m-d')): ?>
                    <span class="badge bg-primary ms-2">Hoy</span>
                    <?php endif; ?>
                </h5>
                <span class="badge bg-light text-dark">
                    <?php echo count($dateAppointments); ?> cita(s)
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                        <?php 
                        usort($dateAppointments, function($a, $b) {
                            return strcmp($a['hora_cita'], $b['hora_cita']);
                        });
                        ?>
                        <?php foreach ($dateAppointments as $appointment): ?>
                        <tr>
                            <td>
                                <strong class="text-primary">
                                    <?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?>
                                </strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo e($appointment['nombre_cliente']); ?></strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone"></i> 
                                        <?php echo e($appointment['telefono_cliente'] ?? 'N/A'); ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php echo e($appointment['nombre_servicio']); ?><br>
                                <small class="text-muted">
                                    <?php echo formatPrice($appointment['precio_servicio']); ?>
                                </small>
                            </td>
                            <td>
                                <i class="bi bi-clock"></i> 
                                <?php echo $appointment['duracion_servicio']; ?> min
                            </td>
                            <td>
                                <?php
                                $statusBadges = [
                                    APPOINTMENT_PENDING => 'bg-warning',
                                    APPOINTMENT_CONFIRMED => 'bg-success',
                                    APPOINTMENT_COMPLETED => 'bg-info',
                                    APPOINTMENT_CANCELLED => 'bg-danger'
                                ];
                                $badgeClass = $statusBadges[$appointment['estado']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($appointment['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewDetails(<?php echo htmlspecialchars(json_encode($appointment)); ?>)"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <?php if ($appointment['estado'] === APPOINTMENT_CONFIRMED): ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=update-appointment-status" 
                                          style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="estado" value="<?php echo APPOINTMENT_COMPLETED; ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm" 
                                                title="Marcar como completada"
                                                onclick="return confirm('¿Marcar esta cita como completada?')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Ver Detalles -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
                <!-- Se llenará con JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(appointment) {
    const detailsHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="bi bi-person-circle"></i> Información del Cliente
                </h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td>${appointment.nombre_cliente}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><a href="mailto:${appointment.email_cliente}">${appointment.email_cliente}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>
                            ${appointment.telefono_cliente ? 
                                `<a href="tel:${appointment.telefono_cliente}">${appointment.telefono_cliente}</a>` : 
                                'N/A'}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="bi bi-calendar-check"></i> Detalles de la Cita
                </h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>${formatDate(appointment.fecha_cita)}</td>
                    </tr>
                    <tr>
                        <td><strong>Hora:</strong></td>
                        <td>${formatTime(appointment.hora_cita)}</td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td><span class="badge bg-${getStatusColor(appointment.estado)}">${appointment.estado}</span></td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">
                    <i class="bi bi-scissors"></i> Información del Servicio
                </h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td style="width: 150px;"><strong>Servicio:</strong></td>
                        <td>${appointment.nombre_servicio}</td>
                    </tr>
                    <tr>
                        <td><strong>Descripción:</strong></td>
                        <td>${appointment.descripcion_servicio || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Duración:</strong></td>
                        <td>${appointment.duracion_servicio} minutos</td>
                    </tr>
                    <tr>
                        <td><strong>Precio:</strong></td>
                        <td><strong class="text-success">${formatPrice(appointment.precio_servicio)}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        ${appointment.notas ? `
        <div class="alert-off alert-info">
            <strong><i class="bi bi-sticky"></i> Notas del cliente:</strong><br>
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

function formatDate(dateString) {
    return new Date(dateString + 'T00:00:00').toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
}

function getStatusColor(status) {
    const colors = {
        'pendiente': 'warning',
        'confirmada': 'success',
        'completada': 'info',
        'cancelada': 'danger'
    };
    return colors[status] || 'secondary';
}
</script>