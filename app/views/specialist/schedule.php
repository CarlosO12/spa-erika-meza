<?php
requireRole(ROLE_SPECIALIST);

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Specialist.php';
require_once APP_PATH . '/models/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$specialistModel = new Specialist($db);
$appointmentModel = new Appointment($db);

$specialist = $specialistModel->findByUserId(getUserId());

// Obtener semana actual o la seleccionada
$weekStart = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));
$weekStartDate = new DateTime($weekStart);
$weekDays = [];

for ($i = 0; $i < 7; $i++) {
    $day = clone $weekStartDate;
    $day->modify("+$i days");
    $weekDays[] = $day;
}

// Función helper para badges
function getStatusBadge($status) {
    $badges = [
        APPOINTMENT_PENDING => 'warning',
        APPOINTMENT_CONFIRMED => 'success',
        APPOINTMENT_COMPLETED => 'info',
        APPOINTMENT_CANCELLED => 'danger'
    ];
    return $badges[$status] ?? 'secondary';
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-calendar-week"></i> Mi Horario
            </h1>
            <p class="text-muted">Gestiona tu disponibilidad y horario de trabajo</p>
        </div>
    </div>
    
    <!-- Navegación de Semana -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <a href="?page=specialist-schedule&week=<?php echo date('Y-m-d', strtotime($weekStart . ' -7 days')); ?>" 
                   class="btn btn-outline-primary">
                    <i class="bi bi-chevron-left"></i> Semana Anterior
                </a>
                
                <h5 class="mb-0 text-center">
                    <?php 
                    $weekEnd = clone $weekStartDate;
                    $weekEnd->modify('+6 days');
                    echo $weekStartDate->format('d M') . ' - ' . $weekEnd->format('d M, Y');
                    ?>
                </h5>
                
                <div class="btn-group">
                    <a href="?page=specialist-schedule" class="btn btn-primary">
                        <i class="bi bi-calendar-today"></i> Hoy
                    </a>
                    <a href="?page=specialist-schedule&week=<?php echo date('Y-m-d', strtotime($weekStart . ' +7 days')); ?>" 
                       class="btn btn-outline-primary">
                        Semana Siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vista de Calendario Semanal -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 schedule-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Hora</th>
                            <?php foreach ($weekDays as $day): ?>
                            <th class="text-center <?php echo $day->format('Y-m-d') === date('Y-m-d') ? 'table-primary' : ''; ?>">
                                <div><?php echo ucfirst($day->format('D')); ?></div>
                                <div class="fw-bold"><?php echo $day->format('d'); ?></div>
                                <small class="text-muted"><?php echo ucfirst($day->format('M')); ?></small>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Horario de trabajo
                        $startHour = 8;
                        $endHour = 18;
                        
                        for ($hour = $startHour; $hour < $endHour; $hour++):
                            $timeSlot = sprintf("%02d:00:00", $hour);
                        ?>
                        <tr>
                            <td class="align-middle text-center bg-light">
                                <strong><?php echo date('h:i A', strtotime($timeSlot)); ?></strong>
                            </td>
                            <?php foreach ($weekDays as $day): ?>
                            <?php
                            $dateStr = $day->format('Y-m-d');
                            $dayAppointments = $appointmentModel->getBySpecialist($specialist['id'], $dateStr);
                            
                            // Buscar cita en este horario
                            $appointment = null;
                            foreach ($dayAppointments as $apt) {
                                $aptHour = (int)date('H', strtotime($apt['hora_cita']));
                                if ($aptHour === $hour && $apt['estado'] !== APPOINTMENT_CANCELLED) {
                                    $appointment = $apt;
                                    break;
                                }
                            }
                            
                            $isPast = $dateStr < date('Y-m-d') || 
                                     ($dateStr === date('Y-m-d') && $hour < (int)date('H'));
                            ?>
                            <td class="schedule-cell <?php echo $isPast ? 'past-time' : ''; ?> 
                                       <?php echo $appointment ? 'has-appointment' : 'available'; ?>"
                                data-date="<?php echo $dateStr; ?>"
                                data-time="<?php echo $timeSlot; ?>">
                                <?php if ($appointment): ?>
                                <div class="appointment-card 
                                            <?php echo 'status-' . $appointment['estado']; ?>"
                                     onclick="viewAppointmentDetails(<?php echo htmlspecialchars(json_encode($appointment), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <div class="fw-bold small text-truncate" title="<?php echo e($appointment['nombre_cliente']); ?>">
                                        <?php echo e($appointment['nombre_cliente']); ?>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 0.75rem;" 
                                         title="<?php echo e($appointment['nombre_servicio']); ?>">
                                        <?php echo e($appointment['nombre_servicio']); ?>
                                    </div>
                                    <div style="font-size: 0.7rem;">
                                        <i class="bi bi-clock"></i> <?php echo $appointment['duracion_servicio']; ?> min
                                    </div>
                                </div>
                                <?php elseif (!$isPast): ?>
                                <div class="text-center text-muted small">
                                    <i class="bi bi-plus-circle"></i> Disponible
                                </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Leyenda -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Leyenda</h6>
            <div class="row">
                <div class="col-md-3 col-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="legend-box status-pendiente me-2"></div>
                        <span>Pendiente</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="legend-box status-confirmada me-2"></div>
                        <span>Confirmada</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="legend-box status-completada me-2"></div>
                        <span>Completada</span>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="legend-box available me-2"></div>
                        <span>Disponible</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen del Día -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-calendar-day"></i> Resumen de Hoy
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $today = date('Y-m-d');
                    $todayAppointments = $appointmentModel->getBySpecialist($specialist['id'], $today);
                    // Filtrar canceladas
                    $todayAppointments = array_filter($todayAppointments, function($apt) {
                        return $apt['estado'] !== APPOINTMENT_CANCELLED;
                    });
                    // Ordenar por hora
                    usort($todayAppointments, function($a, $b) {
                        return strcmp($a['hora_cita'], $b['hora_cita']);
                    });
                    ?>
                    <?php if (empty($todayAppointments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No tienes citas para hoy</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($todayAppointments as $apt): ?>
                        <div class="list-group-item px-0 cursor-pointer" 
                             onclick="viewAppointmentDetails(<?php echo htmlspecialchars(json_encode($apt), ENT_QUOTES, 'UTF-8'); ?>)">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-primary">
                                        <i class="bi bi-clock"></i>
                                        <?php echo date('h:i A', strtotime($apt['hora_cita'])); ?>
                                    </h6>
                                    <p class="mb-1 fw-bold"><?php echo e($apt['nombre_cliente']); ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-scissors"></i>
                                        <?php echo e($apt['nombre_servicio']); ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo getStatusBadge($apt['estado']); ?>">
                                    <?php echo ucfirst($apt['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-clock text-primary"></i>
                            <strong>Horario:</strong> <?= config('business_hours_start') ?> - <?= config('business_hours_end') ?> con descansos entre citas
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-calendar-week text-primary"></i>
                            <strong>Días laborales:</strong> <?= config('business_schedule') ?> o dias acordados
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-hourglass text-primary"></i>
                            <strong>Duración por cita:</strong> Según servicio seleccionado
                        </li>
                        <li>
                            <i class="bi bi-bell text-primary"></i>
                            <strong>Notificaciones:</strong> Recibirás alertas de nuevas citas por email
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles de Cita -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <!-- Se llenará con JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewAppointmentDetails(appointment) {
    const statusBadges = {
        'pendiente': 'warning',
        'confirmada': 'success',
        'completada': 'info',
        'cancelada': 'danger'
    };
    
    const modalBody = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">
                    <i class="bi bi-person-circle"></i> Cliente
                </h6>
                <h5 class="fw-bold">${appointment.nombre_cliente}</h5>
                <p class="mb-0">
                    <i class="bi bi-envelope"></i> 
                    <a href="mailto:${appointment.email_cliente}">${appointment.email_cliente}</a><br>
                    <i class="bi bi-telephone"></i> 
                    ${appointment.telefono_cliente ? 
                        `<a href="tel:${appointment.telefono_cliente}">${appointment.telefono_cliente}</a>` : 
                        'N/A'}
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3">
                    <i class="bi bi-calendar-check"></i> Fecha y Hora
                </h6>
                <p class="mb-0">
                    <i class="bi bi-calendar3"></i> ${formatDate(appointment.fecha_cita)}<br>
                    <i class="bi bi-clock"></i> ${formatTime(appointment.hora_cita)}<br>
                    <span class="badge bg-${statusBadges[appointment.estado]} mt-2">
                        ${appointment.estado.toUpperCase()}
                    </span>
                </p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="text-muted mb-3">
                    <i class="bi bi-scissors"></i> Servicio
                </h6>
                <h5 class="fw-bold">${appointment.nombre_servicio}</h5>
                <p class="mb-0">
                    ${appointment.descripcion_servicio ? 
                        `<small class="text-muted">${appointment.descripcion_servicio}</small><br>` : 
                        ''}
                    <i class="bi bi-clock"></i> Duración: ${appointment.duracion_servicio} minutos<br>
                    <i class="bi bi-cash"></i> Precio: <strong class="text-success">${formatPrice(appointment.precio_servicio)}</strong>
                </p>
            </div>
        </div>
        ${appointment.notas ? `
        <hr>
        <div class="alert-off alert-info mb-0">
            <strong><i class="bi bi-sticky"></i> Notas del cliente:</strong><br>
            ${appointment.notas}
        </div>
        ` : ''}
    `;
    
    document.getElementById('appointmentModalBody').innerHTML = modalBody;
    const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
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
        weekday: 'long',
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
</script>