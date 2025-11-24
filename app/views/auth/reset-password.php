<?php
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: ' . BASE_URL . '/index.php?page=forgot-password');
    exit();
}
?>

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
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-3 mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-lock-fill text-primary" style="font-size: 3rem;"></i>
                        <h2 class="fw-bold mt-2">Nueva Contraseña</h2>
                        <p class="text-muted">Ingresa tu nueva contraseña</p>
                    </div>
                    
                    <form action="<?php echo BASE_URL; ?>/index.php?action=reset-password" method="POST">
                        <input type="hidden" name="token" value="<?php echo e($token); ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" 
                                       name="password" required>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Mínimo 8 caracteres, incluir mayúsculas, minúsculas y números
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Contraseña *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirm" 
                                       name="password_confirm" required>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('password_confirm', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="bi bi-check-circle"></i> Restablecer Contraseña
                        </button>
                        
                        <div class="text-center">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=login" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Volver al Inicio de Sesión
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>