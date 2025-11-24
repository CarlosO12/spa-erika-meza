<?php
$email = $_GET['email'] ?? '';
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
            <div class="card shadow-lg border-0 rounded-3 my-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-envelope-check text-primary" style="font-size: 3rem;"></i>
                        <h2 class="fw-bold mt-2">Verificar Cuenta</h2>
                        <p class="text-muted">Hemos enviado un código de verificación a tu correo electrónico</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Revisa tu bandeja de entrada (y spam) en <strong><?php echo e($email); ?></strong>
                    </div>
                    
                    <form action="<?php echo BASE_URL; ?>/index.php?action=verify" method="POST">
                        <input type="hidden" name="email" value="<?php echo e($email); ?>">
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Código de Verificación</label>
                            <input type="text" class="form-control form-control-lg text-center" 
                                   id="code" name="code" maxlength="6" required autofocus
                                   placeholder="000000" style="letter-spacing: 0.5em; font-size: 1.5rem;">
                            <small class="text-muted">Ingresa el código de 6 dígitos</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="bi bi-check-circle"></i> Verificar Cuenta
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">¿No recibiste el código?</p>
                        <button class="btn btn-outline-secondary btn-sm" id="resendCode">
                            <i class="bi bi-arrow-clockwise"></i> Reenviar Código
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Solo permitir números en el campo de código
    document.getElementById('code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Funcionalidad de reenviar código
    document.getElementById('resendCode').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
        
        // Simular envío
        setTimeout(() => {
            alert('Código reenviado exitosamente');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Reenviar Código';
        }, 2000);
    });
</script>