<?php
requireRole(ROLE_CLIENT);

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Appointment.php';
require_once APP_PATH . '/models/Review.php';

$database = new Database();
$db = $database->getConnection();
$appointmentModel = new Appointment($db);
$reviewModel = new Review($db);

$appointments = $appointmentModel->getByUser(getUserId());

// Agrupar citas por estado
$upcoming = [];
$completed = [];
$cancelled = [];

foreach ($appointments as $appointment) {
    switch ($appointment['estado']) {
        case APPOINTMENT_PENDING:
        case APPOINTMENT_CONFIRMED:
            $upcoming[] = $appointment;
            break;
        case APPOINTMENT_COMPLETED:
            $completed[] = $appointment;
            break;
        case APPOINTMENT_CANCELLED:
            $cancelled[] = $appointment;
            break;
    }
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fw-bold text-gradient">
                    <i class="bi bi-calendar-check"></i> Mis Citas
                </h1>
                <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva Cita
                </a>
            </div>
        </div>
    </div>
    
    <?php displayFlashMessage(); ?>
    
    <!-- Tabs de Navegación -->
    <ul class="nav nav-tabs mb-4" id="appointmentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" 
                    data-bs-target="#upcoming" type="button">
                Próximas <span class="badge bg-primary ms-1"><?php echo count($upcoming); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" 
                    data-bs-target="#completed" type="button">
                Completadas <span class="badge bg-success ms-1"><?php echo count($completed); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" 
                    data-bs-target="#cancelled" type="button">
                Canceladas <span class="badge bg-danger ms-1"><?php echo count($cancelled); ?></span>
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="appointmentTabsContent">
        <!-- Próximas Citas -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <?php if (empty($upcoming)): ?>
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No tienes citas próximas</h5>
                    <p class="text-muted">Agenda una cita para comenzar</p>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" class="btn btn-primary">
                        Agendar Cita
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($upcoming as $appointment): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold mb-0"><?php echo e($appointment['nombre_servicio']); ?></h5>
                                <?php if ($appointment['estado'] === APPOINTMENT_PENDING): ?>
                                <span class="badge bg-warning">Pendiente</span>
                                <?php else: ?>
                                <span class="badge bg-success">Confirmada</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-person text-primary"></i>
                                <strong>Especialista:</strong> <?php echo e($appointment['nombre_especialista']); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <strong>Fecha:</strong> <?php echo formatDate($appointment['fecha_cita']); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-clock text-primary"></i>
                                <strong>Hora:</strong> <?php echo date('h:i A', strtotime($appointment['hora_cita'])); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-hourglass text-primary"></i>
                                <strong>Duración:</strong> <?php echo $appointment['duracion_servicio']; ?> minutos
                            </div>

                            <div class="mb-3">
                                <i class="bi bi-cash text-primary"></i>
                                <strong>Precio:</strong> <?php echo formatPrice($appointment['precio_servicio']); ?>
                            </div>
                            
                            <?php if (!empty($appointment['notas'])): ?>
                            <div class="alert-off alert-info mb-3">
                                <small><strong><i class="bi bi-sticky"></i> Notas:</strong> <?php echo e($appointment['notas']); ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                    <i class="bi bi-x-circle"></i> Cancelar Cita
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Citas Completadas -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <?php if (empty($completed)): ?>
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-check-circle text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No tienes citas completadas</h5>
                    <p class="text-muted">Las citas finalizadas aparecerán aquí</p>
                </div>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($completed as $appointment): ?>
                <?php 
                // Verificar si ya tiene reseña
                $hasReview = $reviewModel->existsForAppointment($appointment['id']);
                $review = $hasReview ? $reviewModel->getByAppointment($appointment['id']) : null;
                ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold mb-0"><?php echo e($appointment['nombre_servicio']); ?></h5>
                                <span class="badge bg-info">Completada</span>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-person text-primary"></i>
                                <strong>Especialista:</strong> <?php echo e($appointment['nombre_especialista']); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <strong>Fecha:</strong> <?php echo formatDate($appointment['fecha_cita']); ?>
                            </div>
                            
                            <div class="mb-3">
                                <i class="bi bi-cash text-primary"></i>
                                <strong>Precio:</strong> <?php echo formatPrice($appointment['precio_servicio']); ?>
                            </div>
                            
                            <hr>
                            
                            <!-- Sistema de Reseñas -->
                            <?php if ($hasReview): ?>
                                <div class="alert-off alert-success">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong><i class="bi bi-check-circle-fill"></i> Tu calificación</strong>
                                        <div>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $review['evaluacion'] ? '-fill' : '' ?> text-warning"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div> 
                                    <?php if (!empty($review['opinion'])): ?>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-quote"></i> "<?= e($review['opinion']) ?>"
                                        </p>
                                    <?php endif; ?>
                                    <small class="text-muted d-block mt-2">
                                        Calificado el <?= date('d/m/Y', strtotime($review['creado'])) ?>
                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="alert-off alert-warning">
                                    <i class="bi bi-star"></i> ¿Cómo fue tu experiencia?
                                </div>
                                <div class="d-grid">
                                    <button type="button" 
                                            class="btn btn-warning"
                                            onclick='openReviewModal(<?= json_encode($appointment) ?>)'>
                                        <i class="bi bi-star-fill"></i> Calificar Servicio
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Citas Canceladas -->
        <div class="tab-pane fade" id="cancelled" role="tabpanel">
            <?php if (empty($cancelled)): ?>
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-slash-circle text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No tienes citas canceladas</h5>
                </div>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($cancelled as $appointment): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 border-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold mb-0"><?php echo e($appointment['nombre_servicio']); ?></h5>
                                <span class="badge bg-danger">Cancelada</span>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-person text-primary"></i>
                                <strong>Especialista:</strong> <?php echo e($appointment['nombre_especialista']); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <strong>Fecha:</strong> <?php echo formatDate($appointment['fecha_cita']); ?>
                            </div>
                            
                            <?php if (!empty($appointment['razon_cancelacion'])): ?>
                            <div class="alert-off alert-danger mt-3">
                                <small>
                                    <strong><i class="bi bi-exclamation-triangle"></i> Razón:</strong><br>
                                    <?php echo e($appointment['razon_cancelacion']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Cancelar Cita -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle"></i> Cancelar Cita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/index.php?action=cancel-appointment" method="POST">
                <input type="hidden" id="cancel_appointment_id" name="id">
                <div class="modal-body">
                    <div class="alert-off alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Importante:</strong> Las cancelaciones deben hacerse con al menos 24 horas de anticipación.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Razón de cancelación (opcional)</label>
                        <textarea class="form-control" 
                                  id="reason" 
                                  name="razon_cancelacion" 
                                  rows="3"
                                  placeholder="¿Por qué necesitas cancelar?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Volver
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle"></i> Confirmar Cancelación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Calificar Servicio -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-star-fill"></i> Calificar Servicio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/index.php?action=create-review" method="POST">
                <?= csrfField() ?>
                <input type="hidden" id="review_cita_id" name="cita_id">
                
                <div class="modal-body">
                    <div id="reviewAppointmentInfo" class="mb-4">
                        <!-- Se llenará con JavaScript -->
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tu Calificación *</label>
                        <div class="star-rating" id="starRating">
                            <input type="radio" id="star5" name="evaluacion" value="5" required>
                            <label for="star5" title="Excelente - 5 estrellas">
                                <i class="bi bi-star-fill"></i>
                            </label>
                            
                            <input type="radio" id="star4" name="evaluacion" value="4">
                            <label for="star4" title="Muy bueno - 4 estrellas">
                                <i class="bi bi-star-fill"></i>
                            </label>
                            
                            <input type="radio" id="star3" name="evaluacion" value="3">
                            <label for="star3" title="Bueno - 3 estrellas">
                                <i class="bi bi-star-fill"></i>
                            </label>
                            
                            <input type="radio" id="star2" name="evaluacion" value="2">
                            <label for="star2" title="Regular - 2 estrellas">
                                <i class="bi bi-star-fill"></i>
                            </label>
                            
                            <input type="radio" id="star1" name="evaluacion" value="1">
                            <label for="star1" title="Malo - 1 estrella">
                                <i class="bi bi-star-fill"></i>
                            </label>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Haz clic en las estrellas para calificar</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review_opinion" class="form-label fw-bold">
                            <i class="bi bi-chat-left-text"></i> Cuéntanos tu experiencia (opcional)
                        </label>
                        <textarea class="form-control" 
                                  id="review_opinion" 
                                  name="opinion" 
                                  rows="4" 
                                  placeholder="¿Qué te pareció el servicio? ¿Algo que destacar?"></textarea>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Tu opinión ayuda a otros usuarios y al especialista a mejorar
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send-fill"></i> Enviar Calificación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cancelAppointment(appointmentId) {
    document.getElementById('cancel_appointment_id').value = appointmentId;
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

function openReviewModal(appointment) {
    // Llenar información de la cita
    const infoHTML = `
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3 text-primary">
                    <i class="bi bi-info-circle"></i> Detalles del Servicio
                </h6>
                <div class="mb-2">
                    <strong>Servicio:</strong> ${appointment.nombre_servicio}
                </div>
                <div class="mb-2">
                    <strong>Especialista:</strong> ${appointment.nombre_especialista}
                </div>
                <div class="mb-0">
                    <strong>Fecha:</strong> ${formatDate(appointment.fecha_cita)}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('reviewAppointmentInfo').innerHTML = infoHTML;
    document.getElementById('review_cita_id').value = appointment.id;
    document.getElementById('review_opinion').value = '';
    
    // Limpiar selección de estrellas
    document.querySelectorAll('.star-rating input[type="radio"]').forEach(input => {
        input.checked = false;
    });
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
}

function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('es-CO', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>