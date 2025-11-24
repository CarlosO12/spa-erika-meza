<?php
requireRole(ROLE_CLIENT);
/**
 * Vista: specialist-reviews.php
 * Mostrar reseñas de un especialista
 */

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Review.php';
require_once APP_PATH . '/models/Specialist.php';

$database = new Database();
$db = $database->getConnection();
$reviewModel = new Review($db);
$specialistModel = new Specialist($db);

$especialistaId = (int)$_GET['id'];
$especialista = $specialistModel->findById($especialistaId);

if (!$especialista) {
    header('Location: ' . BASE_URL . '/index.php?page=services');
    exit();
}

$reviews = $reviewModel->getBySpecialist($especialistaId);
$stats = $reviewModel->getSpecialistStats($especialistaId);
?>

<div class="container py-5">
    <!-- Encabezado del Especialista -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold mb-2"><?= e($especialista['nombre']) ?></h2>
                            <p class="text-muted mb-2">
                                <i class="bi bi-award"></i> <?= e($especialista['especialista']) ?>
                            </p>
                            <?php if (!empty($especialista['descripcion'])): ?>
                                <p class="text-muted"><?= e($especialista['descripcion']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 fw-bold text-warning">
                                <?= number_format($stats['avg_rating'] ?? 0, 1) ?>
                            </div>
                            <div class="mb-2">
                                <?php 
                                $avgRating = $stats['avg_rating'] ?? 0;
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <i class="bi bi-star<?= $i <= round($avgRating) ? '-fill' : '' ?> text-warning fs-4"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0">
                                <?= $stats['total_reviews'] ?? 0 ?> opiniones
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Distribución de Calificaciones -->
    <?php if ($stats['total_reviews'] > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Distribución de Calificaciones</h5>
                    
                    <?php 
                    $total = $stats['total_reviews'];
                    $ratings = [
                        5 => $stats['five_stars'] ?? 0,
                        4 => $stats['four_stars'] ?? 0,
                        3 => $stats['three_stars'] ?? 0,
                        2 => $stats['two_stars'] ?? 0,
                        1 => $stats['one_star'] ?? 0
                    ];
                    ?>
                    
                    <?php foreach ($ratings as $star => $count): ?>
                    <div class="row align-items-center mb-2">
                        <div class="col-auto" style="width: 80px;">
                            <span><?= $star ?> <i class="bi bi-star-fill text-warning"></i></span>
                        </div>
                        <div class="col">
                            <div class="progress" style="height: 20px;">
                                <?php 
                                $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                ?>
                                <div class="progress-bar bg-warning" 
                                     role="progressbar" 
                                     style="width: <?= $percentage ?>%">
                                    <?= round($percentage) ?>%
                                </div>
                            </div>
                        </div>
                        <div class="col-auto" style="width: 60px;">
                            <span class="text-muted"><?= $count ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Lista de Reseñas -->
    <div class="row">
        <div class="col-12">
            <h4 class="fw-bold mb-4">
                <i class="bi bi-chat-left-text"></i> Opiniones de Clientes
            </h4>
            
            <?php if (empty($reviews)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-chat-left-dots display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">Aún no hay reseñas</h5>
                        <p class="text-muted">Sé el primero en compartir tu experiencia</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1"><?= e($review['nombre_cliente']) ?></h6>
                                <small class="text-muted">
                                    <?= e($review['nombre_servicio']) ?> • 
                                    <?= date('d/m/Y', strtotime($review['creado'])) ?>
                                </small>
                            </div>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $review['evaluacion'] ? '-fill' : '' ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($review['opinion'])): ?>
                            <p class="mb-0"><?= nl2br(e($review['opinion'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Botón para agendar -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="<?= BASE_URL ?>/index.php?page=book-appointment&specialist=<?= $especialistaId ?>" 
            class="btn btn-primary btn-lg">
                <i class="bi bi-calendar-plus"></i> Agendar Cita con <?= e($especialista['nombre']) ?>
            </a>
        </div>
    </div>
</div>