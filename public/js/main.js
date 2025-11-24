/**
 * JavaScript Principal
 * SPA Erika Meza
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializar tooltips de Bootstrap
    initTooltips();
    // Inicializar confirmaciones
    initConfirmations();
    // Validación de formularios
    initFormValidation();
    // Animaciones de entrada
    initAnimations();
});

/**
 * Inicializar tooltips de Bootstrap
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Inicializar confirmaciones para acciones críticas
 */
function initConfirmations() {
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Estás seguro?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validación de formularios en tiempo real
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Animaciones de entrada para elementos
 */
function initAnimations() {
    const animatedElements = document.querySelectorAll('.fade-in, .slide-up');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        if (el.classList.contains('slide-up')) {
            el.style.transform = 'translateY(30px)';
        }
        observer.observe(el);
    });
}

/**
 * Formatear precio en pesos colombianos
 */
function formatPrice(precio) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(precio);
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-CO', options);
}

/**
 * Formatear hora
 */
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
}

/**
 * Mostrar loading spinner
 */
function showLoading(element) {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border spinner-border-sm me-2';
    spinner.setAttribute('role', 'status');
    element.prepend(spinner);
    element.disabled = true;
}

/**
 * Ocultar loading spinner
 */
function hideLoading(element) {
    const spinner = element.querySelector('.spinner-border');
    if (spinner) {
        spinner.remove();
    }
    element.disabled = false;
}

/**
 * Realizar petición AJAX
 */
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Error en la petición:', error);
        throw error;
    }
}

/**
 * Mostrar notificación toast
 */
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const colors = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    };
    
    const toastHTML = `
        <div class="toast align-items-center text-white ${colors[type] || colors.info} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHTML;
    document.getElementById('toastContainer').appendChild(toastElement.firstElementChild);
    
    const toast = new bootstrap.Toast(toastElement.firstElementChild, {
        delay: 3000
    });
    toast.show();
    
    // Remover el toast después de que se oculte
    toastElement.firstElementChild.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

/**
 * Validar email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validar teléfono
 */
function validatePhone(telefono) {
    const re = /^3[0-9]{9}$/;
    return re.test(phone.replace(/\s/g, ''));
}

/**
 * Debounce para optimizar búsquedas
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Copiar texto al portapapeles
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copiado al portapapeles', 'success');
    }).catch(err => {
        console.error('Error al copiar:', err);
        showToast('Error al copiar', 'error');
    });
}

/**
 * Scroll suave a un elemento
 */
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

/**
 * Obtener parámetros de la URL
 */
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Validar formulario antes de enviar
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

/**
 * Limpiar formulario
 */
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        const inputs = form.querySelectorAll('.is-invalid, .is-valid');
        inputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });
    }
}

/**
 * Toggle de visibilidad de contraseña
 */
function setupPasswordToggles() {
    const toggleButtons = document.querySelectorAll('[data-toggle-password]');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
}

// Inicializar toggles de contraseña
setupPasswordToggles();

/**
 * Prevenir envío múltiple de formularios
 */
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            showLoading(submitButton);
        }
    });
});

/**
 * Auto-resize para textareas
 */
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
    textarea.addEventListener('input', function() {
        autoResizeTextarea(this);
    });
    // Inicializar tamaño
    autoResizeTextarea(textarea);
});

/**
 * Búsqueda en tiempo real
 */
function setupLiveSearch(inputId, resultsId, searchFunction) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    
    if (!input || !results) return;
    
    const debouncedSearch = debounce(async (query) => {
        if (query.length < 2) {
            results.innerHTML = '';
            return;
        }
        
        results.innerHTML = '<div class="p-3 text-center"><div class="spinner-border spinner-border-sm"></div></div>';
        
        try {
            const data = await searchFunction(query);
            displaySearchResults(results, data);
        } catch (error) {
            results.innerHTML = '<div class="p-3 text-danger">Error en la búsqueda</div>';
        }
    }, 300);
    
    input.addEventListener('input', (e) => {
        debouncedSearch(e.target.value);
    });
}

/**
 * Mostrar resultados de búsqueda
 */
function displaySearchResults(container, results) {
    if (results.length === 0) {
        container.innerHTML = '<div class="p-3 text-muted">No se encontraron resultados</div>';
        return;
    }
    
    let html = '<div class="list-group">';
    results.forEach(result => {
        html += `
            <a href="${result.url}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${result.title}</h6>
                    <small>${result.price || ''}</small>
                </div>
                <p class="mb-1 text-muted small">${result.description || ''}</p>
            </a>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

