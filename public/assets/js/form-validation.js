/**
 * Validaciones de Formularios - Fashion Store
 * Validación en tiempo real para todos los campos
 */

// ============================================
// VALIDACIÓN DE EMAIL
// ============================================
function validateEmail(email) {
    // Debe tener @ y un dominio válido
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateEmailField(input) {
    const email = input.value.trim();
    const errorElement = input.parentElement.nextElementSibling;
    
    if (email === '') {
        showFieldError(input, 'El email es obligatorio', errorElement);
        return false;
    }
    
    if (!validateEmail(email)) {
        showFieldError(input, 'Ingresa un email válido (ejemplo: usuario@gmail.com)', errorElement);
        return false;
    }
    
    showFieldSuccess(input, errorElement);
    return true;
}

// ============================================
// VALIDACIÓN DE TELÉFONO (9 dígitos, empieza con 9)
// ============================================
function validatePhone(phone) {
    // Debe tener exactamente 9 dígitos y empezar con 9
    const phoneRegex = /^9\d{8}$/;
    return phoneRegex.test(phone);
}

function validatePhoneField(input) {
    const phone = input.value.trim();
    const errorElement = input.parentElement.nextElementSibling;
    
    // El teléfono es opcional, si está vacío no validar
    if (phone === '') {
        input.classList.remove('is-invalid', 'is-valid');
        if (errorElement && errorElement.classList.contains('invalid-feedback')) {
            errorElement.style.display = 'none';
        }
        return true;
    }
    
    // Verificar que solo tenga números
    if (!/^\d+$/.test(phone)) {
        showFieldError(input, 'El teléfono solo debe contener números', errorElement);
        return false;
    }
    
    // Verificar que empiece con 9
    if (!phone.startsWith('9')) {
        showFieldError(input, 'El teléfono debe empezar con 9', errorElement);
        return false;
    }
    
    // Verificar que tenga 9 dígitos
    if (phone.length !== 9) {
        showFieldError(input, 'El teléfono debe tener exactamente 9 dígitos', errorElement);
        return false;
    }
    
    showFieldSuccess(input, errorElement);
    return true;
}

// ============================================
// VALIDACIÓN DE USERNAME
// ============================================
function validateUsername(username) {
    // Solo letras, números y guión bajo, 3-20 caracteres
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    return usernameRegex.test(username);
}

function validateUsernameField(input) {
    const username = input.value.trim();
    const errorElement = input.parentElement.nextElementSibling;
    
    if (username === '') {
        showFieldError(input, 'El nombre de usuario es obligatorio', errorElement);
        return false;
    }
    
    if (username.length < 3) {
        showFieldError(input, 'El nombre de usuario debe tener al menos 3 caracteres', errorElement);
        return false;
    }
    
    if (username.length > 20) {
        showFieldError(input, 'El nombre de usuario no puede tener más de 20 caracteres', errorElement);
        return false;
    }
    
    if (!validateUsername(username)) {
        showFieldError(input, 'Solo letras, números y guión bajo', errorElement);
        return false;
    }
    
    showFieldSuccess(input, errorElement);
    return true;
}

// ============================================
// VALIDACIÓN DE CONTRASEÑA
// ============================================
function validatePassword(password) {
    return password.length >= 6;
}

function validatePasswordField(input) {
    const password = input.value;
    const errorElement = input.parentElement.nextElementSibling;
    
    if (password === '') {
        showFieldError(input, 'La contraseña es obligatoria', errorElement);
        return false;
    }
    
    if (password.length < 6) {
        showFieldError(input, 'La contraseña debe tener al menos 6 caracteres', errorElement);
        return false;
    }
    
    showFieldSuccess(input, errorElement);
    return true;
}

// ============================================
// VALIDACIÓN DE NOMBRES (sin números)
// ============================================
function validateName(name) {
    // Solo letras y espacios
    const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    return nameRegex.test(name);
}

function validateNameField(input) {
    const name = input.value.trim();
    const errorElement = input.parentElement.nextElementSibling;
    
    if (name === '') {
        showFieldError(input, 'Este campo es obligatorio', errorElement);
        return false;
    }
    
    if (!validateName(name)) {
        showFieldError(input, 'Solo se permiten letras', errorElement);
        return false;
    }
    
    if (name.length < 2) {
        showFieldError(input, 'Debe tener al menos 2 caracteres', errorElement);
        return false;
    }
    
    showFieldSuccess(input, errorElement);
    return true;
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
function showFieldError(input, message, errorElement) {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    
    if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        input.parentElement.parentElement.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function showFieldSuccess(input, errorElement) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    
    if (errorElement && errorElement.classList.contains('invalid-feedback')) {
        errorElement.style.display = 'none';
    }
}

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Aplicar filtros de entrada en tiempo real (sin validar todavía)
    
    // TELÉFONO - Solo permitir números y limitar a 9 dígitos
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="telefono"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limitar a 9 dígitos
            if (this.value.length > 9) {
                this.value = this.value.slice(0, 9);
            }
        });
    });
    
    // USERNAME - Solo permitir letras, números y guión bajo
    const usernameInputs = document.querySelectorAll('input[name="username"]');
    usernameInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
        });
    });
    
    // NOMBRES - Solo permitir letras y espacios
    const nameInputs = document.querySelectorAll('input[name="nombre"], input[name="apellido"]');
    nameInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    });
    
    // Validar formularios antes de enviar
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validar emails
            const emailFields = this.querySelectorAll('input[type="email"]');
            emailFields.forEach(input => {
                if (!validateEmailField(input)) {
                    isValid = false;
                }
            });
            
            // Validar teléfonos
            const phoneFields = this.querySelectorAll('input[type="tel"], input[name="telefono"]');
            phoneFields.forEach(input => {
                if (!validatePhoneField(input)) {
                    isValid = false;
                }
            });
            
            // Validar usernames
            const usernameFields = this.querySelectorAll('input[name="username"]');
            usernameFields.forEach(input => {
                if (!validateUsernameField(input)) {
                    isValid = false;
                }
            });
            
            // Validar nombres
            const nameFields = this.querySelectorAll('input[name="nombre"], input[name="apellido"]');
            nameFields.forEach(input => {
                if (!validateNameField(input)) {
                    isValid = false;
                }
            });
            
            // Validar contraseñas
            const passwordFields = this.querySelectorAll('input[type="password"][name="password"]');
            passwordFields.forEach(input => {
                if (!validatePasswordField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Scroll al primer campo con error
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
        });
    });
});
