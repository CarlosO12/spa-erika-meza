<?php
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="container">
    <div class="row mb-5 mt-4">
        <div class="col-12 text-center">
            <h1 class="fw-bold text-gradient mb-3">Contacto</h1>
            <p class="lead text-muted">Estamos aquí para ayudarte</p>
        </div>
    </div>
    
    <?php displayFlashMessage(); ?>
    
    <div class="row">
        <!-- Información de Contacto -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Información de Contacto</h4>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-geo-alt text-primary fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold">Dirección</h6>
                                <p class="text-muted mb-0"><?php echo APP_ADDRESS; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-telephone text-primary fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold">Teléfono</h6>
                                <p class="text-muted mb-0">
                                    <a href="tel:<?php echo APP_PHONE; ?>" 
                                       class="text-decoration-none text-muted">
                                        <?php echo APP_PHONE; ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-envelope text-primary fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold">Email</h6>
                                <p class="text-muted mb-0">
                                    <a href="mailto:<?php echo APP_EMAIL; ?>" 
                                       class="text-decoration-none text-muted">
                                        <?php echo APP_EMAIL; ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-clock text-primary fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold">Horario de Atención</h6>
                                <p class="text-muted mb-0">
                                    <?= config('business_schedule') ?><br>
                                    <?= config('business_hours_start') ?> - <?= config('business_hours_end') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div>
                        <h6 class="fw-bold mb-3">Síguenos</h6>
                        <div class="d-flex gap-3">
                            <a href="<?= config('facebook_url') ?>" 
                               class="btn btn-outline-primary btn-sm" 
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="<?= config('instagram_url') ?>" 
                               class="btn btn-outline-primary btn-sm" 
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/\D/', '', config('whatsapp_number')) ?>" 
                               class="btn btn-outline-primary btn-sm" 
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="<?= config('tiktok_url') ?>" 
                               class="btn btn-outline-primary btn-sm" 
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="bi bi-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulario de Contacto -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Envíanos un Mensaje</h4>
                    
                    <?php if (!empty($_SESSION['errors'])): ?>
                        <div class="alert-off alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>
                    
                    <form id="contactForm" 
                          action="<?php echo BASE_URL; ?>/index.php?action=contact" 
                          method="POST"
                          novalidate>
                        
                        
                        <!-- Honeypot anti-spam (campo oculto) -->
                        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       placeholder="3001234567"
                                       value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="subject" class="form-label">Asunto *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="informacion" <?php echo ($formData['subject'] ?? '') === 'informacion' ? 'selected' : ''; ?>>
                                        Información General
                                    </option>
                                    <option value="cita" <?php echo ($formData['subject'] ?? '') === 'cita' ? 'selected' : ''; ?>>
                                        Agendar Cita
                                    </option>
                                    <option value="servicio" <?php echo ($formData['subject'] ?? '') === 'servicio' ? 'selected' : ''; ?>>
                                        Consulta sobre Servicios
                                    </option>
                                    <option value="queja" <?php echo ($formData['subject'] ?? '') === 'queja' ? 'selected' : ''; ?>>
                                        Queja o Reclamo
                                    </option>
                                    <option value="otro" <?php echo ($formData['subject'] ?? '') === 'otro' ? 'selected' : ''; ?>>
                                        Otro
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="mensaje" class="form-label">Mensaje *</label>
                                <textarea class="form-control" id="mensaje" name="mensaje" rows="6" 
                                          required><?php echo htmlspecialchars($formData['mensaje'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send"></i> Enviar Mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Preguntas Frecuentes -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Preguntas Frecuentes</h4>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq1">
                                    ¿Cómo puedo agendar una cita?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Puedes agendar tu cita fácilmente a través de nuestra plataforma en línea. 
                                    Solo necesitas crear una cuenta, elegir el servicio que deseas, seleccionar 
                                    la fecha y hora disponible, y confirmar tu reserva.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq2">
                                    ¿Puedo cancelar o reprogramar mi cita?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Sí, puedes cancelar o reprogramar tu cita con al menos 24 horas de 
                                    anticipación sin ningún costo. Después de ese tiempo, podría aplicar 
                                    una penalización.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq3">
                                    ¿Qué métodos de pago aceptan?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Aceptamos efectivo, tarjetas de débito y crédito, y transferencias 
                                    bancarias. El pago se realiza directamente en nuestro establecimiento 
                                    después del servicio.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#faq4">
                                    ¿Ofrecen promociones o descuentos?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Sí, regularmente ofrecemos promociones especiales. Te recomendamos 
                                    seguirnos en redes sociales y suscribirte a nuestro newsletter para 
                                    estar al tanto de todas nuestras ofertas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación básica del formulario
document.getElementById('contactForm').addEventListener('submit', function(e) {
    const telefono = document.getElementById('telefono').value;
    
    // Validar teléfono colombiano si se proporciona
    if (telefono && !/^3\d{9}$/.test(telefono.replace(/\s/g, ''))) {
        e.preventDefault();
        alert('Por favor ingresa un número de teléfono válido (10 dígitos, inicia con 3)');
        return false;
    }
});
</script>