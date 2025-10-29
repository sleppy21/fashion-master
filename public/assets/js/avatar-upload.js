/* ============================================
   AVATAR UPLOAD & CROP FUNCTIONALITY
   ============================================ */

let croppieInstance = null;

$(document).ready(function() {
    
    // Verificar elementos necesarios
    const avatarUploadArea = $('#avatar-upload-area');
    const avatarFileInput = $('#avatar-file-input');
    const cropModal = $('#avatar-crop-modal');
    
    
    if (avatarUploadArea.length === 0) {
    return;
        return;
    }
    
    if (avatarFileInput.length === 0) {
    return;
        return;
    }
    
    if (cropModal.length === 0) {
    return;
        return;
    }
        
    // Click en el avatar para seleccionar imagen
    avatarUploadArea.on('click', function(e) {
        e.preventDefault();
        avatarFileInput.click();
    });

    // Cuando se selecciona un archivo
    avatarFileInput.on('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) {
            return;
            return;
        }
        
    
        
        // Validar tipo de archivo
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showNotification('Por favor selecciona una imagen válida (JPG, PNG o GIF)', 'error');
            return;
        }
        
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            showNotification('La imagen debe pesar menos de 5MB', 'error');
            return;
        }
        
        
        // Leer la imagen y mostrar el modal
        const reader = new FileReader();
        reader.onload = function(event) {
            showCropModal(event.target.result);
        };
        reader.onerror = function(error) {
            showNotification('Error al leer la imagen', 'error');
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
    
    if (modal.length === 0) {
        showNotification('Error: Modal no encontrado', 'error');
        return;
    }
    
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
        
        if (!cropElement) {
            showNotification('Error: Elemento de crop no encontrado', 'error');
            return;
        }
        
        
        try {
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
            
        } catch (error) {
            showNotification('Error al inicializar el editor de imagen', 'error');
        }
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
    
    if (!croppieInstance) {
        showNotification('Error: No hay imagen para subir', 'error');
        return;
    }
    
    // Deshabilitar botón
    const uploadBtn = $('.btn-upload');
    uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
    
    // Obtener imagen recortada con máxima calidad
    croppieInstance.result({
        type: 'blob',
        size: {
            width: 800,  // Mayor resolución
            height: 800
        },
        format: 'jpeg',
        quality: 1.0,    // Máxima calidad
        circle: true     // Forzar círculo perfecto
    }).then(function(blob) {
       
        
        // Crear FormData
        const formData = new FormData();
        formData.append('avatar', blob, 'avatar.jpg');
        
        // Enviar al servidor
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(baseUrl + '/app/actions/upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            
            if (data.success) {
                
                // Verificar si estamos en la página de perfil Y si existe el header avatar
                const isProfilePage = window.location.pathname.includes('profile.php');
                const hasHeaderAvatar = $('.header-user-avatar .avatar-image').length > 0;
                
                
                if (isProfilePage && hasHeaderAvatar) {
                    // Construir URL completa de la nueva imagen
                    const siteRoot = window.location.origin + '/fashion-master/';
                    let newImageUrl = data.avatar_url;
                    
                    // Asegurar URL absoluta
                    if (!newImageUrl.startsWith('http')) {
                        if (!newImageUrl.startsWith('/')) {
                            newImageUrl = siteRoot + newImageUrl;
                        } else {
                            newImageUrl = window.location.origin + newImageUrl;
                        }
                    }
                    
                    // Agregar timestamp único
                    newImageUrl += '?t=' + Date.now();
                    
                    
                    const $profileAvatar = $('.profile-sidebar .profile-avatar .avatar-image');
                    const $profileContainer = $('.profile-sidebar .profile-avatar');
                    
                    // PASO 1: Cerrar modal primero
                    closeCropModal();
                    
                    // PASO 2: Pre-cargar imagen en memoria
                    const preloadImg = new Image();
                    preloadImg.src = newImageUrl;
                    
                    preloadImg.onload = function() {
                        // PASO 3: Actualizar avatar del perfil
                        $profileAvatar.css({
                            opacity: 0,
                            transform: 'scale(0.9)',
                            transition: 'none'
                        }).attr('src', newImageUrl);
                        
                        // PASO 4: Animar entrada del nuevo avatar
                        requestAnimationFrame(() => {
                            $profileAvatar.css({
                                opacity: 1,
                                transform: 'scale(1)',
                                transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                            });
                            
                            // PASO 5: Aplicar shadow con animación
                            const avatarContainer = $profileContainer[0];
                            if (avatarContainer) {
                                avatarContainer.style.transition = 'box-shadow 0.3s ease-out';
                                updateAvatarShadow(avatarContainer, preloadImg);
                                
                                // PASO 6: Iniciar animación cuando el shadow esté listo
                                setTimeout(() => {
                                    if (typeof window.flyAvatarToHeaderRealTime === 'function') {
                                        window.flyAvatarToHeaderRealTime(newImageUrl, () => {
                                            updateModalAvatar(newImageUrl);
                                        });
                                    } else {
                                        flyAvatarToHeader(newImageUrl, () => {
                                            updateModalAvatar(newImageUrl);
                                        });
                                    }
                                }, 100);
                            }
                        });
                    };
                    
                    // Si la imagen ya está en caché
                    if (preloadImg.complete) {
                        preloadImg.onload();
                    }
                } else {
                    // Actualización normal sin animación
                    updateAvatarDisplays(data.avatar_url);
                }
                
                // Cerrar modal
                closeCropModal();
                
                // Mostrar mensaje de éxito con toast
                showNotification('✅ Avatar actualizado correctamente', 'success');
            } else {
                throw new Error(data.message || 'Error al subir la imagen');
            }
        })
        .catch(error => {
        })
        .finally(() => {
            // Rehabilitar botón
            uploadBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Avatar');
        });
    }).catch(function(error) {
        showNotification('Error al procesar la imagen', 'error');
        uploadBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Avatar');
    });
}/**
 * Actualiza SOLO el avatar del modal (el perfil y header ya fueron actualizados)
 */
