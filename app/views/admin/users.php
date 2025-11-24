<?php
requireAdmin();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$roleFilter = $_GET['rol'] ?? null;
$searchFilter = $_GET['search'] ?? null;
$estadoFilter = $_GET['verificado'] ?? null;

$usuarios = $userModel->getAll($roleFilter, $searchFilter, $estadoFilter);

// Días de la semana para el horario
$diasSemana = [
    '1' => 'Lunes',
    '2' => 'Martes',
    '3' => 'Miércoles',
    '4' => 'Jueves',
    '5' => 'Viernes',
    '6' => 'Sábado',
    '0' => 'Domingo'
];
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fw-bold text-gradient">
                    <i class="bi bi-people"></i> Gestión de Usuarios
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Nuevo Usuario
                </button>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas Rápidas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">
                                <?php echo ($searchFilter || $roleFilter || $estadoFilter) ? 'Resultados' : 'Total Usuarios'; ?>
                            </p>
                            <h3 class="fw-bold mb-0"><?php echo count($usuarios); ?></h3>
                            <?php if ($searchFilter || $roleFilter || $estadoFilter): ?>
                                <small class="text-muted">Filtrado</small>
                            <?php endif; ?>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Clientes</p>
                            <h3 class="fw-bold mb-0">
                                <?php echo $userModel->countByRole(ROLE_CLIENT); ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Especialistas</p>
                            <h3 class="fw-bold mb-0">
                                <?php echo $userModel->countByRole(ROLE_SPECIALIST); ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Administradores</p>
                            <h3 class="fw-bold mb-0">
                                <?php echo $userModel->countByRole(ROLE_ADMIN); ?>
                            </h3>
                        </div>
                        <div class="stats-icon bg-warning">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <input type="hidden" name="page" value="admin-users">
                
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Nombre o email..."
                           value="<?php echo htmlspecialchars($searchFilter ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="role" class="form-label">Rol</label>
                    <select class="form-select" id="role" name="rol">
                        <option value="">Todos los roles</option>
                        <option value="<?php echo ROLE_CLIENT; ?>" <?php echo $roleFilter === ROLE_CLIENT ? 'selected' : ''; ?>>
                            Cliente
                        </option>
                        <option value="<?php echo ROLE_SPECIALIST; ?>" <?php echo $roleFilter === ROLE_SPECIALIST ? 'selected' : ''; ?>>
                            Especialista
                        </option>
                        <option value="<?php echo ROLE_ADMIN; ?>" <?php echo $roleFilter === ROLE_ADMIN ? 'selected' : ''; ?>>
                            Administrador
                        </option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="verified" class="form-label">Estado</label>
                    <select class="form-select" id="verified" name="verificado">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $estadoFilter === '1' ? 'selected' : ''; ?>>Verificados</option>
                        <option value="0" <?php echo $estadoFilter === '0' ? 'selected' : ''; ?>>No Verificados</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?page=admin-users" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Usuarios -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No se encontraron usuarios</h4>
                    <p class="text-muted">
                        <?php if ($searchFilter || $roleFilter || $estadoFilter): ?>
                            Intenta ajustar los filtros de búsqueda.
                        <?php else: ?>
                            Aún no hay usuarios registrados en el sistema.
                        <?php endif; ?>
                    </p>
                    <?php if ($searchFilter || $roleFilter || $estadoFilter): ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?page=admin-users" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise"></i> Ver todos los usuarios
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                         style="width: 40px; height: 40px; font-size: 0.9rem;">
                                        <?php echo getInitials($usuario['nombre']); ?>
                                    </div>
                                    <strong><?php echo e($usuario['nombre']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo e($usuario['email']); ?></td>
                            <td><?php echo e($usuario['telefono'] ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                $roleBadges = [
                                    ROLE_ADMIN => 'bg-danger',
                                    ROLE_SPECIALIST => 'bg-info',
                                    ROLE_CLIENT => 'bg-success'
                                ];
                                $badgeClass = $roleBadges[$usuario['rol']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($usuario['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($usuario['verificado']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Verificado
                                </span>
                                <?php else: ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pendiente
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo formatDate($usuario['creado']); ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($usuario)); ?>)"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewUser(<?php echo htmlspecialchars(json_encode($usuario)); ?>)"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($usuario['id'] != getUserId()): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteUser(<?php echo $usuario['id']; ?>, '<?php echo e($usuario['nombre']); ?>')"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Agregar Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/index.php?action=create-user" method="POST">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Datos básicos -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-person"></i> Información Básica
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   placeholder="3001234567">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol *</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">Seleccionar...</option>
                                <option value="<?php echo ROLE_CLIENT; ?>">Cliente</option>
                                <option value="<?php echo ROLE_SPECIALIST; ?>">Especialista</option>
                                <option value="<?php echo ROLE_ADMIN; ?>">Administrador</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Mínimo 8 caracteres</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="password_confirm" 
                                   name="password_confirm" required>
                        </div>
                        
                        <!-- Campos de Especialista -->
                        <div id="specialistFields" style="display: none;" class="col-12">
                            <hr class="my-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-person-badge"></i> Información de Especialista
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="especialista" class="form-label">Especialidad</label>
                                    <input type="text" class="form-control" id="especialista" 
                                           name="especialista" placeholder="Ej: Manicurista, Pedicurista">
                                </div>
                                <div class="col-md-6">
                                    <label for="experiencia" class="form-label">Años de Experiencia</label>
                                    <input type="number" class="form-control" id="experiencia" 
                                           name="experiencia" placeholder="0">
                                </div>
                                <div class="col-12">
                                    <label for="descripcion" class="form-label">Biografía</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" 
                                              rows="3" placeholder="Breve descripción profesional..."></textarea>
                                </div>
                            </div>
                            
                            <!-- Horarios -->
                            <div class="mt-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-clock"></i> Horario de Disponibilidad
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 100px;">Activo</th>
                                                <th>Día</th>
                                                <th>Hora Inicio</th>
                                                <th>Hora Fin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($diasSemana as $key => $dia): ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input day-checkbox" 
                                                               type="checkbox" 
                                                               id="day_<?php echo $key; ?>"
                                                               name="dias_activos[]" 
                                                               value="<?php echo $key; ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="day_<?php echo $key; ?>" class="form-label mb-0">
                                                        <?php echo $dia; ?>
                                                    </label>
                                                </td>
                                                <td>
                                                    <input type="time" 
                                                           class="form-control form-control-sm" 
                                                           name="hora_inicio[<?php echo $key; ?>]"
                                                           id="inicio_<?php echo $key; ?>"
                                                           value="09:00"
                                                           disabled>
                                                </td>
                                                <td>
                                                    <input type="time" 
                                                           class="form-control form-control-sm" 
                                                           name="hora_fin[<?php echo $key; ?>]"
                                                           id="fin_<?php echo $key; ?>"
                                                           value="18:00"
                                                           disabled>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert_off alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <small>Selecciona los días de trabajo y ajusta los horarios según sea necesario.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/index.php?action=update-user" method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" id="edit_user_id" name="usuario_id">
                <input type="hidden" id="edit_user_rol" name="user_rol">
                <input type="hidden" id="edit_especialista_id" name="especialista_id">
                
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Datos básicos -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-person"></i> Información Básica
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required disabled>
                            <small class="text-muted">El email no se puede modificar</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="edit_telefono" name="telefono">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="edit_direccion" name="direccion" rows="1"></textarea>
                        </div>
                        
                        <!-- Campos de Especialista -->
                        <div id="editSpecialistFields" style="display: none;" class="col-12">
                            <hr class="my-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-person-badge"></i> Información de Especialista
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="edit_especialista" class="form-label">Especialidad</label>
                                    <input type="text" class="form-control" id="edit_especialista" 
                                           name="especialista" placeholder="Ej: Manicurista, Pedicurista">
                                </div>
                                <div class="col-12">
                                    <label for="edit_descripcion" class="form-label">Biografía</label>
                                    <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                              rows="3" placeholder="Breve descripción profesional..."></textarea>
                                </div>
                            </div>
                            
                            <!-- Horarios -->
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-primary mb-0">
                                        <i class="bi bi-clock"></i> Horario de Disponibilidad
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="loadCurrentSchedule()">
                                        <i class="bi bi-arrow-clockwise"></i> Cargar horario actual
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 100px;">Activo</th>
                                                <th>Día</th>
                                                <th>Hora Inicio</th>
                                                <th>Hora Fin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($diasSemana as $key => $dia): ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input edit-day-checkbox" 
                                                               type="checkbox" 
                                                               id="edit_day_<?php echo $key; ?>"
                                                               name="dias_activos[]" 
                                                               value="<?php echo $key; ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="edit_day_<?php echo $key; ?>" class="form-label mb-0">
                                                        <?php echo $dia; ?>
                                                    </label>
                                                </td>
                                                <td>
                                                    <input type="time" 
                                                           class="form-control form-control-sm" 
                                                           name="hora_inicio[<?php echo $key; ?>]"
                                                           id="edit_inicio_<?php echo $key; ?>"
                                                           value="09:00"
                                                           disabled>
                                                </td>
                                                <td>
                                                    <input type="time" 
                                                           class="form-control form-control-sm" 
                                                           name="hora_fin[<?php echo $key; ?>]"
                                                           id="edit_fin_<?php echo $key; ?>"
                                                           value="18:00"
                                                           disabled>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="custom-alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <small>Modifica los días de trabajo y ajusta los horarios según sea necesario.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- Se llenará con JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campos de especialista y habilitar/deshabilitar horarios
document.getElementById('rol').addEventListener('change', function() {
    const specialistFields = document.getElementById('specialistFields');
    if (this.value === '<?php echo ROLE_SPECIALIST; ?>') {
        specialistFields.style.display = 'block';
    } else {
        specialistFields.style.display = 'none';
    }
});

// Habilitar/deshabilitar campos de hora según checkbox
document.querySelectorAll('.day-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const day = this.value;
        const inicioInput = document.getElementById('inicio_' + day);
        const finInput = document.getElementById('fin_' + day);
        
        if (this.checked) {
            inicioInput.disabled = false;
            finInput.disabled = false;
        } else {
            inicioInput.disabled = true;
            finInput.disabled = true;
        }
    });
});

