<?php
requireRole(ROLE_CLIENT);


require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Specialist.php';

$database = new Database();
$db = $database->getConnection();
$serviceModel = new Service($db);
$specialistModel = new Specialist($db);

$services = $serviceModel->getAll();
$specialists = $specialistModel->getAll();

$preselectedService = isset($_GET['service']) ? (int)$_GET['service'] : null;
$preselectedSpecialist = isset($_GET['specialist']) ? (int)$_GET['specialist'] : null;
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-calendar-plus"></i> Agendar Nueva Cita
            </h1>
            <p class="text-muted">Reserva tu servicio en 3 simples pasos</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/index.php?action=create-appointment" 
                          method="POST" id="bookingForm">
                        <?php echo csrfField(); ?>
                        
                        <!-- Paso 1: Seleccionar Servicio --> 
                        <div class="booking-step active" id="step1">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                Selecciona el Servicio
                            </h5>
                            
                            <div class="row g-3">
                                <?php foreach ($services as $service): ?>
                                <div class="col-md-6">
                                    <div class="service-option">
                                        <input type="radio" name="servicio_id" 
                                            value="<?php echo $service['id']; ?>" 
                                            id="service_<?php echo $service['id']; ?>"
                                            data-duration="<?php echo $service['duracion']; ?>"
                                            <?php echo ($preselectedService === $service['id']) ? 'checked' : ''; ?>
                                            required>
                                        <label for="service_<?php echo $service['id']; ?>">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-1"><?php echo e($service['nombre']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo truncate($service['descripcion'], 60); ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-primary">
                                                        <?php echo formatPrice($service['precio']); ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo $service['duracion']; ?> min
                                                    </small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                    Siguiente <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Paso 2: Seleccionar Especialista y Fecha -->
                        <div class="booking-step" id="step2">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                Elige Especialista, Fecha y Hora
                            </h5>
                            
                            <div class="mb-3">
                                <label for="especialista_id" class="form-label">Especialista *</label>
                                <select class="form-select" id="especialista_id" name="especialista_id" required>
                                    <option value="">Seleccionar especialista...</option>
                                    <?php foreach ($specialists as $specialist): ?>
                                    <option value="<?php echo $specialist['id']; ?>"
                                            <?= $preselectedSpecialist === $specialist['id'] ? 'selected' : '' ?>>
                                        <?php echo e($specialist['nombre']); ?> 
                                        <?php if ($specialist['especialista']): ?>
                                        - <?php echo e($specialist['especialista']); ?>
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <!-- Botón para ver reseñas -->
                                <div id="reviewsLink" class="mt-2" style="display: none;">
                                    <a href="#" id="viewReviewsBtn" class="btn btn-sm btn-outline-warning" target="_blank">
                                        <i class="bi bi-star-fill"></i> Ver opiniones de este especialista
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fecha_cita" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="fecha_cita" 
                                       name="fecha_cita" required
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Horarios Disponibles *</label>
                                <div id="availableSlots" class="p-3 border rounded">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle"></i> Selecciona un especialista y fecha para ver los horarios disponibles
                                    </div>
                                </div>
                                <input type="hidden" id="hora_cita" name="hora_cita" required>
                                <small class="form-text text-muted mt-2 d-block">
                                    <i class="bi bi-clock"></i> Los horarios se muestran en bloques de 30 minutos
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                                    <i class="bi bi-arrow-left"></i> Anterior
                                </button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                    Siguiente <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Paso 3: Confirmar -->
                        <div class="booking-step" id="step3">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                Confirmar Reserva
                            </h5>
                            
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Resumen de tu Cita</h6>
                                    <div id="bookingSummary">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 mt-3">
                                <label for="notas" class="form-label">Notas (opcional)</label>
                                <textarea class="form-control" id="notas" name="notas" 
                                          rows="3" placeholder="Alguna solicitud especial..."></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                                    <i class="bi bi-arrow-left"></i> Anterior
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Confirmar Cita
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 100px;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Información Importante
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-clock text-primary"></i>
                            <strong>Puntualidad:</strong> Por favor llega <?= config('minutes_anticipation') ?> minutos antes
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-calendar-x text-primary"></i>
                            <strong>Cancelaciones:</strong> Con <?= config('cancel_time_limit') ?> horas de anticipación
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-credit-card text-primary"></i>
                            <strong>Pago:</strong> Se realiza en el establecimiento
                        </li>
                        <li>
                            <i class="bi bi-envelope text-primary"></i>
                            <strong>Confirmación:</strong> Recibirás un email de confirmación
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let selectedService = null;
let selectedSpecialist = null;
let selectedDate = null;
let selectedTime = null;