function updateModalAvatar(avatarUrl) {
    
    // Actualizar en el modal de cuenta
    const modalAvatar = $('#userAccountModal .modal-avatar img');
    if (modalAvatar.length) {
        const newUrl = avatarUrl.includes('?t=') ? avatarUrl : avatarUrl + '?t=' + Date.now();
        modalAvatar.attr('src', newUrl);
    }
    
    // Aplicar shadow dinámico si existe la función
    if (typeof applyDynamicShadow === 'function') {
        setTimeout(() => applyDynamicShadow(), 100);
    }
}

/**
 * Actualiza SOLO perfil y modal (NO header - para evitar loops con animación)
 */
function updateProfileAndModalAvatars(avatarUrl) {
    
    // Actualizar en el sidebar del perfil
    const profileAvatar = $('.profile-avatar img');
    if (profileAvatar.length) {
        const newUrl = avatarUrl + '?t=' + new Date().getTime();
        profileAvatar.attr('src', newUrl);
        
        profileAvatar.off('load').one('load', function() {
            const avatarContainer = profileAvatar.closest('.profile-avatar')[0];
            if (avatarContainer) {
                updateAvatarShadow(avatarContainer, this);
            }
        });
        
        if (profileAvatar[0].complete && profileAvatar[0].naturalWidth > 0) {
            const avatarContainer = profileAvatar.closest('.profile-avatar')[0];
            if (avatarContainer) {
                updateAvatarShadow(avatarContainer, profileAvatar[0]);
            }
        }
    }
    
    // Actualizar en el modal de usuario
    if ($('#user-account-modal').length) {
        const modalAvatar = $('#user-account-modal .user-avatar');
        if (modalAvatar.length) {
            const newUrl = avatarUrl + '?t=' + new Date().getTime();
            modalAvatar.attr('src', newUrl);
            
            modalAvatar.off('load').one('load', function() {
                const modalContainer = $('#modal-user-avatar')[0];
                if (modalContainer) {
                    updateAvatarShadow(modalContainer, this);
                }
            });
            
            if (modalAvatar[0].complete && modalAvatar[0].naturalWidth > 0) {
                const modalContainer = $('#modal-user-avatar')[0];
                if (modalContainer) {
                    updateAvatarShadow(modalContainer, modalAvatar[0]);
                }
            }
        }
    }
    
    // Actualizar en offcanvas menu (si existe)
    if ($('.offcanvas-user-avatar').length) {
        $('.offcanvas-user-avatar').attr('src', avatarUrl + '?t=' + new Date().getTime());
    }
}

