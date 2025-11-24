<?php
requireAdmin();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$appointmentModel = new Appointment($db);

// Filtros
$statusFilter = $_GET['estado'] ?? null;
$dateFilter = $_GET['fecha_cita'] ?? null;
$searchFilter = $_GET['search'] ?? null;

$appointments = $appointmentModel->getAll($statusFilter, $dateFilter, $searchFilter);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-calendar-check"></i> Gestión de Citas
            </h1>
            <p class="text-muted">Administra todas las citas del sistema</p>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">
                                <?php echo ($statusFilter || $dateFilter || $searchFilter) ? 'Resultados' : 'Total Citas'; ?>
                            </p>
                            <h3 class="fw-bold mb-0">
                                <?php echo count($appointments); ?>
                            </h3>
                            <?php if ($statusFilter || $dateFilter || $searchFilter): ?>
                                <small class="text-muted">Filtrado</small>
                            <?php endif; ?>
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
                            <p class="text-muted mb-1">Pendientes</p>
                            <h3 class="fw-bold mb-0 text-warning">
                                <?php echo $appointmentModel->countByStatus(APPOINTMENT_PENDING); ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-warning">
                            <i class="bi bi-clock-history"></i>
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
                                <?php echo $appointmentModel->countByStatus(APPOINTMENT_CONFIRMED); ?>
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
                            <p class="text-muted mb-1">Completadas</p>
                            <h3 class="fw-bold mb-0 text-info">
                                <?php echo $appointmentModel->countByStatus(APPOINTMENT_COMPLETED); ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="bi bi-check-all"></i>
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
                <input type="hidden" name="page" value="admin-appointments">
                
                <div class="col-md-3">
                    <label for="fecha_cita" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha_cita" name="fecha_cita" 
                           value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
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
                
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Cliente o servicio..."
                           value="<?php echo htmlspecialchars($searchFilter ?? ''); ?>">
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=admin-appointments" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                    <button type="button" class="btn btn-outline-success" 
                            onclick="exportToExcel()"
                            title="Exportar en Excel">
                        <i class="bi bi-file-excel"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Citas -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($appointments)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No se encontraron citas</h4>
                    <p class="text-muted">
                        <?php if ($statusFilter || $dateFilter || $searchFilter): ?>
                            Intenta ajustar los filtros de búsqueda.
                        <?php else: ?>
                            Aún no hay citas registradas en el sistema.
                        <?php endif; ?>
                    </p>
                    <?php if ($statusFilter || $dateFilter || $searchFilter): ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-appointments" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise"></i> Ver todas las citas
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Especialista</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><strong>#<?php echo $appointment['id']; ?></strong></td>
                            <td>
                                <div>
                                    <strong><?php echo e($appointment['nombre_cliente']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($appointment['email_cliente']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <?php echo e($appointment['nombre_servicio']); ?><br>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo $appointment['duracion_servicio']; ?> min
                                    </small>
                                </div>
                            </td>
                            <td><?php echo e($appointment['nombre_especialista']); ?></td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?php echo formatDate($appointment['fecha_cita']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?></strong>
                            </td>
                            <td>
                                <strong class="text-primary">
                                    <?php echo formatPrice($appointment['precio_servicio']); ?>
                                </strong>
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
                                            onclick="viewAppointment(<?php echo htmlspecialchars(json_encode($appointment)); ?>)"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <?php if ($appointment['estado'] === APPOINTMENT_PENDING): ?>
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="updateStatus(<?php echo $appointment['id']; ?>, '<?php echo APPOINTMENT_CONFIRMED; ?>')"
                                            title="Confirmar">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($appointment['estado'] === APPOINTMENT_CONFIRMED): ?>
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="updateStatus(<?php echo $appointment['id']; ?>, '<?php echo APPOINTMENT_COMPLETED; ?>')"
                                            title="Completar">
                                        <i class="bi bi-check-all"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($appointment['estado'], [APPOINTMENT_PENDING, APPOINTMENT_CONFIRMED])): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)"
                                            title="Cancelar">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
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

<!-- Modal Ver Detalles -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1">
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

<!-- Modal Cancelar Cita -->
<div class="modal fade" id="cancelAppointmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Cita</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/index.php?action=update-appointment-status" method="POST">
                <input type="hidden" id="cancel_appointment_id" name="id">
                <input type="hidden" name="estado" value="<?php echo APPOINTMENT_CANCELLED; ?>">
                <div class="modal-body">
                    <div class="alert-off alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta acción cancelará la cita y notificará al cliente.
                    </div>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Razón de cancelación (opcional)</label>
                        <textarea class="form-control" id="razon_cancelacion" name="razon_cancelacion" 
                                  rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewAppointment(appointment) {
    const detailsHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">Información del Cliente</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td>${appointment.nombre_cliente}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${appointment.email_cliente}</td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>${appointment.telefono_cliente || 'N/A'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">Información del Servicio</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Servicio:</strong></td>
                        <td>${appointment.nombre_servicio}</td>
                    </tr>
                    <tr>
                        <td><strong>Especialista:</strong></td>
                        <td>${appointment.nombre_especialista}</td>
                    </tr>
                    <tr>
                        <td><strong>Duración:</strong></td>
                        <td>${appointment.duracion_servicio} minutos</td>
                    </tr>
                    <tr>
                        <td><strong>Precio:</strong></td>
                        <td><strong class="text-primary">${formatPrice(appointment.precio_servicio)}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">Detalles de la Cita</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td style="width: 150px;"><strong>Fecha:</strong></td>
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
                    <tr>
                        <td><strong>Creada:</strong></td>
                        <td>${formatDateTime(appointment.creada)}</td>
                    </tr>
                </table>
            </div>
        </div>
        ${appointment.notas ? `
        <div class="alert-off alert-info mt-3">
            <strong><i class="bi bi-sticky"></i> Notas del cliente:</strong><br>
            ${appointment.notas}
        </div>
        ` : ''}
        ${appointment.razon_cancelacion ? `
        <div class="alert-off alert-danger mt-3">
            <strong><i class="bi bi-x-circle"></i> Razón de cancelación:</strong><br>
            ${appointment.razon_cancelacion}
        </div>
        ` : ''}
    `;
    
    document.getElementById('appointmentDetails').innerHTML = detailsHTML;
    const modal = new bootstrap.Modal(document.getElementById('viewAppointmentModal'));
    modal.show();
}

function updateStatus(appointmentId, newStatus) {
    const statusNames = {
        'pendiente': 'Pendiente',
        'confirmada': 'Confirmada',
        'completada': 'Completada',
        'cancelada': 'Cancelada'
    };
    
    if (confirm(`¿Cambiar el estado de la cita a "${statusNames[newStatus]}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/index.php?action=update-appointment-status';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = appointmentId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'estado';
        statusInput.value = newStatus;
        
        form.appendChild(idInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelAppointment(appointmentId) {
    document.getElementById('cancel_appointment_id').value = appointmentId;
    const modal = new bootstrap.Modal(document.getElementById('cancelAppointmentModal'));
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
    return new Date(dateString).toLocaleDateString('es-CO', {
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

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '—';

    const fixedString = dateTimeString.replace(' ', 'T');
    const date = new Date(fixedString);

    if (isNaN(date)) return '—';

    return date.toLocaleString('es-CO', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
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

function exportToExcel() {
    window.location.href = '<?php echo BASE_URL; ?>/index.php?action=export-csv&type=appointments';
}
</script>