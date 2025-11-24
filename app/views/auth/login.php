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
    <div class="container mt-4">
            <?php echo displayFlashMessage(); ?>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-3 mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="img/favicon.png" alt="icono" class="bi" width="54" height="54">
                        <h2 class="fw-bold mt-2">Iniciar Sesión</h2>
                        <p class="text-muted">Accede a tu cuenta de <?php echo APP_NAME; ?></p>
                    </div>
                    
                    <form action="<?php echo BASE_URL; ?>/index.php?action=login" method="POST">
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </button>
                        
                        <div class="text-center">
                            <a href="<?php echo BASE_URL; ?>/index.php?page=forgot-password" class="text-decoration-none">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">¿No tienes cuenta? 
                                <a href="<?php echo BASE_URL; ?>/index.php?page=register" class="fw-bold text-decoration-none">
                                    Regístrate aquí
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
</script>