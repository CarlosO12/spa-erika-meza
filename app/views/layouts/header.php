<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SPA Erika Meza - Servicios de belleza y relajación">
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>img/favicon.png">
    <title><?php echo APP_NAME; ?> - <?php echo ucfirst($page ?? 'Inicio'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    
    <?php if (isAdmin()): ?>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/admin.css">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?php echo BASE_URL; ?>/index.php">
                <img src="img/favicon.png" alt="icono" class="bi" width="24" height="24"> <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=services">Servicios</a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isClient()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=specialists">Especialistas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=my-appointments">Mis Citas</a>
                            </li>
                            <!--
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=cart">
                                    <i class="bi bi-cart"></i> Carrito
                                </a>
                            </li>
                            -->
                        <?php endif; ?>
                        
                        <?php if (isSpecialist()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=specialist-dashboard">Panel</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=specialist-appointments">Mis Citas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=specialist-schedule">Mi Horario</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=admin-messages">
                                    <i class="bi bi-envelope-paper"></i> Mensajes
                                    <?php
                                    require_once APP_PATH . '/controllers/ContactController.php';
                                    $contactController = new ContactController();
                                    $stats = $contactController->getStats();
                                    if ($stats['nuevos'] > 0):
                                    ?>
                                        <span class="badge bg-danger rounded-pill ms-2">
                                            <?= $stats['nuevos'] ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=admin-dashboard">
                                    <i class="bi bi-speedometer2"></i> Administración
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo e(getUserName()); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item <?= $page === 'profile' ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>/index.php?page=profile">
                                        <i class="bi bi-person"></i> Mi Perfil
                                    </a></li>
                                <?php if (isClient()): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/index.php?page=history">
                                        <i class="bi bi-clock-history"></i> Historial
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/index.php?page=user-dashboard">
                                        <i class="bi bi-speedometer2"></i> Mi Panel
                                    </a></li>
                                <?php endif; ?>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item <?= $page === 'admin-settings' ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>/index.php?page=admin-settings">
                                        <i class="bi bi-gear"></i> Ajustes
                                    </a></li>
                                    <li><a class="dropdown-item <?= $page === 'admin-reviews' ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>/index.php?page=admin-reviews">
                                        <i class="bi bi-star"></i> Reseñas
                                    </a></li>
                                <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/index.php?action=logout">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=contact">Contactanos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=about">Nosotros</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?page=login">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="<?php echo BASE_URL; ?>/index.php?page=register">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="main-content">
        <div class="container mt-4">
            <?php echo displayFlashMessage(); ?>
        </div>