// Habilitar/deshabilitar campos de hora según checkbox (modal editar)
document.querySelectorAll('.edit-day-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const day = this.value;
        const inicioInput = document.getElementById('edit_inicio_' + day);
        const finInput = document.getElementById('edit_fin_' + day);
        
        if (this.checked) {
            inicioInput.disabled = false;
            finInput.disabled = false;
        } else {
            inicioInput.disabled = true;
            finInput.disabled = true;
        }
    });
});

async function editUser(user) {
    // Llenar datos básicos
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_user_rol').value = user.rol;
    document.getElementById('edit_nombre').value = user.nombre;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_telefono').value = user.telefono || '';
    document.getElementById('edit_direccion').value = user.direccion || '';
    
    // Limpiar horarios previos
    document.querySelectorAll('.edit-day-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        const day = checkbox.value;
        document.getElementById('edit_inicio_' + day).disabled = true;
        document.getElementById('edit_fin_' + day).disabled = true;
    });
    
    // Si es especialista, mostrar campos y cargar datos
    const specialistFields = document.getElementById('editSpecialistFields');
    if (user.rol === '<?php echo ROLE_SPECIALIST; ?>') {
        specialistFields.style.display = 'block';
        
        // Cargar datos del especialista
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/index.php?action=get-specialist-data&user_id=' + user.id);
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('edit_especialista_id').value = data.especialista.id;
                document.getElementById('edit_especialista').value = data.especialista.especialista || '';
                document.getElementById('edit_descripcion').value = data.especialista.descripcion || '';
                
                // Cargar horarios
                if (data.horarios && data.horarios.length > 0) {
                    data.horarios.forEach(horario => {
                        const checkbox = document.getElementById('edit_day_' + horario.dia_semana);
                        const inicioInput = document.getElementById('edit_inicio_' + horario.dia_semana);
                        const finInput = document.getElementById('edit_fin_' + horario.dia_semana);
                        
                        if (checkbox) {
                            checkbox.checked = true;
                            inicioInput.disabled = false;
                            finInput.disabled = false;
                            inicioInput.value = horario.hora_inicio;
                            finInput.value = horario.hora_fin;
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando datos del especialista:', error);
        }
    } else {
        specialistFields.style.display = 'none';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

function loadCurrentSchedule() {
    const especialistaId = document.getElementById('edit_especialista_id').value;
    
    if (!especialistaId) {
        alert('No se pudo cargar el horario');
        return;
    }
    
    // Ya está cargado en editUser, esta función es para refrescar
    location.reload();
}

function viewUser(user) {
    const detailsHTML = `
        <div class="text-center mb-3">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width: 80px; height: 80px; font-size: 2rem;">
                ${user.nombre.split(' ').map(n => n[0]).join('').substring(0, 2)}
            </div>
        </div>
        <h5 class="text-center fw-bold mb-3">${user.nombre}</h5>
        <table class="table table-borderless">
            <tr>
                <td><strong>Email:</strong></td>
                <td>${user.email}</td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong></td>
                <td>${user.telefono || 'N/A'}</td>
            </tr>
            <tr>
                <td><strong>Rol:</strong></td>
                <td><span class="badge bg-primary">${user.rol}</span></td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td>${user.verificado ? '<span class="badge bg-success">Verificado</span>' : '<span class="badge bg-warning">No Verificado</span>'}</td>
            </tr>
            <tr>
                <td><strong>Registro:</strong></td>
                <td>${new Date(user.creado).toLocaleDateString('es-CO')}</td>
            </tr>
        </table>
    `;
    
    document.getElementById('userDetails').innerHTML = detailsHTML;
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    modal.show();
}

function deleteUser(userId, userName) {
    if (confirm(`¿Estás seguro de eliminar al usuario "${userName}"?\n\nEsta acción no se puede deshacer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/index.php?action=delete-user';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'usuario_id';
        input.value = userId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>