/**
 * Confirmar antes de salir con cambios sin guardar
 */
function setupUnsavedChangesWarning(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    let hasChanges = false;
    
    form.addEventListener('input', () => {
        hasChanges = true;
    });
    
    form.addEventListener('submit', () => {
        hasChanges = false;
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
}

/**
 * Previsualización de imagen antes de subir
 */
function setupImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (!input || !preview) return;
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validar tipo de archivo
            if (!file.type.startsWith('image/')) {
                showToast('Por favor selecciona una imagen válida', 'error');
                return;
            }
            
            // Validar tamaño (5MB máximo)
            if (file.size > 5 * 1024 * 1024) {
                showToast('La imagen no debe superar 5MB', 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

/**
 * Contador de caracteres para textarea
 */
function setupCharCounter(textareaId, counterId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    
    if (!textarea || !counter) return;
    
    textarea.setAttribute('maxlength', maxLength);
    
    function updateCounter() {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${remaining} caracteres restantes`;
        
        if (remaining < 20) {
            counter.classList.add('text-warning');
        } else {
            counter.classList.remove('text-warning');
        }
    }
    
    textarea.addEventListener('input', updateCounter);
    updateCounter();
}

/**
 * Exportar datos a CSV
 */
function exportToCSV(data, filename) {
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, filename);
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }
}

/**
 * Convertir array de objetos a CSV
 */
function convertToCSV(data) {
    if (!data || data.length === 0) return '';
    
    const headers = Object.keys(data[0]);
    const csvRows = [];
    
    csvRows.push(headers.join(','));
    
    data.forEach(row => {
        const values = headers.map(header => {
            const value = row[header];
            return typeof value === 'string' ? `"${value}"` : value;
        });
        csvRows.push(values.join(','));
    });
    
    return csvRows.join('\n');
}

/**
 * Imprimir contenido de un elemento
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Imprimir</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

/**
 * Cargar más elementos (paginación infinita)
 */
function setupInfiniteScroll(loadMoreFunction) {
    let loading = false;
    let page = 1;
    
    window.addEventListener('scroll', () => {
        if (loading) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;
        
        if (scrollPosition >= pageHeight - 100) {
            loading = true;
            page++;
            
            loadMoreFunction(page).then(() => {
                loading = false;
            }).catch(() => {
                loading = false;
            });
        }
    });
}

function nextStep(step) {
    // Oculta todos los pasos
    document.querySelectorAll('.booking-step').forEach(stepDiv => {
        stepDiv.classList.remove('active');
    });

    // Muestra el paso indicado
    const next = document.getElementById('step' + step);
    if (next) {
        next.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Si pasas del paso 1 al 2, podrías llenar el resumen o hacer lógica adicional aquí
    if (step === 3) {
        updateBookingSummary();
    }
}

function prevStep(step) {
    // Igual que nextStep pero al retroceder
    document.querySelectorAll('.booking-step').forEach(stepDiv => {
        stepDiv.classList.remove('active');
    });

    const prev = document.getElementById('step' + step);
    if (prev) {
        prev.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// (Opcional) Actualiza el resumen en el paso 3
function updateBookingSummary() {
    const selectedService = document.querySelector('input[name="servicio_id"]:checked');
    const specialist = document.getElementById('especialista_id');
    const date = document.getElementById('fecha_cita');
    const hour = document.getElementById('hora_cita');

    const summaryDiv = document.getElementById('bookingSummary');
    if (!summaryDiv) return;

    summaryDiv.innerHTML = `
        <p><strong>Servicio:</strong> ${selectedService ? selectedService.nextElementSibling.querySelector('h6').textContent : 'No seleccionado'}</p>
        <p><strong>Especialista:</strong> ${specialist?.selectedOptions[0]?.text || 'No seleccionado'}</p>
        <p><strong>Fecha:</strong> ${date?.value || 'No seleccionada'}</p>
        <p><strong>Hora:</strong> ${hour?.value || 'No seleccionada'}</p>
    `;
}

// Exportar funciones para uso global
window.SpaUtils = {
    formatPrice,
    formatDate,
    formatTime,
    showLoading,
    hideLoading,
    ajaxRequest,
    showToast,
    validateEmail,
    validatePhone,
    debounce,
    copyToClipboard,
    scrollToElement,
    getUrlParameter,
    validateForm,
    resetForm,
    setupLiveSearch, 
    setupImagePreview,
    setupCharCounter,
    exportToCSV,
    printElement,
    setupInfiniteScroll,
    setupUnsavedChangesWarning,
    nextStep,
    prevStep,
    updateBookingSummary
};