<?php
requireAdmin();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Review.php';

$database = new Database();
$db = $database->getConnection();
$reviewModel = new Review($db);

$reviews = $reviewModel->getAll();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-star"></i> Gestión de Reseñas
            </h1>
            <p class="text-muted">Administra las calificaciones y opiniones de los clientes</p>
        </div>
    </div>
    
    <?php displayFlashMessage(); ?>
    
    <!-- Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Reseñas</p>
                            <h3 class="fw-bold mb-0"><?= count($reviews) ?></h3>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-chat-left-text"></i>
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
                            <p class="text-muted mb-1">Promedio General</p>
                            <h3 class="fw-bold mb-0 text-warning">
                                <?php 
                                $avgTotal = !empty($reviews) ? array_sum(array_column($reviews, 'evaluacion')) / count($reviews) : 0;
                                echo number_format($avgTotal, 1);
                                ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-warning">
                            <i class="bi bi-star-fill"></i>
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
                            <p class="text-muted mb-1">5 Estrellas</p>
                            <h3 class="fw-bold mb-0 text-success">
                                <?php 
                                $fiveStars = array_filter($reviews, fn($r) => $r['evaluacion'] == 5);
                                echo count($fiveStars);
                                ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-emoji-smile"></i>
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
                            <p class="text-muted mb-1">Con Opinión</p>
                            <h3 class="fw-bold mb-0 text-info">
                                <?php 
                                $withOpinion = array_filter($reviews, fn($r) => !empty($r['opinion']));
                                echo count($withOpinion);
                                ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="bi bi-chat-quote"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Reseñas -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($reviews)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-star display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No hay reseñas todavía</h4>
                    <p class="text-muted">Las reseñas aparecerán aquí cuando los clientes califiquen los servicios</p>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Especialista</th>
                            <th>Servicio</th>
                            <th>Calificación</th>
                            <th>Opinión</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><strong>#<?= $review['id'] ?></strong></td>
                            <td><?= e($review['nombre_cliente']) ?></td>
                            <td><?= e($review['nombre_especialista']) ?></td>
                            <td><?= e($review['nombre_servicio']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2">
                                        <?= $review['evaluacion'] ?>
                                    </span>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= $review['evaluacion'] ? '-fill' : '' ?> text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($review['opinion'])): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info"
                                            onclick="viewOpinion('<?= e($review['opinion']) ?>')">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">Sin opinión</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= formatDate($review['creado']) ?>
                                </small>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteReview(<?= $review['id'] ?>)">
                                    <i class="bi bi-trash"></i>
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

<!-- Modal Ver Opinión -->
<div class="modal fade" id="opinionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Opinión del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="opinionContent" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewOpinion(opinion) {
    document.getElementById('opinionContent').textContent = opinion;
    const modal = new bootstrap.Modal(document.getElementById('opinionModal'));
    modal.show();
}

function deleteReview(reviewId) {
    if (confirm('¿Estás seguro de eliminar esta reseña?\n\nEsta acción no se puede deshacer y afectará la calificación del especialista.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/index.php?action=delete-review';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'review_id';
        input.value = reviewId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>