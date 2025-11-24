<?php
requireAdmin();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Configuration.php';

$database = new Database();
$db = $database->getConnection();
$configModel = new Configuration($db);

$categories = $configModel->getCategories();
$allConfigs = $configModel->getAll();

// Agrupar por categoría
$groupedConfigs = [];
foreach ($allConfigs as $config) {
    $groupedConfigs[$config['categoria']][] = $config;
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-gear"></i> Configuración del Sistema
            </h1>
            <p class="text-muted">Personaliza los parámetros de tu aplicación</p>
        </div>
    </div>
    
    <form action="<?php echo BASE_URL; ?>/index.php?action=update-settings" method="POST">
        <?php echo csrfField(); ?>
        
        <!-- Tabs de Categorías -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
            <?php $first = true; ?>
            <?php foreach ($categories as $category): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                        id="tab-<?php echo $category; ?>" 
                        data-bs-toggle="tab" 
                        data-bs-target="#category-<?php echo $category; ?>" 
                        type="button">
                    <i class="bi bi-<?php echo getCategoryIcon($category); ?>"></i>
                    <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
                </button>
            </li>
            <?php $first = false; ?>
            <?php endforeach; ?>
        </ul>
        
        <!-- Contenido de Tabs -->
        <div class="tab-content" id="settingsTabContent">
            <?php $first = true; ?>
            <?php foreach ($groupedConfigs as $category => $configs): ?>
            <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                 id="category-<?php echo $category; ?>" 
                 role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-<?php echo getCategoryIcon($category); ?>"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php foreach ($configs as $config): ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <label for="<?php echo $config['clave']; ?>" class="form-label fw-bold">
                                        <?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?>
                                    </label>
                                    <?php if ($config['descripcion']): ?>
                                    <small class="text-muted d-block mb-2">
                                        <?php echo e($config['descripcion']); ?>
                                    </small>
                                    <?php endif; ?>
                                    
                                    <?php if ($config['tipo'] === 'textarea'): ?>
                                    <textarea class="form-control" 
                                              id="<?php echo $config['clave']; ?>" 
                                              name="<?php echo $config['clave']; ?>" 
                                              rows="3"><?php echo e($config['valor']); ?></textarea>
                                    
                                    <?php elseif ($config['tipo'] === 'boolean'): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="<?php echo $config['clave']; ?>" 
                                               name="<?php echo $config['clave']; ?>" 
                                               value="1"
                                               <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="<?php echo $config['clave']; ?>">
                                            <?php echo $config['valor'] == '1' ? 'Activado' : 'Desactivado'; ?>
                                        </label>
                                    </div>
                                    
                                    <?php else: ?>
                                    <input type="<?php echo getInputType($config['tipo']); ?>" 
                                           class="form-control" 
                                           id="<?php echo $config['clave']; ?>" 
                                           name="<?php echo $config['clave']; ?>" 
                                           value="<?php echo e($config['valor']); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $first = false; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Alert de Advertencia -->
    <div class="alert-off alert-warning mt-4">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Importante:</strong> Los cambios en la configuración del sistema pueden afectar el funcionamiento de la aplicación. 
        Asegúrate de conocer el impacto antes de modificar valores críticos.
    </div>
</div>

<?php
function getCategoryIcon($category) {
    $icons = [
        'general' => 'gear',
        'contacto' => 'envelope',
        'horarios' => 'clock',
        'citas' => 'calendar',
        'email' => 'at',
        'notificaciones' => 'bell',
        'redes_sociales' => 'share'
    ];
    return $icons[$category] ?? 'gear';
}

function getInputType($type) {
    $types = [
        'email' => 'email',
        'phone' => 'tel',
        'number' => 'number',
        'text' => 'text'
    ];
    return $types[$type] ?? 'text';
}
?>