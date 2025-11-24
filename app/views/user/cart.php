<?php
requireRole(ROLE_CLIENT);

$cart = $_SESSION['carrito'] ?? [];
$total = 0;
$totalDuration = 0;

foreach ($cart as $item) {
    $total += $item['precio'];
    $totalDuration += $item['duracion'];
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-cart"></i> Mi Carrito
            </h1>
        </div>
    </div>
    
    <?php if (empty($cart)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i>
                    <h3 class="mt-3">Tu carrito está vacío</h3>
                    <p class="text-muted">Agrega servicios para comenzar a reservar</p>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=services" class="btn btn-primary btn-lg">
                        <i class="bi bi-grid-3x3-gap"></i> Explorar Servicios
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php foreach ($cart as $serviceId => $item): ?>
                    <div class="cart-item d-flex justify-content-between align-items-center p-3 mb-3 border rounded"
                         data-service-id="<?php echo $serviceId; ?>"
                         data-price="<?php echo $item['precio']; ?>"
                         data-duration="<?php echo $item['duracion']; ?>">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?php echo e($item['nombre']); ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-clock"></i> <?php echo $item['duracion']; ?> minutos
                            </p>
                            <span class="text-primary fw-bold">
                                <?php echo formatPrice($item['precio']); ?>
                            </span>
                        </div>
                        <form action="<?php echo BASE_URL; ?>/index.php?action=remove-from-cart" method="POST">
                            <input type="hidden" name="service_id" value="<?php echo $serviceId; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger remove-from-cart">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="text-end mt-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=clear-cart" 
                           class="btn btn-sm btn-outline-secondary"
                           onclick="return confirm('¿Estás seguro de vaciar el carrito?')">
                            <i class="bi bi-trash"></i> Vaciar Carrito
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">Resumen del Carrito</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total de Servicios:</span>
                        <strong><?php echo count($cart); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Duración Total:</span>
                        <strong id="totalDuration"><?php echo $totalDuration; ?> min</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5">Total:</span>
                        <strong class="fs-4 text-primary" id="cartTotal">
                            <?php echo formatPrice($total); ?>
                        </strong>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" 
                           class="btn btn-primary btn-lg">
                            <i class="bi bi-calendar-check"></i> Proceder a Reservar
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=services" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Agregar Más Servicios
                        </a>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Los precios no incluyen IVA. El pago se realiza en el establecimiento.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="<?php echo ASSETS_URL; ?>js/cart.js"></script>