<?php
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Service.php';

$database = new Database();
$db = $database->getConnection();
$serviceModel = new Service($db);

$popularServices = $serviceModel->getMostPopular(3);
?>

<!-- Hero Section -->
<section class="hero-section py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-3 slide-up">
                    Bienvenido a <?php echo APP_NAME; ?>
                </h1>
                <p class="lead mb-4 slide-up" style="animation-delay: 0.2s;">
                    Tu espacio de belleza y bienestar en Medellín. Ofrecemos servicios de alta calidad 
                    para que te sientas y luzcas increíble.
                </p>
                <div class="d-flex gap-3 slide-up" style="animation-delay: 0.4s;">
                    <a href="<?php echo BASE_URL; ?>/index.php?page=services" class="btn btn-light btn-lg">
                        <i class="bi bi-grid-3x3-gap"></i> Ver Servicios
                    </a>
                    <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=register" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-person-plus"></i> Registrarse
                    </a>
                    <?php else: ?>
                        <?php if (isAdmin() || isSpecialist()): ?>
                            <a href="#" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center fade-in" style="animation-delay: 0.6s;">
                <img src="img/favicon.png" alt="icono" class="bi" width="284" height="284"> 
            </div>
        </div>
    </div>
</section>

<!-- Características -->
<section class="features-section py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4 fade-in">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="stats-icon mx-auto mb-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h5 class="fw-bold">Reservas en Línea</h5>
                        <p class="text-muted">
                            Agenda tus citas de manera fácil y rápida, 24/7 desde cualquier dispositivo.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="stats-icon mx-auto mb-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-bold">Especialistas Calificados</h5>
                        <p class="text-muted">
                            Profesionales experimentados comprometidos con tu belleza y bienestar.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 fade-in" style="animation-delay: 0.4s;">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="stats-icon mx-auto mb-3">
                            <i class="bi bi-star"></i>
                        </div>
                        <h5 class="fw-bold">Servicios de Calidad</h5>
                        <p class="text-muted">
                            Utilizamos productos de primera calidad para garantizar los mejores resultados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Servicios Populares -->
<?php if (!empty($popularServices)): ?>
<section class="popular-services py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-gradient">Nuestros Servicios Más Populares</h2>
            <p class="text-muted">Descubre los servicios más solicitados por nuestros clientes</p>
        </div>
        
        <div class="row">
            <?php foreach ($popularServices as $index => $service): ?>
            <div class="col-md-4 mb-4 fade-in" style="animation-delay: <?php echo $index * 0.2; ?>s;">
                <div class="card service-card h-100 border-0 shadow-sm">
                    <?php if ($service['imagen']): ?>
                    <img src="<?php echo ASSETS_URL . 'uploads/' . e($service['imagen']); ?>" 
                         class="card-img-top" alt="<?php echo e($service['nombre']); ?>">
                    <?php else: ?>
                    <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center" 
                         style="height: 250px;">
                        <i class="bi bi-flower1" style="font-size: 5rem; opacity: 0.3;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?php echo e($service['nombre']); ?></h5>
                        <p class="card-text text-muted">
                            <?php echo e(truncate($service['descripcion'], 100)); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-bold fs-5">
                                <?php echo formatPrice($service['precio']); ?>
                            </span>
                            <span class="text-muted">
                                <i class="bi bi-clock"></i> <?php echo $service['duracion']; ?> min
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="<?php echo BASE_URL; ?>/index.php?page=service-detail&id=<?php echo $service['id']; ?>" 
                           class="btn btn-primary w-100">
                            <i class="bi bi-info-circle"></i> Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/index.php?page=services" class="btn btn-outline-primary btn-lg">
                Ver Todos los Servicios <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<section class="cta-section py-5 bg-gradient-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">¿Listo para tu transformación?</h2>
        <p class="lead mb-4">Agenda tu cita hoy y experimenta el mejor servicio de belleza en Medellín</p>
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin() || isSpecialist()): ?>
                <a href="#" class="btn btn-light btn-lg">
                    <i class="bi bi-calendar-plus"></i> Agendar Cita Ahora
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" class="btn btn-light btn-lg">
                    <i class="bi bi-calendar-plus"></i> Agendar Cita Ahora
                </a>
            <?php endif; ?>
        <?php else: ?>
        <a href="<?php echo BASE_URL; ?>/index.php?page=register" class="btn btn-light btn-lg">
            <i class="bi bi-person-plus"></i> Crear Cuenta Gratis
        </a>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonios -->
<section class="testimonials-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-gradient">Lo Que Dicen Nuestros Clientes</h2>
            <p class="text-muted">Testimonios reales de personas satisfechas</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4 fade-in">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="fst-italic">
                            "Excelente servicio, las instalaciones son hermosas y el personal muy profesional. 
                            Quedé encantada con mi manicura."
                        </p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px;">
                                <strong>MC</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold">María Cardona</h6>
                                <small class="text-muted">Cliente Regular</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 fade-in" style="animation-delay: 0.2s;">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="fst-italic">
                            "Me encanta poder reservar mis citas online. Es muy práctico y el sistema es muy fácil de usar."
                        </p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px;">
                                <strong>LG</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold">Laura Gómez</h6>
                                <small class="text-muted">Cliente Regular</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 fade-in" style="animation-delay: 0.4s;">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="fst-italic">
                            "El mejor spa de Medellín. Ambiente relajante, productos de calidad y atención personalizada."
                        </p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px;">
                                <strong>AR</strong>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold">Ana Rodríguez</h6>
                                <small class="text-muted">Cliente Regular</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>