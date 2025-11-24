<?php
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Service.php';

$database = new Database();
$db = $database->getConnection();
$serviceModel = new Service($db);

$serviceId = (int)($_GET['id'] ?? 0);
$service = $serviceModel->findById($serviceId);

if (!$service) {
    header('Location: ' . BASE_URL . '/index.php?page=services');
    exit();
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?page=services">Servicios</a></li>
                    <li class="breadcrumb-item active"><?php echo e($service['nombre']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mb-4">
            <?php if ($service['imagen']): ?>
            <img src="<?php echo ASSETS_URL . 'uploads/' . e($service['imagen']); ?>" 
                 alt="<?php echo e($service['nombre']); ?>"
                 class="img-fluid rounded shadow-sm">
            <?php else: ?>
            <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center rounded shadow-sm" 
                 style="height: 400px;">
                <i class="bi bi-flower1" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-6">
            <span class="badge bg-primary mb-3"><?php echo e($service['categoria']); ?></span>
            <h1 class="fw-bold mb-3"><?php echo e($service['nombre']); ?></h1>
            
            <div class="d-flex align-items-center mb-4">
                <div class="me-4">
                    <h3 class="text-primary fw-bold mb-0">
                        <?php echo formatPrice($service['precio']); ?>
                    </h3>
                    <small class="text-muted">Precio</small>
                </div>
                <div>
                    <h4 class="mb-0">
                        <i class="bi bi-clock"></i> <?php echo $service['duracion']; ?> min
                    </h4>
                    <small class="text-muted">Duración</small>
                </div>
            </div>
            
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Descripción</h5>
                <p class="text-muted"><?php echo nl2br(e($service['descripcion'])); ?></p>
            </div>
            
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Beneficios</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Atención personalizada</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Productos de alta calidad</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Profesionales certificados</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Ambiente relajante</li>
                </ul>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin() || isSpecialist()): ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Los administradores o especilistas no pueden realizar reservas ni agregar al carrito.
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg w-100" disabled>
                            <i class="bi bi-calendar-x"></i> Reservar Directamente
                        </button>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2">
                        <!--
                        <form action="<?php echo BASE_URL; ?>/index.php?action=add-to-cart" method="POST">
                            <input type="hidden" name="servicio_id" value="<?php echo $service['id']; ?>">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-cart-plus"></i> Agregar al Carrito
                            </button>
                        </form>
                        -->
                        <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment&service=<?php echo $service['id']; ?>"
                        class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-calendar-plus"></i> Reservar Directamente
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert-info mb-3 p-3 border rounded">
                    <i class="bi bi-info-circle"></i>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="alert-link">Inicia sesión</a> 
                    para reservar este servicio
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Servicios Relacionados -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4">Servicios Relacionados</h3>
        </div>
        
        <?php
        $relatedServices = $serviceModel->getByCategory($service['categoria']);
        $relatedServices = array_filter($relatedServices, function($s) use ($serviceId) {
            return $s['id'] != $serviceId;
        });
        $relatedServices = array_slice($relatedServices, 0, 3);
        ?>
        
        <?php foreach ($relatedServices as $related): ?>
        <div class="col-md-4 mb-4">
            <div class="card service-card h-100 shadow-sm">
                <?php if ($related['imagen']): ?>
                <img src="<?php echo ASSETS_URL . 'uploads/' . e($related['imagen']); ?>" 
                     class="card-img-top" alt="<?php echo e($related['nombre']); ?>">
                <?php else: ?>
                <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center" 
                     style="height: 200px;">
                    <i class="bi bi-flower1" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?php echo e($related['nombre']); ?></h5>
                    <p class="card-text text-muted">
                        <?php echo e(truncate($related['descripcion'], 80)); ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-primary fw-bold"><?php echo formatPrice($related['precio']); ?></span>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=service-detail&id=<?php echo $related['id']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>