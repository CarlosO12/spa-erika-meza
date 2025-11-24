<?php
requireAuth();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$user = $userModel->findById(getUserId());
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-person-circle"></i> Mi Perfil
            </h1>
        </div>
    </div>
    
    <div class="row">
        <!-- Información Personal -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person"></i> Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>/index.php?action=update-profile" method="POST">
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo e($user['nombre']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo e($user['email']); ?>" disabled>
                            <small class="text-muted">El email no se puede cambiar</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo e($user['telefono']); ?>" placeholder="3001234567">
                        </div>
                        
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" 
                                      rows="2"><?php echo e($user['direccion'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Cambiar Contraseña -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-lock"></i> Cambiar Contraseña
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>/index.php?action=change-password" method="POST">
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual *</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password" required>
                            <small class="text-muted">
                                Mínimo 8 caracteres, incluir mayúsculas, minúsculas y números
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Información de Cuenta -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo getInitials($user['nombre']); ?>
                        </div>
                    </div>
                    <h5 class="fw-bold"><?php echo e($user['nombre']); ?></h5>
                    <p class="text-muted mb-2"><?php echo e($user['email']); ?></p>
                    <span class="badge bg-success">
                        <?php echo ucfirst($user['rol']); ?>
                    </span>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h6 class="fw-bold mb-0">Información de Cuenta</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-calendar-check text-primary"></i>
                            <strong>Miembro desde:</strong><br>
                            <small class="text-muted"><?php echo formatDate($user['creado']); ?></small>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-shield-check text-success"></i>
                            <strong>Estado:</strong><br>
                            <?php if ($user['verificado']): ?>
                            <span class="badge bg-success">Verificado</span>
                            <?php else: ?>
                            <span class="badge bg-warning">No Verificado</span>
                            <?php endif; ?>
                        </li>
                        <li>
                            <i class="bi bi-person-badge text-info"></i>
                            <strong>Rol:</strong><br>
                            <small class="text-muted"><?php echo ucfirst($user['rol']); ?></small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>