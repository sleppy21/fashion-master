/* ============================================
   AVATAR UPLOAD & CROP FUNCTIONALITY
   ============================================ */

let croppieInstance = null;

$(document).ready(function() {
    // Click en el avatar para seleccionar imagen
    $('#avatar-upload-area').on('click', function(e) {
        e.preventDefault();
        $('#avatar-file-input').click();
    });

    // Cuando se selecciona un archivo
    $('#avatar-file-input').on('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) return;
        
        // Validar tipo de archivo
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Tipo de archivo no válido',
                text: 'Por favor selecciona una imagen (JPG, PNG o GIF)',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo muy grande',
                text: 'La imagen debe pesar menos de 5MB',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        // Leer la imagen y mostrar el modal
        const reader = new FileReader();
        reader.onload = function(event) {
            showCropModal(event.target.result);
        };
        reader.readAsDataURL(file);
    });

    // Cerrar modal
    $('#avatar-crop-modal').on('click', '.avatar-crop-close, .btn-cancel', function() {
        closeCropModal();
    });

    // Click en overlay para cerrar
    $('#avatar-crop-modal').on('click', '.avatar-crop-overlay', function() {
        closeCropModal();
    });

    // Evitar que el click en el contenido cierre el modal
    $('#avatar-crop-modal').on('click', '.avatar-crop-content', function(e) {
        e.stopPropagation();
    });

    // Subir imagen recortada
    $('#avatar-crop-modal').on('click', '.btn-upload', function() {
        uploadCroppedImage();
    });
});

function showCropModal(imageData) {
    const modal = $('#avatar-crop-modal');
    
    // Mostrar modal
    modal.removeClass('hidden');
    
    // Esperar a que el modal esté visible antes de inicializar Croppie
    setTimeout(() => {
        // Destruir instancia anterior si existe
        if (croppieInstance) {
            croppieInstance.destroy();
        }
        
        // Inicializar Croppie
        const cropElement = document.getElementById('crop-image');
        croppieInstance = new Croppie(cropElement, {
            viewport: {
                width: 250,
                height: 250,
                type: 'circle'
            },
            boundary: {
                width: 300,
                height: 300
            },
            showZoomer: true,
            enableOrientation: true,
            enableResize: false,
            mouseWheelZoom: true
        });
        
        // Cargar la imagen
        croppieInstance.bind({
            url: imageData
        });
    }, 100);
}

function closeCropModal() {
    const modal = $('#avatar-crop-modal');
    
    // Destruir Croppie
    if (croppieInstance) {
        croppieInstance.destroy();
        croppieInstance = null;
    }
    
    // Ocultar modal
    modal.addClass('hidden');
    
    // Limpiar input
    $('#avatar-file-input').val('');
}

function uploadCroppedImage() {
    if (!croppieInstance) return;
    
    // Deshabilitar botón
    const uploadBtn = $('.btn-upload');
    uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
    
    // Obtener imagen recortada
    croppieInstance.result({
        type: 'blob',
        size: {
            width: 500,
            height: 500
        },
        format: 'jpeg',
        quality: 0.9,
        circle: false
    }).then(function(blob) {
        // Crear FormData
        const formData = new FormData();
        formData.append('avatar', blob, 'avatar.jpg');
        
        // Enviar al servidor
        fetch('app/actions/upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar avatar en la página
                updateAvatarDisplays(data.avatar_url);
                
                // Cerrar modal
                closeCropModal();
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Avatar actualizado!',
                    text: 'Tu foto de perfil ha sido actualizada correctamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Error al subir la imagen');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error al subir',
                text: error.message || 'No se pudo actualizar el avatar. Por favor intenta de nuevo.',
                confirmButtonText: 'Entendido'
            });
        })
        .finally(() => {
            // Rehabilitar botón
            uploadBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Avatar');
        });
    });
}

function updateAvatarDisplays(avatarUrl) {
    // Actualizar en el sidebar del perfil
    $('.profile-avatar img').attr('src', avatarUrl);
    
    // Actualizar en el modal de usuario (si está cargado)
    if ($('#user-account-modal').length) {
        $('#user-account-modal .user-avatar').attr('src', avatarUrl);
    }
    
    // Actualizar en el header (si existe)
    if ($('.user-account-link img').length) {
        $('.user-account-link img').attr('src', avatarUrl);
    }
    
    // Actualizar en offcanvas menu (si existe)
    if ($('.offcanvas-user-avatar').length) {
        $('.offcanvas-user-avatar').attr('src', avatarUrl);
    }
}

// Permitir arrastrar y soltar
$(document).ready(function() {
    const uploadArea = $('#avatar-upload-area');
    
    if (!uploadArea.length) return;
    
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $('#avatar-file-input')[0].files = files;
            $('#avatar-file-input').trigger('change');
        }
    });
});
