<?php
requireRole(ROLE_CLIENT);

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$appointmentModel = new Appointment($db);

$appointments = $appointmentModel->getByUser(getUserId(), APPOINTMENT_COMPLETED);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-clock-history"></i> Mi Historial de Servicios
            </h1>
            <p class="text-muted">Revisa todos los servicios que has recibido</p>
        </div>
    </div>
    
    <?php if (empty($appointments)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox text-muted" style="font-size: 5rem;"></i>
                    <h3 class="mt-3">No tienes historial aún</h3>
                    <p class="text-muted">Una vez completes tus primeros servicios, aparecerán aquí</p>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=services" class="btn btn-primary">
                        <i class="bi bi-grid-3x3-gap"></i> Explorar Servicios
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Especialista</th>
                                    <th>Duración</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo formatDate($appointment['fecha_cita']); ?></td>
                                    <td>
                                        <strong><?php echo e($appointment['nombre_servicio']); ?></strong><br>
                                        <small class="text-muted"><?php echo e($appointment['especialidad']); ?></small>
                                    </td>
                                    <td><?php echo e($appointment['nombre_especialista']); ?></td>
                                    <td><?php echo $appointment['duracion_servicio']; ?> min</td>
                                    <td><?php echo formatPrice($appointment['precio_servicio']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/index.php?page=service-detail&id=<?php echo $appointment['servicio_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver servicio">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" 
                                           class="btn btn-sm btn-outline-success" title="Reservar de nuevo">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <p class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Total de servicios completados: <strong><?php echo count($appointments); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>