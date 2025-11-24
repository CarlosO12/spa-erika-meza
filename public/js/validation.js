/**
 * JavaScript para Validación de Formularios
 * SPA Erika Meza
 */

// Configuración de validaciones
const validationRules = {
    email: {
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        message: 'Email inválido'
    },
    phone: {
        pattern: /^3[0-9]{9}$/,
        message: 'Teléfono inválido. Debe ser un número colombiano válido (10 dígitos)'
    },
    password: {
        minLength: 8,
        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
        message: 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números'
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
});

function initFormValidation() {
    // Validación en tiempo real
    setupRealtimeValidation();
    
    // Validación al enviar
    setupFormSubmitValidation();
    
    // Validación de contraseñas coincidentes
    setupPasswordMatchValidation();
    
    // Validación de teléfono
    setupPhoneValidation();
}

function setupRealtimeValidation() {
    // Email
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });
    
    // Teléfono
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="telefono"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validatePhone(this);
        });
    });
    
    // Contraseña
    const passwordInputs = document.querySelectorAll('input[type="password"][name="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validatePassword(this);
        });
    });
}

function setupFormSubmitValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                e.stopPropagation();
                
                // Scroll al primer error
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
}

function setupPasswordMatchValidation() {
    const confirmInputs = document.querySelectorAll('input[name="password_confirm"], input[name="confirm_password"]');
    
    confirmInputs.forEach(confirmInput => {
        confirmInput.addEventListener('input', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput) {
                validatePasswordMatch(passwordInput, this);
            }
        });
    });
}

function setupPhoneValidation() {
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="telefono"]');
    
    phoneInputs.forEach(input => {
        // Solo permitir números
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limitar a 10 dígitos
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    
    // Validar campos requeridos
    const requiredInputs = form.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            setInvalid(input, 'Este campo es requerido');
            isValid = false;
        } else {
            setValid(input);
        }
    });
    
    // Validar emails
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !validateEmail(input)) {
            isValid = false;
        }
    });
    
    // Validar teléfonos
    const phoneInputs = form.querySelectorAll('input[type="tel"], input[name="telefono"]');
    phoneInputs.forEach(input => {
        if (input.value && !validatePhone(input)) {
            isValid = false;
        }
    });
    
    // Validar contraseñas
    const passwordInputs = form.querySelectorAll('input[type="password"][name="password"]');
    passwordInputs.forEach(input => {
        if (input.value && !validatePassword(input)) {
            isValid = false;
        }
    });
    
    // Validar coincidencia de contraseñas
    const confirmInput = form.querySelector('input[name="password_confirm"], input[name="confirm_password"]');
    const passwordInput = form.querySelector('input[name="password"]');
    if (confirmInput && passwordInput && confirmInput.value) {
        if (!validatePasswordMatch(passwordInput, confirmInput)) {
            isValid = false;
        }
    }
    
    return isValid;
}

function validateEmail(input) {
    const value = input.value.trim();
    
    if (!value) {
        return true; // Si está vacío, lo maneja la validación de requerido
    }
    
    if (!validationRules.email.pattern.test(value)) {
        setInvalid(input, validationRules.email.message);
        return false;
    }
    
    setValid(input);
    return true;
}

function validatePhone(input) {
    const value = input.value.trim().replace(/\s/g, '');
    
    if (!value) {
        return true;
    }
    
    if (!validationRules.phone.pattern.test(value)) {
        setInvalid(input, validationRules.phone.message);
        return false;
    }
    
    setValid(input);
    return true;
}

function validatePassword(input) {
    const value = input.value;
    
    if (!value) {
        return true;
    }
    
    if (value.length < validationRules.password.minLength) {
        setInvalid(input, `Mínimo ${validationRules.password.minLength} caracteres`);
        return false;
    }
    
    if (!validationRules.password.pattern.test(value)) {
        setInvalid(input, validationRules.password.message);
        return false;
    }
    
    setValid(input);
    return true;
}

function validatePasswordMatch(passwordInput, confirmInput) {
    if (passwordInput.value !== confirmInput.value) {
        setInvalid(confirmInput, 'Las contraseñas no coinciden');
        return false;
    }
    
    setValid(confirmInput);
    return true;
}

function setInvalid(input, message) {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    
    // Buscar o crear feedback
    let feedback = input.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.appendChild(feedback);
    }
    
    feedback.textContent = message;
    feedback.style.display = 'block';
}

function setValid(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    
    // Ocultar feedback
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.style.display = 'none';
    }
}

function clearValidation(input) {
    input.classList.remove('is-valid', 'is-invalid');
    
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.style.display = 'none';
    }
}

// Validaciones adicionales útiles
function validateDate(input) {
    const value = input.value;
    const date = new Date(value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (date < today) {
        setInvalid(input, 'La fecha debe ser futura');
        return false;
    }
    
    setValid(input);
    return true;
}

function validateMinLength(input, minLength) {
    if (input.value.length < minLength) {
        setInvalid(input, `Mínimo ${minLength} caracteres`);
        return false;
    }
    
    setValid(input);
    return true;
}

function validateMaxLength(input, maxLength) {
    if (input.value.length > maxLength) {
        setInvalid(input, `Máximo ${maxLength} caracteres`);
        return false;
    }
    
    setValid(input);
    return true;
}

function validateNumber(input, min = null, max = null) {
    const value = parseFloat(input.value);
    
    if (isNaN(value)) {
        setInvalid(input, 'Debe ser un número válido');
        return false;
    }
    
    if (min !== null && value < min) {
        setInvalid(input, `El valor mínimo es ${min}`);
        return false;
    }
    
    if (max !== null && value > max) {
        setInvalid(input, `El valor máximo es ${max}`);
        return false;
    }
    
    setValid(input);
    return true;
}

// Exportar funciones para uso global
window.FormValidation = {
    validateForm,
    validateEmail,
    validatePhone,
    validatePassword,
    validatePasswordMatch,
    validateDate,
    validateMinLength,
    validateMaxLength,
    validateNumber,
    setInvalid,
    setValid,
    clearValidation
};