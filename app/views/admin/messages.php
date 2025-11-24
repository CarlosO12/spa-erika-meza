<?php
/**
 * Vista de Administración de Mensajes de Contacto
 * SPA Erika Meza
 */
requireAdmin();

require_once APP_PATH . '/controllers/ContactController.php';
$contactController = new ContactController();

// Obtener estadísticas
$stats = $contactController->getStats();

// Filtro por estado
$filtroEstado = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null;
$filtroBusqueda = isset($_GET['search']) && $_GET['search'] !== '' ? $_GET['search'] : null;

$mensajes = $contactController->getMessages($filtroEstado, $filtroBusqueda);

// Mapeo de estados
$estadosMap = [
    'nuevo' => ['badge' => 'bg-primary', 'texto' => 'Nuevo', 'icono' => 'envelope'],
    'leido' => ['badge' => 'bg-info', 'texto' => 'Leído', 'icono' => 'envelope-open'],
    'respondido' => ['badge' => 'bg-success', 'texto' => 'Respondido', 'icono' => 'check-circle']
];

// Mapeo de asuntos
$asuntosMap = [
    'informacion' => ['icono' => 'info-circle', 'color' => 'text-info'],
    'cita' => ['icono' => 'calendar-check', 'color' => 'text-primary'],
    'servicio' => ['icono' => 'spa', 'color' => 'text-success'],
    'queja' => ['icono' => 'exclamation-triangle', 'color' => 'text-warning'],
    'otro' => ['icono' => 'question-circle', 'color' => 'text-secondary']
];
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-envelope-paper"></i> Mensajes de Contacto
            </h1>
            <p class="text-muted">Gestiona los mensajes recibidos desde el formulario de contacto</p>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Mensajes</p>
                            <h3 class="fw-bold mb-0"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-envelope-paper"></i>
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
                            <p class="text-muted mb-1">Nuevos</p>
                            <h3 class="fw-bold mb-0 text-primary"><?= $stats['nuevos'] ?></h3>
                        </div>
                        <div class="stats-icon bg-primary">
                            <i class="bi bi-envelope"></i>
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
                            <p class="text-muted mb-1">Leídos</p>
                            <h3 class="fw-bold mb-0 text-info"><?= $stats['leidos'] ?></h3>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="bi bi-envelope-open"></i>
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
                            <p class="text-muted mb-1">Respondidos</p>
                            <h3 class="fw-bold mb-0 text-success"><?= $stats['respondidos'] ?></h3>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin-messages">
                
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos los estados</option>
                        <option value="nuevo" <?= $filtroEstado === 'nuevo' ? 'selected' : '' ?>>
                            Nuevos
                        </option>
                        <option value="leido" <?= $filtroEstado === 'leido' ? 'selected' : '' ?>>
                            Leídos
                        </option>
                        <option value="respondido" <?= $filtroEstado === 'respondido' ? 'selected' : '' ?>>
                            Respondidos
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= $filtroBusqueda ?>"
                           placeholder="Nombre o email...">
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="<?= BASE_URL ?>/index.php?page=admin-messages" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Mensajes -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($mensajes)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No hay mensajes</h4>
                    <p class="text-muted">
                        <?php if ($filtroEstado): ?>
                            No se encontraron mensajes con el estado seleccionado.
                        <?php else: ?>
                            Aún no has recibido ningún mensaje de contacto.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Estado</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Asunto</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mensajes as $mensaje): ?>
                                <?php 
                                $estadoInfo = $estadosMap[$mensaje['estado']];
                                $asuntoInfo = $asuntosMap[$mensaje['asunto']] ?? ['icono' => 'circle', 'color' => 'text-muted'];
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $estadoInfo['badge'] ?>" 
                                              title="<?= $estadoInfo['texto'] ?>">
                                            <i class="bi bi-<?= $estadoInfo['icono'] ?>"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= e($mensaje['nombre']) ?></strong>
                                            <?php if (!empty($mensaje['telefono'])): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-telephone"></i> 
                                                    <?= e($mensaje['telefono']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= e($mensaje['email']) ?><br>
                                        <small class="text-muted">
                                            <a href="javascript:void(0)" 
                                               class="text-decoration-none"
                                               onclick="replyMessage(<?= htmlspecialchars(json_encode($mensaje)) ?>)">
                                                <i class="bi bi-reply"></i> Responder
                                            </a>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-<?= $asuntoInfo['icono'] ?> <?= $asuntoInfo['color'] ?>"></i>
                                            <?php
                                            $asuntoTexto = [
                                                'informacion' => 'Información',
                                                'cita' => 'Cita',
                                                'servicio' => 'Servicios',
                                                'queja' => 'Queja',
                                                'otro' => 'Otro'
                                            ];
                                            echo $asuntoTexto[$mensaje['asunto']] ?? $mensaje['asunto'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px;">
                                            <?php 
                                            $preview = strlen($mensaje['mensaje']) > 80 
                                                ? substr($mensaje['mensaje'], 0, 80) . '...' 
                                                : $mensaje['mensaje'];
                                            echo nl2br(e($preview));
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= formatDate($mensaje['fecha_envio']) ?>
                                        </span><br>
                                        <small class="text-muted">
                                            <strong><?= date('h:i A', strtotime($mensaje['fecha_envio'])) ?></strong>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Ver detalle -->
                                            <button type="button" 
                                                    class="btn btn-outline-info"
                                                    onclick="viewMessage(<?= htmlspecialchars(json_encode($mensaje)) ?>)"
                                                    title="Ver mensaje">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <!-- Marcar como leído -->
                                            <?php if ($mensaje['estado'] === 'nuevo'): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-primary"
                                                    onclick="updateMessageStatus(<?= $mensaje['id'] ?>, 'leido')"
                                                    title="Marcar como leído">
                                                <i class="bi bi-envelope-open"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <!-- Marcar como respondido -->
                                            <?php if ($mensaje['estado'] !== 'respondido'): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-success"
                                                    onclick="updateMessageStatus(<?= $mensaje['id'] ?>, 'respondido')"
                                                    title="Marcar como respondido">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <!-- Eliminar -->
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="deleteMessage(<?= $mensaje['id'] ?>)"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<!-- Modal Ver Mensaje -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageDetails">
                <!-- Se llenará con JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="replyFromModal()">
                    <i class="bi bi-reply"></i> Responder
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Responder Mensaje -->
<div class="modal fade" id="replyMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-reply"></i> Responder Mensaje
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/index.php?action=reply-message" method="POST">
                <input type="hidden" id="reply_message_id" name="message_id">
                <div class="modal-body">
                    <!-- Información del destinatario -->
                    <div class="alert-info mb-3 p-3 border rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="bi bi-person"></i> Para:</strong> 
                                <span id="reply_to_name">Cargando...</span>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="bi bi-envelope"></i> Email:</strong> 
                                <span id="reply_to_email">Cargando...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje original -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mensaje original:</label>
                        <div class="bg-light p-3 rounded" id="original_message" style="max-height: 150px; overflow-y: auto; white-space: pre-wrap;">
                        </div>
                    </div>

                    <hr>

                    <!-- Asunto de la respuesta -->
                    <div class="mb-3">
                        <label for="reply_subject" class="form-label fw-bold">Asunto *</label>
                        <input type="text" class="form-control" id="reply_subject" name="subject" required>
                    </div>

                    <!-- Respuesta -->
                    <div class="mb-3">
                        <label for="reply_message" class="form-label fw-bold">Tu respuesta *</label>
                        <textarea class="form-control" id="reply_message" name="message" rows="8" 
                                  placeholder="Escribe tu respuesta aquí..."
                                  required></textarea>
                    </div>

                    <!-- Plantillas rápidas -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Plantillas rápidas:</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="insertTemplate('agradecimiento')">
                                <i class="bi bi-chat-left-text"></i> Agradecimiento
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="insertTemplate('info_recibida')">
                                <i class="bi bi-check-circle"></i> Info Recibida
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="insertTemplate('agendar_cita')">
                                <i class="bi bi-calendar"></i> Agendar Cita
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Enviar Respuesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentMessageData = null;

function viewMessage(mensaje) {
    currentMessageData = mensaje;
    
    const asuntoTexto = {
        'informacion': 'Información General',
        'cita': 'Agendar Cita',
        'servicio': 'Consulta sobre Servicios',
        'queja': 'Queja o Reclamo',
        'otro': 'Otro'
    };

    const estadoTexto = {
        'nuevo': 'Nuevo',
        'leido': 'Leído',
        'respondido': 'Respondido'
    };

    const estadoBadge = {
        'nuevo': 'bg-primary',
        'leido': 'bg-info',
        'respondido': 'bg-success'
    };

    const detailsHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">Información del Remitente</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td>${mensaje.nombre}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>
                            <a href="mailto:${mensaje.email}">${mensaje.email}</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>${mensaje.telefono || 'No proporcionado'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">Detalles del Mensaje</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Asunto:</strong></td>
                        <td>
                            <span class="badge bg-light text-dark">
                                ${asuntoTexto[mensaje.asunto] || mensaje.asunto}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            <span class="badge ${estadoBadge[mensaje.estado]}">
                                ${estadoTexto[mensaje.estado]}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>${formatDate(mensaje.fecha_envio)} ${formatTime(mensaje.fecha_envio)}</td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3">Mensaje</h6>
                <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">
                    ${mensaje.mensaje}
                </div>
            </div>
        </div>
    `;

    document.getElementById('messageDetails').innerHTML = detailsHTML;
    
    const modal = new bootstrap.Modal(document.getElementById('viewMessageModal'));
    modal.show();
}

function replyFromModal() {
    if (!currentMessageData) {
        console.error('No hay datos del mensaje actual');
        return;
    }
    
    // Cerrar modal de ver mensaje
    const viewModalElement = document.getElementById('viewMessageModal');
    if (viewModalElement) {
        const viewModal = bootstrap.Modal.getInstance(viewModalElement);
        if (viewModal) {
            viewModal.hide();
        }
    }
    
    // Pequeño delay para evitar conflictos entre modales
    setTimeout(() => {
        replyMessage(currentMessageData);
    }, 300);
}

function replyMessage(mensaje) {
    // Guardar mensaje actual para las plantillas
    currentMessageData = mensaje;
    
    const asuntoTexto = {
        'informacion': 'Información General',
        'cita': 'Agendar Cita',
        'servicio': 'Consulta sobre Servicios',
        'queja': 'Queja o Reclamo',
        'otro': 'Otro'
    };
    
    // Abrir modal primero
    const modalElement = document.getElementById('replyMessageModal');
    if (!modalElement) {
        console.error('Modal replyMessageModal no encontrado en el DOM');
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Esperar a que el modal esté completamente visible
    modalElement.addEventListener('shown.bs.modal', function fillModalData() {
        // Remover el listener después de usarlo
        modalElement.removeEventListener('shown.bs.modal', fillModalData);
        
        // Ahora sí buscar los elementos
        const replyMessageId = document.getElementById('reply_message_id');
        const replyToName = document.getElementById('reply_to_name');
        const replyToEmail = document.getElementById('reply_to_email');
        const originalMessage = document.getElementById('original_message');
        const replySubject = document.getElementById('reply_subject');
        const replyMessageTextarea = document.getElementById('reply_message');
        
        // Llenar datos del modal
        if (replyMessageId) replyMessageId.value = mensaje.id;
        if (replyToName) replyToName.textContent = mensaje.nombre;
        if (replyToEmail) replyToEmail.textContent = mensaje.email;
        if (originalMessage) originalMessage.textContent = mensaje.mensaje;
        
        // Asunto predeterminado
        const asuntoOriginal = asuntoTexto[mensaje.asunto] || mensaje.asunto;
        if (replySubject) replySubject.value = `Re: ${asuntoOriginal}`;
        
        // Limpiar mensaje de respuesta
        if (replyMessageTextarea) replyMessageTextarea.value = '';
    });
}

function insertTemplate(tipo) {
    const nombre = currentMessageData?.nombre || '[Nombre]';
    
    const templates = {
        'agradecimiento': `Hola ${nombre},

Gracias por contactarnos. Hemos recibido tu mensaje y apreciamos tu interés en nuestros servicios.

Atentamente,
Equipo de <?= APP_NAME ?>`,
        
        'info_recibida': `Hola ${nombre},

Hemos recibido tu solicitud de información. Nos pondremos en contacto contigo a la brevedad posible para atender tu consulta.

Si necesitas atención inmediata, puedes llamarnos al <?= APP_PHONE ?>.

Saludos cordiales,
<?= APP_NAME ?>`,
        
        'agendar_cita': `Hola ${nombre},

¡Gracias por tu interés en agendar una cita!

Para programar tu cita, puedes:
- Visitar nuestra página web: <?= BASE_URL ?>
- Llamarnos al: <?= APP_PHONE ?>
- Responder este correo con tu disponibilidad

¡Esperamos verte pronto!
Equipo de <?= APP_NAME ?>`
    };
    
    const textarea = document.getElementById('reply_message');
    if (textarea && templates[tipo]) {
        textarea.value = templates[tipo];
        textarea.focus();
    }
}

function updateMessageStatus(messageId, newStatus) {
    const statusNames = {
        'leido': 'Leído',
        'respondido': 'Respondido'
    };
    
    if (confirm(`¿Cambiar el estado del mensaje a "${statusNames[newStatus]}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/index.php?action=update-message-status';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = messageId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'estado';
        statusInput.value = newStatus;
        
        form.appendChild(idInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteMessage(messageId) {
    if (confirm('¿Estás seguro de eliminar este mensaje?\n\nEsta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/index.php?action=delete-message';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = messageId;
        
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('es-CO', {
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>