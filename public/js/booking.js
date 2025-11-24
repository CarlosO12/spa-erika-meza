/**
 * JavaScript para Sistema de Reservas
 * SPA Erika Meza
 */

let currentStep = 1;
let selectedService = null;
let selectedSpecialist = null;
let selectedDate = null;
let selectedTime = null;

document.addEventListener('DOMContentLoaded', function() {
    initBookingSystem();
});

function initBookingSystem() {
    // Listeners para cambios en servicios
    const serviceRadios = document.querySelectorAll('input[name="servicio_id"]');
    serviceRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            selectedService = {
                id: this.value,
                nombre: this.nextElementSibling.querySelector('h6').textContent,
                duracion: this.dataset.duration,
                precio: this.nextElementSibling.querySelector('.text-primary').textContent
            };
        });
    });
    
    // Listener para cambio de especialista
    const specialistSelect = document.getElementById('especialista_id');
    if (specialistSelect) {
        specialistSelect.addEventListener('change', function() {
            selectedSpecialist = {
                id: this.value,
                nombre: this.options[this.selectedIndex].text
            };
            loadAvailableSlots();
        });
    }
    
    // Listener para cambio de fecha
    const dateInput = document.getElementById('fecha_cita');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            selectedDate = this.value;
            loadAvailableSlots();
        });
    }
}

function nextStep(step) {
    // Validar paso actual
    if (!validateCurrentStep(currentStep)) {
        return;
    }
    
    // Ocultar paso actual
    document.getElementById('step' + currentStep).classList.remove('active');
    
    // Mostrar siguiente paso
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');
    
    // Si es el paso 3, mostrar resumen
    if (step === 3) {
        showBookingSummary();
    }
    
    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep(step) {
    document.getElementById('step' + currentStep).classList.remove('active');
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateCurrentStep(step) {
    switch(step) {
        case 1:
            if (!selectedService) {
                showToast('Por favor selecciona un servicio', 'warning');
                return false;
            }
            return true;
            
        case 2:
            if (!selectedSpecialist || !selectedSpecialist.id) {
                showToast('Por favor selecciona un especialista', 'warning');
                return false;
            }
            if (!selectedDate) {
                showToast('Por favor selecciona una fecha', 'warning');
                return false;
            }
            if (!selectedTime) {
                showToast('Por favor selecciona un horario', 'warning');
                return false;
            }
            return true;
            
        default:
            return true;
    }
}

async function loadAvailableSlots() {
    if (!selectedSpecialist || !selectedSpecialist.id || !selectedDate || !selectedService) {
        return;
    }
    
    const slotsContainer = document.getElementById('availableSlots');
    slotsContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Cargando horarios...</div>';
    
    try {
        const response = await fetch(
            `index.php?action=get-available-slots&specialist_id=${selectedSpecialist.id}&date=${selectedDate}&service_id=${selectedService.id}`
        );
        
        const data = await response.json();
        
        if (data.error) {
            slotsContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            return;
        }
        
        if (data.slots && data.slots.length > 0) {
            let slotsHTML = '<div class="d-flex flex-wrap gap-2">';
            data.slots.forEach(slot => {
                const timeFormatted = formatTime(slot);
                slotsHTML += `
                    <button type="button" class="time-slot" onclick="selectTimeSlot('${slot}', this)">
                        ${timeFormatted}
                    </button>
                `;
            });
            slotsHTML += '</div>';
            slotsContainer.innerHTML = slotsHTML;
        } else {
            slotsContainer.innerHTML = '<div class="alert alert-warning">No hay horarios disponibles para esta fecha</div>';
        }
    } catch (error) {
        console.error('Error cargando horarios:', error);
        slotsContainer.innerHTML = '<div class="alert alert-danger">Error al cargar los horarios</div>';
    }
}

function selectTimeSlot(time, button) {
    // Remover selección previa
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Seleccionar nuevo
    button.classList.add('selected');
    selectedTime = time;
    document.getElementById('hora_cita').value = time;
}

function showBookingSummary() {
    const summaryContainer = document.getElementById('bookingSummary');
    
    if (!selectedService || !selectedSpecialist || !selectedDate || !selectedTime) {
        summaryContainer.innerHTML = '<p class="text-danger">Faltan datos por completar</p>';
        return;
    }
    
    const dateFormatted = formatDate(selectedDate);
    const timeFormatted = formatTime(selectedTime);
    
    summaryContainer.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <p class="mb-1"><strong>Servicio:</strong></p>
                <p class="text-muted">${selectedService.nombre}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Precio:</strong></p>
                <p class="text-muted">${selectedService.precio}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Especialista:</strong></p>
                <p class="text-muted">${selectedSpecialist.nombre}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Duración:</strong></p>
                <p class="text-muted">${selectedService.duracion} minutos</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Fecha:</strong></p>
                <p class="text-muted">${dateFormatted}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Hora:</strong></p>
                <p class="text-muted">${timeFormatted}</p>
            </div>
        </div>
    `;
}

function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-CO', options);
}

function showToast(message, type = 'info') {
    if (typeof window.SpaUtils !== 'undefined' && window.SpaUtils.showToast) {
        window.SpaUtils.showToast(message, type);
    } else {
        alert(message);
    }
}