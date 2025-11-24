<?php
requireAdmin();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Service.php';

$database = new Database();
$db = $database->getConnection();
$serviceModel = new Service($db);

$services = $serviceModel->getAllAdmin();
$categories = SERVICE_CATEGORIES;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fw-bold text-gradient">
                    <i class="bi bi-grid-3x3-gap"></i> Gestión de Servicios
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Servicio
                </button>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Duración</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($service['imagen']): ?>
                                    <img src="<?php echo ASSETS_URL . 'uploads/' . $service['imagen']; ?>" 
                                         alt="<?php echo e($service['nombre']); ?>"
                                         class="rounded me-2"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                    <span><?php echo e($service['nombre']); ?></span>
                                </div>
                            </td>
                            <td><?php echo e($service['categoria']); ?></td>
                            <td><?php echo formatPrice($service['precio']); ?></td>
                            <td><?php echo $service['duracion']; ?> min</td>
                            <td>
                                <?php if ($service['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)"
                                        title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteService(<?php echo $service['id']; ?>)"
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Servicio -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/index.php?action=create-service" method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categories as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="precio" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio" name="precio" 
                                       min="0" step="1000" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="duracion" class="form-label">Duración (minutos) *</label>
                            <input type="number" class="form-control" id="duracion" name="duracion" 
                                   min="15" step="15" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label for="imagen" class="form-label">Imagen</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="activo" 
                                       name="activo" checked>
                                <label class="form-check-label" for="activo">
                                    Servicio Activo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Servicio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Servicio -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/index.php?action=update-service" method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" id="edit_service_id" name="service_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="edit_categoria" name="categoria" required>
                                <?php foreach ($categories as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_precio" class="form-label">Precio *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_precio" name="precio" 
                                       min="0" step="1000" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_duracion" class="form-label">Duración (minutos) *</label>
                            <input type="number" class="form-control" id="edit_duracion" name="duracion" 
                                   min="15" step="15" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="edit_descripcion" class="form-label">Descripción *</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                      rows="3" required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label for="edit_imagen" class="form-label">Nueva Imagen (opcional)</label>
                            <input type="file" class="form-control" id="edit_imagen" name="imagen" accept="image/*">
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_activo" name="activo">
                                <label class="form-check-label" for="edit_activo">
                                    Servicio Activo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editService(service) {
    document.getElementById('edit_service_id').value = service.id;
    document.getElementById('edit_nombre').value = service.nombre;
    document.getElementById('edit_categoria').value = service.categoria;
    document.getElementById('edit_precio').value = service.precio;
    document.getElementById('edit_duracion').value = service.duracion;
    document.getElementById('edit_descripcion').value = service.descripcion;
    document.getElementById('edit_activo').checked = service.activo == 1;
    
    const modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
    modal.show();
}

function deleteService(id) {
    if (confirm('¿Estás seguro de eliminar este servicio?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/index.php?action=delete-service';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'service_id';
        idInput.value = id;
        
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>