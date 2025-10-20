/* ============================================
   AVATAR UPLOAD & CROP FUNCTIONALITY
   ============================================ */

let croppieInstance = null;

$(document).ready(function() {
    console.log('🎭 Avatar Upload: Inicializando...');
    
    // Verificar elementos necesarios
    const avatarUploadArea = $('#avatar-upload-area');
    const avatarFileInput = $('#avatar-file-input');
    const cropModal = $('#avatar-crop-modal');
    
    console.log('📍 Elementos encontrados:', {
        uploadArea: avatarUploadArea.length,
        fileInput: avatarFileInput.length,
        cropModal: cropModal.length
    });
    
    if (avatarUploadArea.length === 0) {
        console.error('❌ Error: #avatar-upload-area no encontrado');
        return;
    }
    
    if (avatarFileInput.length === 0) {
        console.error('❌ Error: #avatar-file-input no encontrado');
        return;
    }
    
    if (cropModal.length === 0) {
        console.error('❌ Error: #avatar-crop-modal no encontrado');
        return;
    }
    
    console.log('✅ Todos los elementos encontrados correctamente');
    
    // Click en el avatar para seleccionar imagen
    avatarUploadArea.on('click', function(e) {
        e.preventDefault();
        console.log('🖱️ Click en área de avatar');
        avatarFileInput.click();
    });

    // Cuando se selecciona un archivo
    avatarFileInput.on('change', function(e) {
        console.log('📁 Archivo seleccionado:', e.target.files);
        const file = e.target.files[0];
        
        if (!file) {
            console.warn('⚠️ No se seleccionó ningún archivo');
            return;
        }
        
        console.log('📄 Archivo:', {
            name: file.name,
            type: file.type,
            size: file.size,
            sizeMB: (file.size / 1024 / 1024).toFixed(2) + ' MB'
        });
        
        // Validar tipo de archivo
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            console.error('❌ Tipo de archivo inválido:', file.type);
            showNotification('Por favor selecciona una imagen válida (JPG, PNG o GIF)', 'error');
            return;
        }
        
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
            console.error('❌ Archivo muy grande:', file.size);
            showNotification('La imagen debe pesar menos de 5MB', 'error');
            return;
        }
        
        console.log('✅ Validaciones pasadas, leyendo imagen...');
        
        // Leer la imagen y mostrar el modal
        const reader = new FileReader();
        reader.onload = function(event) {
            console.log('✅ Imagen leída, mostrando modal de crop');
            showCropModal(event.target.result);
        };
        reader.onerror = function(error) {
            console.error('❌ Error al leer archivo:', error);
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
    console.log('🎨 Mostrando modal de crop...');
    const modal = $('#avatar-crop-modal');
    
    if (modal.length === 0) {
        console.error('❌ Modal #avatar-crop-modal no encontrado');
        showNotification('Error: Modal no encontrado', 'error');
        return;
    }
    
    // Mostrar modal
    modal.removeClass('hidden');
    console.log('✅ Modal mostrado, clase "hidden" removida');
    
    // Esperar a que el modal esté visible antes de inicializar Croppie
    setTimeout(() => {
        console.log('🔧 Inicializando Croppie...');
        
        // Destruir instancia anterior si existe
        if (croppieInstance) {
            console.log('🗑️ Destruyendo instancia anterior de Croppie');
            croppieInstance.destroy();
        }
        
        // Inicializar Croppie
        const cropElement = document.getElementById('crop-image');
        
        if (!cropElement) {
            console.error('❌ Elemento #crop-image no encontrado');
            showNotification('Error: Elemento de crop no encontrado', 'error');
            return;
        }
        
        console.log('✅ Elemento #crop-image encontrado');
        
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
            
            console.log('✅ Croppie inicializado');
            
            // Cargar la imagen
            croppieInstance.bind({
                url: imageData
            });
            
            console.log('✅ Imagen cargada en Croppie');
        } catch (error) {
            console.error('❌ Error al inicializar Croppie:', error);
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
    console.log('📤 Iniciando subida de imagen recortada...');
    
    if (!croppieInstance) {
        console.error('❌ No hay instancia de Croppie');
        showNotification('Error: No hay imagen para subir', 'error');
        return;
    }
    
    // Deshabilitar botón
    const uploadBtn = $('.btn-upload');
    uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
    console.log('🔒 Botón deshabilitado');
    
    // Obtener imagen recortada
    console.log('✂️ Obteniendo resultado de Croppie...');
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
        console.log('✅ Blob obtenido:', {
            size: blob.size,
            type: blob.type,
            sizeMB: (blob.size / 1024 / 1024).toFixed(2) + ' MB'
        });
        
        // Crear FormData
        const formData = new FormData();
        formData.append('avatar', blob, 'avatar.jpg');
        console.log('📦 FormData creado');
        
        // Enviar al servidor
        console.log('🚀 Enviando a: app/actions/upload_avatar.php');
        fetch('app/actions/upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('📥 Respuesta recibida:', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('📊 Datos procesados:', data);
            
            if (data.success) {
                console.log('✅ Avatar subido exitosamente');
                console.log('🖼️ Nueva URL:', data.avatar_url);
                
                // Verificar si estamos en la página de perfil Y si existe el header avatar
                const isProfilePage = window.location.pathname.includes('profile.php');
                const hasHeaderAvatar = $('.header-user-avatar .avatar-image').length > 0;
                
                console.log('📍 Estado:', { isProfilePage, hasHeaderAvatar });
                
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
                    
                    console.log('🎨 URL final con timestamp:', newImageUrl);
                    
                    const $profileAvatar = $('.profile-sidebar .profile-avatar .avatar-image');
                    const $profileContainer = $('.profile-sidebar .profile-avatar');
                    
                    // ACTUALIZAR INMEDIATAMENTE el avatar del perfil
                    $profileAvatar.attr('src', newImageUrl);
                    console.log('✅ Avatar del perfil actualizado');
                    
                    // Esperar a que la nueva imagen se cargue completamente
                    $profileAvatar.off('load').one('load', function() {
                        console.log('🖼️ Imagen cargada en perfil');
                        
                        // CALCULAR Y APLICAR SHADOW AL AVATAR GRANDE
                        const avatarContainer = $profileContainer[0];
                        if (avatarContainer) {
                            updateAvatarShadow(avatarContainer, this);
                            
                            // Esperar un momento para que se aplique el shadow
                            setTimeout(() => {
                                console.log('✈️ Iniciando animación de vuelo...');
                                
                                // AHORA ejecutar la animación (con el shadow ya aplicado)
                                if (typeof window.flyAvatarToHeaderRealTime === 'function') {
                                    window.flyAvatarToHeaderRealTime(newImageUrl, () => {
                                        // Solo actualizar modal
                                        updateModalAvatar(newImageUrl);
                                    });
                                } else {
                                    flyAvatarToHeader(newImageUrl, () => {
                                        updateModalAvatar(newImageUrl);
                                    });
                                }
                            }, 150); // Esperar a que el shadow se aplique visualmente
                        }
                    });
                    
                    // Si la imagen ya está en caché, forzar el evento load
                    if ($profileAvatar[0].complete) {
                        $profileAvatar.trigger('load');
                    }
                } else {
                    console.log('⚠️ No cumple condiciones para animación, actualización normal');
                    // Actualización normal sin animación
                    updateAvatarDisplays(data.avatar_url);
                }
                
                // Cerrar modal
                closeCropModal();
                console.log('🚪 Modal cerrado');
                
                // Mostrar mensaje de éxito con toast
                showNotification('✅ Avatar actualizado correctamente', 'success');
            } else {
                console.error('❌ Error del servidor:', data.message);
                throw new Error(data.message || 'Error al subir la imagen');
            }
        })
        .catch(error => {
            console.error('❌ Error en la subida:', error);
            showNotification(error.message || 'No se pudo actualizar el avatar', 'error');
        })
        .finally(() => {
            console.log('🔓 Rehabilitando botón');
            // Rehabilitar botón
            uploadBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Subir Avatar');
        });
    }).catch(function(error) {
        console.error('❌ Error al procesar imagen con Croppie:', error);
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
        console.error('❌ Error al actualizar shadow:', e);
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
            console.warn('⚠️ No se encontraron píxeles válidos');
            return { r: 102, g: 126, b: 234 }; // Color por defecto
        }
        
        r = Math.floor(r / count);
        g = Math.floor(g / count);
        b = Math.floor(b / count);
        
        
        return { r, g, b };
    } catch (e) {
        console.warn('⚠️ No se pudo extraer color (posible error CORS):', e.message);
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
        console.error('❌ FALLO: Elementos no encontrados');
        if (callback) callback();
        return;
    }
    
    // PASO 2: OBTENER POSICIONES USANDO MÚLTIPLES MÉTODOS
    // USAR EL CONTENEDOR, NO LA IMAGEN (porque la imagen tiene position: absolute)
    const sourceRect = $profileAvatarContainer[0].getBoundingClientRect();
    const sourceOffset = $profileAvatarContainer.offset();
    const headerRect = $headerContainer[0].getBoundingClientRect();
    const headerOffset = $headerContainer.offset();
    
    // IMPORTANTE: getBoundingClientRect() es relativo al viewport
    // position: fixed también es relativo al viewport, así que está correcto
    console.log('📍 Posición del scroll:', {
        scrollY: window.scrollY,
        scrollX: window.scrollX
    });
    
    console.log('📏 Source Rect:', {
        top: Math.round(sourceRect.top),
        left: Math.round(sourceRect.left),
        width: Math.round(sourceRect.width),
        height: Math.round(sourceRect.height)
    });
    
    console.log('📏 Header Rect:', {
        top: Math.round(headerRect.top),
        left: Math.round(headerRect.left),
        width: Math.round(headerRect.width),
        height: Math.round(headerRect.height)
    });
    
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

