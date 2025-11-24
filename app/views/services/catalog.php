<?php
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Service.php';

$database = new Database();
$db = $database->getConnection();
$serviceModel = new Service($db);

$category = $_GET['categoria'] ?? null;
$search = $_GET['search'] ?? null;

if ($search) {
    $services = $serviceModel->search($search);
} elseif ($category) {
    $services = $serviceModel->getByCategory($category);
} else {
    $services = $serviceModel->getAll();
}

$categories = $serviceModel->getCategories();
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-grid-3x3-gap"></i> Nuestros Servicios
            </h1>
            <p class="text-muted">Descubre todos los servicios que tenemos para ti</p>
        </div>
    </div>
    
    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="hidden" name="page" value="services">
                <input type="text" name="search" class="form-control" 
                       placeholder="Buscar servicios..." 
                       value="<?php echo e($search ?? ''); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
        <div class="col-md-4">
            <select class="form-select" onchange="filterByCategory(this.value)">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo e($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                    <?php echo e($cat); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <?php if (empty($services)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No se encontraron servicios</h5>
                    <p class="text-muted">Intenta con otros términos de búsqueda</p>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=services" class="btn btn-primary">
                        Ver Todos los Servicios
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($services as $service): ?>
        <div class="col-md-4 fade-in">
            <div class="card service-card h-100 shadow-sm">
                <?php if ($service['imagen']): ?>
                <img src="<?php echo ASSETS_URL . 'uploads/' . e($service['imagen']); ?>" 
                     class="card-img-top" alt="<?php echo e($service['nombre']); ?>">
                <?php else: ?>
                <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center" 
                     style="height: 250px;">
                    <i class="bi bi-flower1" style="font-size: 5rem; opacity: 0.3;"></i>
                </div>
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-primary mb-2 align-self-start">
                        <?php echo e($service['categoria']); ?>
                    </span>
                    
                    <h5 class="card-title fw-bold"><?php echo e($service['nombre']); ?></h5>
                    
                    <p class="card-text text-muted flex-grow-1">
                        <?php echo e(truncate($service['descripcion'], 120)); ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary fw-bold fs-5">
                            <?php echo formatPrice($service['precio']); ?>
                        </span>
                        <span class="text-muted">
                            <i class="bi bi-clock"></i> <?php echo $service['duracion']; ?> min
                        </span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/index.php?page=service-detail&id=<?php echo $service['id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-info-circle"></i> Ver Detalles
                        </a>
                        
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin() || isSpecialist()): ?>
                                <button type="button" class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-exclamation-triangle"></i> No puede agendar cita
                                </button>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment&service=<?php echo $service['id']; ?>"
                                class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-calendar-plus"></i> Reservar
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Inicia Sesión para Reservar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function filterByCategory(category) {
    if (category) {
        window.location.href = '<?php echo BASE_URL; ?>/index.php?page=services&categoria=' + encodeURIComponent(category);
    } else {
        window.location.href = '<?php echo BASE_URL; ?>/index.php?page=services';
    }
}
</script>
