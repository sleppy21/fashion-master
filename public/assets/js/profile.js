/**
 * PROFILE PAGE JAVASCRIPT
 * Maneja la funcionalidad de la página de perfil
 */

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
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
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
                Swal.fire({
                    icon: 'success',
                    title: '¡Perfil Actualizado!',
                    text: 'Tus datos han sido actualizados correctamente',
                    confirmButtonColor: '#667eea',
                    timer: 2000
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'No se pudo actualizar el perfil',
                    confirmButtonColor: '#e74c3c'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al actualizar el perfil',
                confirmButtonColor: '#e74c3c'
            });
        }
    });
    
    // ============================================
    // CHANGE PASSWORD
    // ============================================
    const formChangePassword = document.getElementById('form-change-password');
    
    formChangePassword.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Validate passwords match
        if (data.new_password !== data.confirm_password) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden',
                confirmButtonColor: '#e74c3c'
            });
            return;
        }
        
        // Validate password length
        if (data.new_password.length < 6) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La contraseña debe tener al menos 6 caracteres',
                confirmButtonColor: '#e74c3c'
            });
            return;
        }
        
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
                Swal.fire({
                    icon: 'success',
                    title: '¡Contraseña Cambiada!',
                    text: 'Tu contraseña ha sido actualizada correctamente',
                    confirmButtonColor: '#667eea',
                    timer: 2000
                });
                
                // Reset form
                formChangePassword.reset();
                
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'No se pudo cambiar la contraseña',
                    confirmButtonColor: '#e74c3c'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al cambiar la contraseña',
                confirmButtonColor: '#e74c3c'
            });
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
            Swal.fire({
                icon: 'info',
                title: 'Función en Desarrollo',
                text: 'La gestión de direcciones estará disponible próximamente',
                confirmButtonColor: '#667eea'
            });
        });
    }
    
    if (btnAddFirstAddress) {
        btnAddFirstAddress.addEventListener('click', function() {
            Swal.fire({
                icon: 'info',
                title: 'Función en Desarrollo',
                text: 'La gestión de direcciones estará disponible próximamente',
                confirmButtonColor: '#667eea'
            });
        });
    }
    
    // ============================================
    // CHANGE AVATAR
    // ============================================
    const btnChangeAvatar = document.querySelector('.btn-change-avatar');
    
    if (btnChangeAvatar) {
        btnChangeAvatar.addEventListener('click', function() {
            Swal.fire({
                icon: 'info',
                title: 'Función en Desarrollo',
                text: 'La función de cambiar avatar estará disponible próximamente',
                confirmButtonColor: '#667eea'
            });
        });
    }
    
});