// Navegación entre pasos
function nextStep(step) {
    try {
        if (step === 2 && !validateStep1()) {
            alert('Por favor selecciona un servicio');
            return;
        }
        
        if (step === 3 && !validateStep2()) {
            alert('Por favor completa todos los campos');
            return;
        }
        
        document.getElementById('step' + currentStep).classList.remove('active');
        document.getElementById('step' + step).classList.add('active');
        currentStep = step;
        
        if (step === 3) {
            showBookingSummary();
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        console.error('Error:', error);
    }
}

function prevStep(step) {
    document.getElementById('step' + currentStep).classList.remove('active');
    document.getElementById('step' + step).classList.add('active');
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Validaciones
function validateStep1() {
    selectedService = document.querySelector('input[name="servicio_id"]:checked');
    return selectedService !== null;
}

function validateStep2() {
    selectedSpecialist = document.getElementById('especialista_id').value;
    selectedDate = document.getElementById('fecha_cita').value;
    selectedTime = document.getElementById('hora_cita').value;
    
    return selectedSpecialist && selectedDate && selectedTime;
}

// Cargar horarios disponibles
async function loadAvailableSlots() {
    const especialistaId = document.getElementById('especialista_id').value;
    const fechaCita = document.getElementById('fecha_cita').value;
    const servicioId = document.querySelector('input[name="servicio_id"]:checked')?.value;
    
    const slotsContainer = document.getElementById('availableSlots');
    
    if (!especialistaId || !fechaCita || !servicioId) {
        slotsContainer.innerHTML = '<div class="alert-off alert-info">Selecciona un especialista y fecha para ver los horarios disponibles</div>';
        return;
    }
    
    // Mostrar loading
    slotsContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando horarios...</p></div>';
    
    try {
        const response = await fetch(`<?= BASE_URL ?>/index.php?action=get-available-slots&especialista_id=${especialistaId}&fecha_cita=${fechaCita}&servicio_id=${servicioId}`);
        const data = await response.json();
        
        if (!data.success) {
            slotsContainer.innerHTML = `<div class="alert-off alert-danger">${data.error}</div>`;
            return;
        }
        
        if (data.slots.length === 0) {
            slotsContainer.innerHTML = '<div class="alert-off alert-warning"><i class="bi bi-exclamation-triangle"></i> No hay horarios disponibles para esta fecha. Por favor selecciona otra fecha.</div>';
            return;
        }
        
        // Mostrar horarios disponibles
        let slotsHTML = '<div class="row g-2">';
        data.slots.forEach(slot => {
            slotsHTML += `
                <div class="col-6 col-md-4 col-lg-3">
                    <button type="button" class="btn btn-outline-primary w-100 time-slot-btn" 
                            data-time="${slot.time}" onclick="selectTimeSlot('${slot.time}', this)">
                        ${slot.formatted}
                    </button>
                </div>
            `;
        });
        slotsHTML += '</div>';
        
        slotsContainer.innerHTML = slotsHTML;
        
    } catch (error) {
        console.error('Error:', error);
        slotsContainer.innerHTML = '<div class="alert-off alert-danger">Error al cargar los horarios. Por favor intenta nuevamente.</div>';
    }
}

// Seleccionar horario
function selectTimeSlot(time, button) {
    document.querySelectorAll('.time-slot-btn').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    
    button.classList.remove('btn-outline-primary');
    button.classList.add('btn-primary');
    
    const timeValue = time.includes(':') && time.split(':').length === 2 ? time + ':00' : time;
    
    document.getElementById('hora_cita').value = timeValue;
    selectedTime = time;
    
    console.log('Time selected:', timeValue);
}

// Mostrar resumen
function showBookingSummary() {
    const servicioRadio = document.querySelector('input[name="servicio_id"]:checked');
    const servicioLabel = servicioRadio ? document.querySelector(`label[for="${servicioRadio.id}"]`) : null;
    const especialistaSelect = document.getElementById('especialista_id');
    const fechaCita = document.getElementById('fecha_cita').value;
    const horaCita = document.getElementById('hora_cita').value;
    
    if (!servicioLabel || !especialistaSelect.value || !fechaCita || !horaCita) {
        document.getElementById('bookingSummary').innerHTML = '<div class="alert alert-warning">Datos incompletos</div>';
        return;
    }
    
    const servicioNombre = servicioLabel.querySelector('h6').textContent;
    const servicioPrecio = servicioLabel.querySelector('.text-primary').textContent;
    const servicioDuracion = servicioLabel.querySelector('.text-muted:last-child').textContent;
    const especialistaNombre = especialistaSelect.options[especialistaSelect.selectedIndex].text;
    
    // Formatear fecha
    const fechaObj = new Date(fechaCita + 'T00:00:00');
    const fechaFormateada = fechaObj.toLocaleDateString('es-CO', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    // Formatear hora
    let [hora, minuto] = horaCita.split(':');
    hora = parseInt(hora);
    const periodo = hora >= 12 ? 'PM' : 'AM';
    hora = hora % 12 || 12;
    const horaFormateada = `${hora}:${minuto} ${periodo}`;
    
    const summaryHTML = `
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <strong>Servicio:</strong>
                    <span>${servicioNombre}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <strong>Precio:</strong>
                    <span class="text-primary">${servicioPrecio}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <strong>Duración:</strong>
                    <span>${servicioDuracion}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <strong>Especialista:</strong>
                    <span>${especialistaNombre}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <strong>Fecha:</strong>
                    <span>${fechaFormateada}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <strong>Hora:</strong>
                    <span>${horaFormateada}</span>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('bookingSummary').innerHTML = summaryHTML;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('especialista_id').addEventListener('change', loadAvailableSlots);
    document.getElementById('fecha_cita').addEventListener('change', loadAvailableSlots);
    
    // Validar formulario antes de enviar
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        if (!validateStep1() || !validateStep2()) {
            e.preventDefault();
            alert('Por favor completa todos los campos requeridos');
            return false;
        }
    });

    // Si hay servicio pre-seleccionado, hacer scroll
    <?php if ($preselectedService): ?>
    const serviceRadio = document.querySelector('input[name="servicio_id"]:checked');
    if (serviceRadio) {
        serviceRadio.closest('.service-option').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
    }
    <?php endif; ?>
    
    // Si hay especialista pre-seleccionado, cargar datos
    <?php if ($preselectedSpecialist): ?>
    const especialistaSelect = document.getElementById('especialista_id');
    if (especialistaSelect.value) {
        const event = new Event('change');
        especialistaSelect.dispatchEvent(event);
    }
    <?php endif; ?>
});
</script>