function updateAvatarDisplays(avatarUrl) {
    
    // Actualizar en el sidebar del perfil
    const profileAvatar = $('.profile-avatar img');
    if (profileAvatar.length) {
        const newUrl = avatarUrl + '?t=' + new Date().getTime(); // Cache bust
        profileAvatar.attr('src', newUrl);
        
        // ACTUALIZAR SHADOW EN TIEMPO REAL - Esperar a que la imagen se cargue
        profileAvatar.off('load').one('load', function() {
            const avatarContainer = profileAvatar.closest('.profile-avatar')[0];
            if (avatarContainer) {
                // Usar directamente la imagen del DOM que ya está cargada
                updateAvatarShadow(avatarContainer, this);
            }
        });
        
        // Si la imagen ya está en cache y cargada, ejecutar inmediatamente
        if (profileAvatar[0].complete && profileAvatar[0].naturalWidth > 0) {
            const avatarContainer = profileAvatar.closest('.profile-avatar')[0];
            if (avatarContainer) {
                updateAvatarShadow(avatarContainer, profileAvatar[0]);
            }
        }
    }
    
    // Actualizar en el modal de usuario (si está cargado)
    if ($('#user-account-modal').length) {
        const modalAvatar = $('#user-account-modal .user-avatar');
        if (modalAvatar.length) {
            const newUrl = avatarUrl + '?t=' + new Date().getTime();
            modalAvatar.attr('src', newUrl);
            
            // ACTUALIZAR SHADOW EN TIEMPO REAL PARA MODAL
            modalAvatar.off('load').one('load', function() {
                const modalContainer = $('#modal-user-avatar')[0];
                if (modalContainer) {
                    updateAvatarShadow(modalContainer, this);
                }
            });
            
            // Si ya está cargado
            if (modalAvatar[0].complete && modalAvatar[0].naturalWidth > 0) {
                const modalContainer = $('#modal-user-avatar')[0];
                if (modalContainer) {
                    updateAvatarShadow(modalContainer, modalAvatar[0]);
                }
            }
        }
    }
    
    // Actualizar en el header (avatar circular)
    const headerAvatar = $('.header-user-avatar .avatar-image');
    if (headerAvatar.length) {
        const newUrl = avatarUrl + '?t=' + new Date().getTime();
        headerAvatar.attr('src', newUrl);
        
        // ACTUALIZAR SHADOW EN TIEMPO REAL PARA HEADER
        headerAvatar.off('load').one('load', function() {
            const headerContainer = $('.header-user-avatar')[0];
            if (headerContainer && window.applyDynamicShadow) {
                // Usar la función global de image-color-extractor.js
                window.applyDynamicShadow(this, {
                    shadowIntensity: 0.4,
                    shadowBlur: 15,
                    shadowSpread: 0,
                    saturationBoost: 1.8
                });
            }
        });
        
        // Si ya está cargado
        if (headerAvatar[0].complete && headerAvatar[0].naturalWidth > 0) {
            if (window.applyDynamicShadow) {
                window.applyDynamicShadow(headerAvatar[0], {
                    shadowIntensity: 0.4,
                    shadowBlur: 15,
                    shadowSpread: 0,
                    saturationBoost: 1.8
                });
            }
        }
    }
    
    // Actualizar en el header (legacy - si existe el selector antiguo)
    if ($('.user-account-link img').length) {
        $('.user-account-link img').attr('src', avatarUrl + '?t=' + new Date().getTime());
    }
    
    // Actualizar en offcanvas menu (si existe)
    if ($('.offcanvas-user-avatar').length) {
        $('.offcanvas-user-avatar').attr('src', avatarUrl + '?t=' + new Date().getTime());
    }
}

