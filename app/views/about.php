<div class="container">
    <!-- Hero Section -->
    <div class="row mb-5 mt-4">
        <div class="col-12 text-center">
            <h1 class="fw-bold text-gradient mb-3">Acerca de Nosotros</h1>
            <p class="lead text-muted">Descubre quiénes somos y nuestra pasión por la belleza</p>
        </div>
    </div>
    
    <!-- Nuestra Historia -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="bg-gradient-primary text-white rounded shadow-sm p-5 text-center">
                <img src="img/favicon.png" alt="icono" class="bi" width="284" height="284"> 
            </div>
        </div>
        <div class="col-lg-6">
            <h2 class="fw-bold mb-3">Nuestra Historia</h2>
            <p class="text-muted">
                SPA Erika Meza nació de la pasión por la belleza y el bienestar. Con más de 10 años 
                de experiencia en el sector, nos hemos consolidado como uno de los centros de estética 
                más confiables de Medellín.
            </p>
            <p class="text-muted">
                Nuestro compromiso es brindar servicios de la más alta calidad, utilizando productos 
                profesionales y técnicas innovadoras para garantizar la satisfacción de cada uno de 
                nuestros clientes.
            </p>
        </div>
    </div>
    
    <!-- Misión y Visión -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-bullseye text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold text-center mb-3">Nuestra Misión</h4>
                    <p class="text-muted text-center">
                        Proporcionar servicios de belleza y bienestar de excelencia, superando las 
                        expectativas de nuestros clientes mediante la innovación, profesionalismo y 
                        atención personalizada en un ambiente acogedor.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-eye text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold text-center mb-3">Nuestra Visión</h4>
                    <p class="text-muted text-center">
                        Ser reconocidos como el SPA líder en Medellín, referentes en innovación y 
                        calidad de servicios, expandiendo nuestra presencia para llevar bienestar y 
                        belleza a más personas.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Valores -->
    <div class="row mb-5">
        <div class="col-12 mb-4">
            <h2 class="fw-bold text-center mb-4">Nuestros Valores</h2>
        </div>
        <div class="col-md-3 mb-3">
            <div class="text-center">
                <div class="stats-icon mx-auto mb-3">
                    <i class="bi bi-star"></i>
                </div>
                <h5 class="fw-bold">Excelencia</h5>
                <p class="text-muted">Compromiso con la calidad en cada servicio</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="text-center">
                <div class="stats-icon mx-auto mb-3 bg-success">
                    <i class="bi bi-heart"></i>
                </div>
                <h5 class="fw-bold">Pasión</h5>
                <p class="text-muted">Amor por lo que hacemos y nuestros clientes</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="text-center">
                <div class="stats-icon mx-auto mb-3 bg-info">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="fw-bold">Confianza</h5>
                <p class="text-muted">Seguridad y profesionalismo garantizados</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="text-center">
                <div class="stats-icon mx-auto mb-3 bg-warning">
                    <i class="bi bi-lightbulb"></i>
                </div>
                <h5 class="fw-bold">Innovación</h5>
                <p class="text-muted">Técnicas y productos de última generación</p>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body p-5 text-center">
                    <h3 class="fw-bold mb-3">¿Listo para tu transformación?</h3>
                    <p class="lead mb-4">Agenda tu cita hoy y descubre la diferencia</p>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin() || isSpecialist()): ?>
                            <a href="#" 
                               class="btn btn-light btn-lg">
                                <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/index.php?page=book-appointment" 
                            class="btn btn-light btn-lg">
                                <i class="bi bi-calendar-plus"></i> Agendar Cita
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=register" 
                       class="btn btn-light btn-lg">
                        <i class="bi bi-person-plus"></i> Crear Cuenta
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>