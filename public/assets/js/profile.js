/**
 * PROFILE PAGE JAVASCRIPT
 * Maneja la funcionalidad de la página de perfil
 */

// ============================================
// FUNCIÓN TOAST NOTIFICATION (REUTILIZABLE)
// ============================================
function showNotification(message, type = 'info') {
    console.log('🔔 showNotification llamada:', message, type);
    
    // Usar la función global si existe (de cart-favorites-handler.js)
    if (typeof window.showNotification === 'function' && window.showNotification !== showNotification) {
        console.log('✅ Usando window.showNotification (global)');
        window.showNotification(message, type);
        return;
    }
    
    console.log('⚠️ Usando implementación standalone');
    
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
    console.log('🎯 profile.js: DOMContentLoaded iniciado');
    console.log('🔍 window.showNotification disponible:', typeof window.showNotification === 'function');
    
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
        
        // Enable inputs
        formInputs.forEach(input => {
            // Don't enable username and email (usually shouldn't be changed easily)
            if (input.name !== 'username' && input.name !== 'email') {
                input.disabled = false;
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
        
        console.log('📝 Formulario personal enviado');
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Validar que se hayan hecho cambios
        let hasChanges = false;
        for (let key in data) {
            if (originalValues[key] !== data[key]) {
                hasChanges = true;
                console.log(`✏️ Campo modificado: ${key}`, {
                    anterior: originalValues[key],
                    nuevo: data[key]
                });
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
        
        if (data.telefono && !/^[\d\s\+\-\(\)]+$/.test(data.telefono)) {
            showNotification('El teléfono solo puede contener números y símbolos válidos', 'error');
            return;
        }
        
        console.log('✅ Validaciones pasadas, enviando datos...');
        
        try {
            const response = await fetch('app/actions/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            console.log('📡 Respuesta recibida:', response.status);
            
            const result = await response.json();
            
            console.log('📦 Resultado:', result);
            
            if (result.success) {
                showNotification('Perfil actualizado correctamente', 'success');
                
                // Actualizar valores originales con los nuevos
                formInputs.forEach(input => {
                    originalValues[input.name] = input.value;
                });
                
                // Disable inputs again
                formInputs.forEach(input => {
                    input.disabled = true;
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
                
            } else {
                showNotification(result.message || 'No se pudo actualizar el perfil', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Ocurrió un error al actualizar el perfil', 'error');
        }
    });
    
    // ============================================
    // CHANGE PASSWORD
    // ============================================
    const formChangePassword = document.getElementById('form-change-password');
    
    formChangePassword.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('🔐 Formulario de contraseña enviado');
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Validar que todos los campos estén llenos
        if (!data.current_password || !data.new_password || !data.confirm_password) {
            showNotification('Todos los campos son obligatorios', 'error');
            return;
        }
        
        // Validate passwords match
        if (data.new_password !== data.confirm_password) {
            showNotification('Las contraseñas no coinciden', 'error');
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
        
        console.log('✅ Validaciones de contraseña pasadas');
        
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
                showNotification('Contraseña actualizada correctamente', 'success');
                
                // Reset form
                formChangePassword.reset();
                
            } else {
                showNotification(result.message || 'No se pudo cambiar la contraseña', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Ocurrió un error al cambiar la contraseña', 'error');
        }
    });
    
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
    
    if (btnAddAddress) {
        btnAddAddress.addEventListener('click', function() {
            showNotification('La gestión de direcciones estará disponible próximamente', 'info');
        });
    }
    
    if (btnAddFirstAddress) {
        btnAddFirstAddress.addEventListener('click', function() {
            showNotification('La gestión de direcciones estará disponible próximamente', 'info');
        });
    }
    
    // ============================================
    // CHANGE AVATAR
    // ============================================
    // La funcionalidad de avatar está manejada por avatar-upload.js
    
});