/**
 * Actualiza el shadow del avatar basado en los colores de la imagen
 * FUNCIÓN INDEPENDIENTE para actualización en tiempo real
 */
function updateAvatarShadow(avatarElement, img) {
    
    if (!avatarElement || !img) {
        return;
    }
    
    try {
        const color = getAverageColorFromImage(img);
        const saturatedColor = increaseSaturationColor(color.r, color.g, color.b, 1.8);
        
        
        // Crear shadows múltiples con el color extraído
        const shadows = [
            `0 4px 12px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, 0.4)`,
            `0 8px 24px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, 0.3)`,
            `0 0 30px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, 0.2)`
        ];
        
        // Aplicar con una pequeña animación
        avatarElement.style.transition = 'box-shadow 0.5s ease-in-out';
        avatarElement.style.boxShadow = shadows.join(', ');
        avatarElement.dataset.shadowColor = `${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}`;
        
        // También aplicar a la imagen si es diferente del contenedor
        if (img !== avatarElement && img.tagName === 'IMG') {
            img.dataset.shadowColor = `${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}`;
            img.dataset.shadowApplied = 'true';
        }
        
        
        // Disparar evento personalizado para notificar que el color está listo
        const event = new CustomEvent('avatarShadowUpdated', {
            detail: { 
                r: saturatedColor.r, 
                g: saturatedColor.g, 
                b: saturatedColor.b 
            },
            bubbles: true
        });
        
        // Disparar desde el documento para que se propague globalmente
        document.dispatchEvent(event);
        
    } catch (e) {
        // Aplicar shadow por defecto en caso de error
        const defaultShadow = '0 4px 12px rgba(102, 126, 234, 0.4), 0 8px 24px rgba(102, 126, 234, 0.3), 0 0 30px rgba(102, 126, 234, 0.2)';
        avatarElement.style.boxShadow = defaultShadow;
    }
}

/**
 * Extrae el color promedio de una imagen
 */
function getAverageColorFromImage(img) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    
    // Usar dimensiones más pequeñas para mejor rendimiento
    const width = Math.min(img.naturalWidth || img.width, 200);
    const height = Math.min(img.naturalHeight || img.height, 200);
    
    canvas.width = width;
    canvas.height = height;
    
    try {
        // Dibujar la imagen en el canvas
        ctx.drawImage(img, 0, 0, width, height);
        
        // Intentar obtener los datos de la imagen
        const imageData = ctx.getImageData(0, 0, width, height);
        const data = imageData.data;
        
        let r = 0, g = 0, b = 0;
        let count = 0;
        
        // Muestrear cada 10 píxeles para mejor rendimiento
        for (let i = 0; i < data.length; i += 40) { // 40 = 10 pixels * 4 (RGBA)
            const red = data[i];
            const green = data[i + 1];
            const blue = data[i + 2];
            const alpha = data[i + 3];
            
            // Solo contar píxeles no transparentes
            if (alpha > 128) {
                r += red;
                g += green;
                b += blue;
                count++;
            }
        }
        
        if (count === 0) {
            return { r: 102, g: 126, b: 234 }; // Color por defecto
        }
        
        r = Math.floor(r / count);
        g = Math.floor(g / count);
        b = Math.floor(b / count);
        
        
        return { r, g, b };
    } catch (e) {
        // Retornar color por defecto (púrpura del gradiente)
        return { r: 102, g: 126, b: 234 }; // #667eea
    }
}

/**
 * Aumenta la saturación de un color RGB
 */
