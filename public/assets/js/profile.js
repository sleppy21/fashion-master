/**
 * PROFILE PAGE JAVASCRIPT
 * Maneja la funcionalidad de la página de perfil
 */

// ============================================
// FUNCIÓN TOAST NOTIFICATION (REUTILIZABLE)
// ============================================
function showNotification(message, type = 'info') {
    
    // Usar la función global si existe (de cart-favorites-handler.js)
    if (typeof window.showNotification === 'function' && window.showNotification !== showNotification) {
        window.showNotification(message, type);
        return;
    }
    
    
    // Fallback: Implementación standalone
    const existingNotif = document.querySelector('.modern-toast');
    if (existingNotif) {
        existingNotif.remove();
    }

    const isDarkMode = document.body.classList.contains('dark-mode');

    const icons = {
        success: '<i class="fa fa-check-circle"></i>',
        error: '<i class="fa fa-times-circle"></i>',
        info: '<i class="fa fa-info-circle"></i>',
        warning: '<i class="fa fa-exclamation-circle"></i>'
    };

    const colors = {
        success: { bg: '#10b981', shadow: 'rgba(16, 185, 129, 0.4)' },
        error: { bg: '#ef4444', shadow: 'rgba(239, 68, 68, 0.4)' },
        info: { bg: '#3b82f6', shadow: 'rgba(59, 130, 246, 0.4)' },
        warning: { bg: '#f59e0b', shadow: 'rgba(245, 158, 11, 0.4)' }
    };

    const color = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.className = `modern-toast ${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${icons[type]}</div>
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fa fa-times"></i>
        </button>
    `;

    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        min-width: 300px;
        max-width: 400px;
        background: ${isDarkMode ? '#1f2937' : 'white'};
        border-radius: 12px;
        box-shadow: ${isDarkMode 
            ? '0 10px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1)' 
            : '0 10px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05)'
        };
        z-index: 999999;
        animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        overflow: hidden;
    `;

    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        background: ${color.bg};
        width: 100%;
        animation: progressBar 3s linear;
        border-radius: 0 0 12px 12px;
    `;
    toast.appendChild(progressBar);

    document.body.appendChild(toast);

    // Agregar estilos dinámicos si no existen
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes slideInUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes progressBar {
                from { width: 100%; }
                to { width: 0%; }
            }
            .modern-toast .toast-icon {
                font-size: 24px;
                flex-shrink: 0;
            }
            .modern-toast.success .toast-icon { color: #10b981; }
            .modern-toast.error .toast-icon { color: #ef4444; }
            .modern-toast.info .toast-icon { color: #3b82f6; }
            .modern-toast.warning .toast-icon { color: #f59e0b; }
            .modern-toast .toast-content {
                flex: 1;
            }
            .modern-toast .toast-message {
                font-size: 14px;
                font-weight: 500;
                color: #111;
            }
            body.dark-mode .modern-toast .toast-message {
                color: #f3f4f6;
            }
            .modern-toast .toast-close {
                background: none;
                border: none;
                color: #999;
                cursor: pointer;
                padding: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color 0.2s;
            }
            .modern-toast .toast-close:hover {
                color: #333;
            }
            body.dark-mode .modern-toast .toast-close:hover {
                color: #fff;
            }
        `;
        document.head.appendChild(style);
    }

    setTimeout(() => {
        if (toast && toast.parentElement) {
            toast.style.animation = 'slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) reverse';
            setTimeout(() => toast.remove(), 400);
        }
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // TABS FUNCTIONALITY
    // ============================================
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // ============================================
    // REAL-TIME VALIDATION
    // ============================================
    function addRealTimeValidation(input) {
        input.addEventListener('input', function() {
            validateField(this);
        });
        
        input.addEventListener('blur', function() {
            validateField(this);
        });
    }
    
    function validateField(input) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Remove previous error
        removeFieldError(input);
        
        // Validate based on field type
        switch(input.name) {
            case 'nombre':
            case 'apellido':
                if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Debe tener al menos 2 caracteres';
                } else if (value.length > 50) {
                    isValid = false;
                    errorMessage = 'Máximo 50 caracteres';
                } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Solo se permiten letras';
                }
                break;
                
            case 'telefono':
                if (value && !/^[\d\s\+\-\(\)]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Solo números y símbolos: + - ( )';
                } else if (value && (value.length < 7 || value.length > 15)) {
                    isValid = false;
                    errorMessage = 'Debe tener entre 7 y 15 caracteres';
                }
                break;
                
            case 'fecha_nacimiento':
                if (value) {
                    const birthDate = new Date(value);
                    const today = new Date();
                    const age = today.getFullYear() - birthDate.getFullYear();
                    
                    if (age < 18) {
                        isValid = false;
                        errorMessage = 'Debes ser mayor de 18 años';
                    } else if (age > 120) {
                        isValid = false;
                        errorMessage = 'Fecha inválida';
                    }
                }
                break;
        }
        
        // Show error if invalid
        if (!isValid) {
            showFieldError(input, errorMessage);
        }
        
        return isValid;
    }
    
    function showFieldError(input, message) {
        input.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        
        input.parentElement.appendChild(errorDiv);
    }
    
    function removeFieldError(input) {
        input.classList.remove('is-invalid');
        
        const existingError = input.parentElement.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    }
    
    // ============================================
    // EDIT PERSONAL INFO
    // ============================================
    const btnEditPersonal = document.getElementById('btn-edit-personal');
    const btnCancelPersonal = document.getElementById('btn-cancel-personal');
    const formPersonalInfo = document.getElementById('form-personal-info');
    const formInputs = formPersonalInfo.querySelectorAll('input, select');
    const formActions = formPersonalInfo.querySelector('.form-actions');
    
    // Original values backup
    let originalValues = {};
    
    btnEditPersonal.addEventListener('click', function() {
        // Backup original values
        formInputs.forEach(input => {
            originalValues[input.name] = input.value;
        });
        
        // Enable inputs (except username and email which are readonly)
        formInputs.forEach(input => {
            if (input.name !== 'username' && input.name !== 'email') {
                input.disabled = false;
                // Add real-time validation
                addRealTimeValidation(input);
            }
        });
        
        // Show form actions
        formActions.style.display = 'flex';
        btnEditPersonal.style.display = 'none';
    });
    
    btnCancelPersonal.addEventListener('click', function() {
        // Restore original values
        formInputs.forEach(input => {
            input.value = originalValues[input.name];
            input.disabled = true;
        });
        
        // Hide form actions
        formActions.style.display = 'none';
        btnEditPersonal.style.display = 'flex';
    });
    
    formPersonalInfo.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validar todos los campos antes de enviar
        let allValid = true;
        formInputs.forEach(input => {
            if (!input.disabled && input.name !== 'username' && input.name !== 'email') {
                if (!validateField(input)) {
                    allValid = false;
                }
            }
        });
        
        if (!allValid) {
            showNotification('Por favor corrige los errores en el formulario', 'error');
            return;
        }
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Validar que se hayan hecho cambios
        let hasChanges = false;
        for (let key in data) {
            if (originalValues[key] !== data[key]) {
                hasChanges = true;
                break;
            }
        }
        
        if (!hasChanges) {
            showNotification('No se detectaron cambios para guardar', 'warning');
            return;
        }
        
        // Validar campos requeridos
        if (!data.nombre || !data.apellido) {
            showNotification('Nombre y apellido son obligatorios', 'error');
            return;
        }
        
        // Mostrar indicador de carga
        const submitBtn = formActions.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
        
        try {
            const response = await fetch('app/actions/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('✅ Perfil actualizado correctamente', 'success');
                
                // Actualizar valores originales con los nuevos
                formInputs.forEach(input => {
                    originalValues[input.name] = input.value;
                });
                
                // Disable inputs again
                formInputs.forEach(input => {
                    input.disabled = true;
                    removeFieldError(input);
                });
                
                // Hide form actions
                formActions.style.display = 'none';
                btnEditPersonal.style.display = 'flex';
                
                // Update sidebar name if changed
                if (data.nombre || data.apellido) {
                    const profileName = document.querySelector('.profile-name');
                    if (profileName) {
                        profileName.textContent = `${data.nombre} ${data.apellido}`;
                    }
                }
                
                // Actualizar nombre en el header si existe
                const headerUserName = document.querySelector('.header-user-name');
                if (headerUserName) {
                    headerUserName.textContent = `${data.nombre} ${data.apellido}`;
                }
                
            } else {
                showNotification(result.message || 'No se pudo actualizar el perfil', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('❌ Ocurrió un error al actualizar el perfil', 'error');
        } finally {
            // Restaurar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
    
    // ============================================
    // CHANGE PASSWORD
    // ============================================
    const formChangePassword = document.getElementById('form-change-password');
    
    // Agregar indicador de fortaleza de contraseña
    const newPasswordInput = formChangePassword.querySelector('[name="new_password"]');
    if (newPasswordInput) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength-indicator';
        strengthIndicator.style.cssText = `
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            display: none;
        `;
        
        const strengthBar = document.createElement('div');
        strengthBar.className = 'password-strength-bar';
        strengthBar.style.cssText = `
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 2px;
        `;
        
        strengthIndicator.appendChild(strengthBar);
        newPasswordInput.parentElement.appendChild(strengthIndicator);
        
        const strengthText = document.createElement('small');
        strengthText.className = 'password-strength-text';
        strengthText.style.cssText = 'display: block; margin-top: 4px; font-size: 12px;';
        newPasswordInput.parentElement.appendChild(strengthText);
        
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            if (password.length === 0) {
                strengthIndicator.style.display = 'none';
                strengthText.textContent = '';
                return;
            }
            
            strengthIndicator.style.display = 'block';
            strengthBar.style.width = strength.percentage + '%';
            strengthBar.style.background = strength.color;
            strengthText.textContent = strength.text;
            strengthText.style.color = strength.color;
        });
    }
    
    formChangePassword.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Validar que todos los campos estén llenos
        if (!data.current_password || !data.new_password || !data.confirm_password) {
            showNotification('Todos los campos son obligatorios', 'error');
            return;
        }
        
        // Validate passwords match
        if (data.new_password !== data.confirm_password) {
            showNotification('Las contraseñas nuevas no coinciden', 'error');
            return;
        }
        
        // Validate password length
        if (data.new_password.length < 6) {
            showNotification('La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }
        
        // Validar que la nueva contraseña sea diferente a la actual
        if (data.current_password === data.new_password) {
            showNotification('La nueva contraseña debe ser diferente a la actual', 'warning');
            return;
        }
        
        // Mostrar indicador de carga
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cambiando...';
        
        try {
            const response = await fetch('app/actions/change_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('✅ Contraseña actualizada correctamente', 'success');
                
                // Reset form
                formChangePassword.reset();
                
                // Ocultar indicador de fortaleza
                const strengthIndicator = formChangePassword.querySelector('.password-strength-indicator');
                const strengthText = formChangePassword.querySelector('.password-strength-text');
                if (strengthIndicator) strengthIndicator.style.display = 'none';
                if (strengthText) strengthText.textContent = '';
                
            } else {
                showNotification(result.message || 'No se pudo cambiar la contraseña', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('❌ Ocurrió un error al cambiar la contraseña', 'error');
        } finally {
            // Restaurar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
    
    // ============================================
    // PASSWORD STRENGTH CALCULATOR
    // ============================================
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 6) strength += 20;
        if (password.length >= 8) strength += 10;
        if (password.length >= 10) strength += 10;
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
        
        let text = '';
        let color = '';
        
        if (strength <= 30) {
            text = 'Muy débil';
            color = '#e74c3c';
        } else if (strength <= 50) {
            text = 'Débil';
            color = '#f39c12';
        } else if (strength <= 70) {
            text = 'Regular';
            color = '#f1c40f';
        } else if (strength <= 85) {
            text = 'Buena';
            color = '#3498db';
        } else {
            text = 'Muy fuerte';
            color = '#2ecc71';
        }
        
        return {
            percentage: strength,
            text: text,
            color: color
        };
    }
    
    // ============================================
    // TOGGLE PASSWORD VISIBILITY
    // ============================================
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // ============================================
    // ADD ADDRESS BUTTONS
    // ============================================
    const btnAddAddress = document.getElementById('btn-add-address');
    const btnAddFirstAddress = document.getElementById('btn-add-first-address');
    
    // Los botones de agregar dirección están deshabilitados porque se usa el modal
    // La funcionalidad está en address-manager.js
    
    // ============================================
    // CHANGE AVATAR
    // ============================================
    // La funcionalidad de avatar está manejada por avatar-upload.js
    
    // ============================================
    // TOGGLE FAVORITES MODAL
    // ============================================
    const toggleFavoritesBtn = document.getElementById('toggle-favorites-btn');
    
    if (toggleFavoritesBtn) {
        toggleFavoritesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const favoritesModal = document.getElementById('favorites-modal');
            
            if (!favoritesModal) {
                console.warn('Modal de favoritos no encontrado');
                return;
            }
            
            // Toggle del modal
            if (favoritesModal.style.display === 'block') {
                // Cerrar modal
                favoritesModal.style.display = 'none';
                document.body.style.overflow = ''; // Restaurar scroll
            } else {
                // Abrir modal
                favoritesModal.style.display = 'block';
                document.body.style.overflow = 'hidden'; // Prevenir scroll del body
                
                // Cargar favoritos si existe la función
                if (typeof window.loadFavorites === 'function') {
                    window.loadFavorites();
                }
            }
        });
    }
    
    // Cerrar modal al hacer click en el overlay o botón cerrar
    const favoritesModal = document.getElementById('favorites-modal');
    
    if (favoritesModal) {
        // Click en el overlay (fuera del contenido)
        favoritesModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
        
        // Click en botón cerrar
        const closeBtn = favoritesModal.querySelector('.favorites-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                favoritesModal.style.display = 'none';
                document.body.style.overflow = '';
            });
        }
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && favoritesModal.style.display === 'block') {
                favoritesModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
    
});
