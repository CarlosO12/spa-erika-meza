<?php
requireRole(ROLE_CLIENT);

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Specialist.php';
require_once APP_PATH . '/models/Review.php';

$database = new Database();
$db = $database->getConnection();
$specialistModel = new Specialist($db);
$reviewModel = new Review($db);

$specialists = $specialistModel->getAll();
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-people"></i> Nuestros Especialistas
            </h1>
            <p class="text-muted">Conoce a nuestro equipo y lee las opiniones de otros clientes</p>
        </div>
    </div>
    
    <div class="row g-4">
        <?php foreach ($specialists as $specialist): ?>
        <?php 
        $stats = $reviewModel->getSpecialistStats($specialist['id']);
        $avgRating = $stats['avg_rating'] ?? 0;
        $totalReviews = $stats['total_reviews'] ?? 0;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-2"><?= e($specialist['nombre']) ?></h5>
                    <p class="text-muted mb-3">
                        <i class="bi bi-award"></i> <?= e($specialist['especialista']) ?>
                    </p>
                    
                    <?php if (!empty($specialist['descripcion'])): ?>
                        <p class="text-muted small mb-3">
                            <?= e(truncate($specialist['descripcion'], 100)) ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Rating -->
                    <?php if ($totalReviews > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <span class="fs-5 fw-bold text-warning me-2">
                                <?= number_format($avgRating, 1) ?>
                            </span>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= round($avgRating) ? '-fill' : '' ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <small class="text-muted"><?= $totalReviews ?> opiniones</small>
                    </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-star"></i> Sin reseñas aún
                        </small>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <?php if ($totalReviews > 0): ?>
                        <a href="<?= BASE_URL ?>/index.php?page=specialist-reviews&id=<?= $specialist['id'] ?>" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-chat-left-text"></i> Leer Opiniones
                        </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/index.php?page=book-appointment&specialist=<?= $specialist['id'] ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>