function increaseSaturationColor(r, g, b, amount = 1.5) {
    r /= 255;
    g /= 255;
    b /= 255;

    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h, s, l = (max + min) / 2;

    if (max === min) {
        h = s = 0;
    } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        
        switch (max) {
            case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
            case g: h = ((b - r) / d + 2) / 6; break;
            case b: h = ((r - g) / d + 4) / 6; break;
        }
    }

    s = Math.min(1, s * amount);

    function hue2rgb(p, q, t) {
        if (t < 0) t += 1;
        if (t > 1) t -= 1;
        if (t < 1/6) return p + (q - p) * 6 * t;
        if (t < 1/2) return q;
        if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
        return p;
    }

    let r2, g2, b2;
    if (s === 0) {
        r2 = g2 = b2 = l;
    } else {
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r2 = hue2rgb(p, q, h + 1/3);
        g2 = hue2rgb(p, q, h);
        b2 = hue2rgb(p, q, h - 1/3);
    }

    return {
        r: Math.round(r2 * 255),
        g: Math.round(g2 * 255),
        b: Math.round(b2 * 255)
    };
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

/**
 * ============================================
 * ANIMACIÓN DE VUELO DEL AVATAR
 * Avatar vuela desde el perfil hasta el header
 * ============================================
 */
function flyAvatarToHeader(avatarUrl, callback) {
    
    // PASO 1: ENCONTRAR ELEMENTOS
    const $profileSidebar = $('.profile-sidebar').first();
    const $profileAvatarContainer = $profileSidebar.find('.profile-avatar').first();
    const $profileAvatar = $profileAvatarContainer.find('.avatar-image').first();
    const $headerContainer = $('.header-user-avatar').first();
    
    
    if (!$profileAvatar.length || !$headerContainer.length) {
        if (callback) callback();
        return;
    }
    
    // PASO 2: OBTENER POSICIONES USANDO MÚLTIPLES MÉTODOS
    // USAR EL CONTENEDOR, NO LA IMAGEN (porque la imagen tiene position: absolute)
    const sourceRect = $profileAvatarContainer[0].getBoundingClientRect();
    const sourceOffset = $profileAvatarContainer.offset();
    const headerRect = $headerContainer[0].getBoundingClientRect();
    const headerOffset = $headerContainer.offset();
    
    // PASO 3: CREAR MARCADORES VISUALES PARA DEBUG (opcional - comentar después)
    const $debugSource = $('<div>').css({
        position: 'fixed',
        top: sourceRect.top + 'px',
        left: sourceRect.left + 'px',
        width: sourceRect.width + 'px',
        height: sourceRect.height + 'px',
        border: '3px solid red',
        borderRadius: '50%',
        zIndex: 9999999,
        pointerEvents: 'none',
        background: 'rgba(255, 0, 0, 0.2)'
    });
    
    const $debugDest = $('<div>').css({
        position: 'fixed',
        top: headerRect.top + 'px',
        left: headerRect.left + 'px',
        width: headerRect.width + 'px',
        height: headerRect.height + 'px',
        border: '3px solid green',
        borderRadius: '50%',
        zIndex: 9999999,
        pointerEvents: 'none',
        background: 'rgba(0, 255, 0, 0.2)'
    });
    
    $('body').append($debugSource, $debugDest);
    
    // Remover marcadores después de 3 segundos
    setTimeout(() => {
        $debugSource.fadeOut(500, function() { $(this).remove(); });
        $debugDest.fadeOut(500, function() { $(this).remove(); });
    }, 3000);
    
    // PASO 4: CREAR CLON ANIMADO
    const $flyingClone = $('<div>').addClass('flying-avatar-clone').css({
        position: 'fixed',
        top: sourceRect.top + 'px',
        left: sourceRect.left + 'px',
        width: sourceRect.width + 'px',
        height: sourceRect.height + 'px',
        borderRadius: '50%',
        overflow: 'hidden',
        zIndex: 9999998,
        pointerEvents: 'none',
        boxShadow: '0 25px 70px rgba(0,0,0,0.6), 0 0 0 4px rgba(255,255,255,0.8)',
        transition: 'none',
        backgroundImage: `url(${$profileAvatar.attr('src')})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        border: '4px solid white'
    });
    
    $('body').append($flyingClone);
    
    // PASO 5: OVERLAY OSCURO
    const $overlay = $('<div>').css({
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        background: 'rgba(0,0,0,0.7)',
        zIndex: 9999997,
        pointerEvents: 'none',
        opacity: 0,
        transition: 'opacity 0.5s',
        backdropFilter: 'blur(3px)'
    });
    
    $('body').append($overlay);
    
    // PASO 6: EFECTOS INICIALES
    $profileAvatar.css({
        opacity: 0.15,
        filter: 'blur(2px)',
        transition: 'all 0.4s'
    });
    
    $headerContainer.css({
        animation: 'pulse 1.2s ease-in-out infinite',
        boxShadow: '0 0 0 4px rgba(102, 126, 234, 0.5), 0 0 20px rgba(102, 126, 234, 0.3) !important',
        transition: 'all 0.3s'
    });
    
    setTimeout(() => $overlay.css('opacity', 1), 50);
    
    // PASO 7: CALCULAR TRAYECTORIA
    const deltaX = headerRect.left - sourceRect.left;
    const deltaY = headerRect.top - sourceRect.top;
    const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    const duration = Math.min(2500, Math.max(1500, distance * 2));
    
    
    // PASO 8: INICIAR ANIMACIÓN
    setTimeout(() => {
        
        $flyingClone.css({
            top: headerRect.top + 'px',
            left: headerRect.left + 'px',
            width: headerRect.width + 'px',
            height: headerRect.height + 'px',
            transform: 'rotate(1080deg) scale(1.3)',
            transition: `all ${duration}ms cubic-bezier(0.22, 0.61, 0.36, 1)`,
            opacity: 1,
            boxShadow: '0 30px 90px rgba(102, 126, 234, 0.8), 0 0 0 4px rgba(255,255,255,1)'
        });
        
    }, 200);
    
    // PASO 9: AL ATERRIZAR
    setTimeout(() => {
        
        const $headerAvatar = $headerContainer.find('.avatar-image');
        const newImageUrl = avatarUrl + '?t=' + Date.now();
        
        $headerAvatar.off('load').one('load', function() {
            
            // Remover elementos
            $flyingClone.css({
                opacity: 0,
                transform: 'scale(0.5)',
                transition: 'all 0.4s'
            });
            
            setTimeout(() => $flyingClone.remove(), 400);
            
            $overlay.css('opacity', 0);
            setTimeout(() => $overlay.remove(), 500);
            
            // Restaurar avatar original
            $profileAvatar.css({
                opacity: 1,
                filter: 'none'
            });
            
            // Remover efectos del header
            $headerContainer.css({
                animation: 'none',
                boxShadow: ''
            });
            
            // Flash de éxito
            $headerContainer.css({
                boxShadow: '0 0 30px rgba(40, 167, 69, 0.9) !important',
                transform: 'scale(1.1)',
                transition: 'all 0.4s'
            });
            
            setTimeout(() => {
                $headerContainer.css({
                    boxShadow: '',
                    transform: 'scale(1)'
                });
            }, 1000);
            
            
            if (callback) callback();
        });
        
        $headerAvatar.attr('src', newImageUrl);
        
        if ($headerAvatar[0].complete) {
            $headerAvatar.trigger('load');
        }
        
    }, duration + 250);
}

// INYECTAR CSS
if (!$('#avatar-flight-pulse-animation').length) {
    $('head').append(`
        <style id="avatar-flight-pulse-animation">
            @keyframes pulse {
                0%, 100% { 
                    transform: scale(1); 
                }
                50% { 
                    transform: scale(1.2); 
                }
            }
            
            .flying-avatar-clone {
                will-change: transform, top, left, width, height, opacity;
            }
        </style>
    `);
}

