/**
 * ANIMACIÓN DE VUELO DEL AVATAR CON TRACKING EN TIEMPO REAL
 */

window.flyAvatarToHeaderRealTime = function(avatarUrl, callback) {
    
    // ENCONTRAR ELEMENTOS
    const $profileContainer = $('.profile-sidebar .profile-avatar').first();
    const $profileAvatar = $profileContainer.find('.avatar-image').first();
    const $headerContainer = $('.header-user-avatar').first();
    
    if (!$profileContainer.length || !$headerContainer.length) {
        if (callback) callback();
        return;
    }
    
    // FUNCIÓN PARA OBTENER POSICIONES EN TIEMPO REAL
    function getRealTimePositions() {
        const source = $profileContainer[0].getBoundingClientRect();
        const dest = $headerContainer[0].getBoundingClientRect();
        
        return {
            source: {
                top: source.top,
                left: source.left,
                width: source.width,
                height: source.height,
                centerX: source.left + source.width / 2,
                centerY: source.top + source.height / 2
            },
            dest: {
                top: dest.top,
                left: dest.left,
                width: dest.width,
                height: dest.height,
                centerX: dest.left + dest.width / 2,
                centerY: dest.top + dest.height / 2
            }
        };
    }
    
    // POSICIONES INICIALES
    const initialPos = getRealTimePositions();
    
    
    // USAR LA URL QUE SE PASÓ COMO PARÁMETRO (no leer del DOM)
    let currentImageUrl = avatarUrl;
    
    
    // Pre-cargar la imagen para asegurar que esté lista
    const preloadImg = new Image();
    preloadImg.crossOrigin = 'anonymous';
    preloadImg.src = currentImageUrl;
    
    
    // Variable para guardar el color dominante
    let imageShadow = '0 0 15px rgba(0,0,0,0.25), 0 0 30px rgba(0,0,0,0.15)'; // Shadow circular muy sutil
    let shadowColorRGB = null; // Guardar RGB para aplicar después
    
    // Esperar a que la imagen se cargue antes de crear el clon
    preloadImg.onload = function() {
        
        // EXTRAER COLOR DOMINANTE DE LA IMAGEN
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = preloadImg.width;
            canvas.height = preloadImg.height;
            ctx.drawImage(preloadImg, 0, 0);
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            let r = 0, g = 0, b = 0;
            
            for (let i = 0; i < data.length; i += 4) {
                r += data[i];
                g += data[i + 1];
                b += data[i + 2];
            }
            
            const pixelCount = data.length / 4;
            r = Math.floor(r / pixelCount);
            g = Math.floor(g / pixelCount);
            b = Math.floor(b / pixelCount);
            
            // Saturar el color un poco
            const saturation = 1.3;
            const avg = (r + g + b) / 3;
            r = Math.min(255, Math.floor(avg + (r - avg) * saturation));
            g = Math.min(255, Math.floor(avg + (g - avg) * saturation));
            b = Math.min(255, Math.floor(avg + (b - avg) * saturation));
            
            // Guardar RGB para aplicar después
            shadowColorRGB = { r, g, b };
            
            // Crear shadow con el color extraído (CIRCULAR con blur-radius) - MUY SUTIL
            imageShadow = `0 0 15px rgba(${r}, ${g}, ${b}, 0.25), 0 0 30px rgba(${r}, ${g}, ${b}, 0.15)`;
            
            
        } catch (e) {
        }
        
        createAndAnimateClone();
    };
    
    preloadImg.onerror = function() {
        createAndAnimateClone();
    };
    
    // FUNCIÓN PRINCIPAL DE ANIMACIÓN
    function createAndAnimateClone() {
    
    // CLONAR AVATAR VOLADOR USANDO IMG REAL
    const $flyingAvatar = $('<div>').css({
        position: 'fixed',
        top: initialPos.source.top + 'px',
        left: initialPos.source.left + 'px',
        width: initialPos.source.width + 'px',
        height: initialPos.source.height + 'px',
        borderRadius: '50%',
        overflow: 'hidden',
        zIndex: 9999999,
        opacity: 0,
        pointerEvents: 'none',
        boxShadow: imageShadow,
        willChange: 'transform, top, left, width, height, opacity',
        transformOrigin: 'center center',
        transition: 'none'
    });
    
    // CREAR IMG TAG REAL DENTRO DEL DIV
    const $img = $('<img>').attr({
        'src': currentImageUrl,
        'crossorigin': 'anonymous'
    }).css({
        width: '100%',
        height: '100%',
        objectFit: 'cover',
        objectPosition: 'center',
        borderRadius: '50%',
        display: 'block'
    });
    
    $flyingAvatar.append($img);
    $('body').append($flyingAvatar);
    
    // NO CREAR OVERLAY OSCURO - Se eliminó para que el fondo no cambie
    
    // EFECTOS VISUALES (reducidos para no oscurecer)
    $profileAvatar.css({
        opacity: 0.3,
        filter: 'blur(2px)',
        transition: 'all 0.3s'
    });
    
    // NO AGREGAR EFECTOS AL HEADER - Solo resaltar sin sombras azules/verdes
    
    // CALCULAR PARÁMETROS (ANIMACIÓN MÁS RÁPIDA)
    const distance = Math.sqrt(
        Math.pow(initialPos.dest.centerX - initialPos.source.centerX, 2) + 
        Math.pow(initialPos.dest.centerY - initialPos.source.centerY, 2)
    );
    const duration = Math.min(1200, Math.max(800, distance * 0.8)); // Más rápida (reducido de 1.2)
    
    
    // ANIMACIÓN CON REQUEST ANIMATION FRAME
    let startTime = null;
    let animationId = null;
    
    function animateFrame(timestamp) {
        if (!startTime) startTime = timestamp;
        const elapsed = timestamp - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing: ease-out-cubic (más rápido al inicio, más lento al final)
        const eased = 1 - Math.pow(1 - progress, 3);
        
        // OBTENER POSICIONES ACTUALES EN TIEMPO REAL
        const currentPos = getRealTimePositions();
        
        // INTERPOLAR
        const top = currentPos.source.top + (currentPos.dest.top - currentPos.source.top) * eased;
        const left = currentPos.source.left + (currentPos.dest.left - currentPos.source.left) * eased;
        const width = currentPos.source.width + (currentPos.dest.width - currentPos.source.width) * eased;
        const height = currentPos.source.height + (currentPos.dest.height - currentPos.source.height) * eased;
        
        // APLICAR TRANSFORMACIONES (sin bordes blancos, con shadow dinámico)
        const rotation = 1440 * eased; // 4 vueltas completas
        const scale = 1 + 0.4 * Math.sin(progress * Math.PI); // Efecto de pulso
        
        // Intensificar el shadow durante la animación
        const shadowIntensity = 0.6 + 0.4 * progress;
        const currentShadow = imageShadow.replace(/0\.\d+/g, shadowIntensity.toFixed(1));
        
        $flyingAvatar.css({
            top: top + 'px',
            left: left + 'px',
            width: width + 'px',
            height: height + 'px',
            transform: `rotate(${rotation}deg) scale(${scale})`,
            boxShadow: currentShadow,
            opacity: 1
        });
        
        // CONTINUAR O FINALIZAR
        if (progress < 1) {
            animationId = requestAnimationFrame(animateFrame);
        } else {
            finalizarAnimacion();
        }
    }
    
    // FUNCIÓN DE FINALIZACIÓN
    function finalizarAnimacion() {
        
        // Primero hacer que el clon se fusione visualmente
        $flyingAvatar.css({
            opacity: 0,
            transform: 'scale(0.8)',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
        });
        
        // Restaurar perfil gradualmente
        $profileAvatar.css({
            opacity: 1,
            filter: 'none',
            transition: 'all 0.3s ease-out'
        });
        
        // ESPERAR a que el clon "desaparezca" antes de actualizar el header
        setTimeout(() => {
            // Actualizar imagen del header con efecto suave
            const $headerAvatar = $headerContainer.find('.avatar-image');
            const newImageUrl = avatarUrl + '?t=' + Date.now();
            
            // Preparar el header para la transición
            $headerAvatar.css({
                transition: 'all 0.3s ease-out',
                opacity: 0,
                transform: 'scale(0.9)'
            });
            
                
                // NO aplicar shadow aquí - ya se aplicó desde avatarShadowUpdated
                // Solo asegurarnos de que tenga el data-shadow-color
            // Actualizar header con delay y efectos suaves
            setTimeout(() => {
                const $headerAvatar = $headerContainer.find('.avatar-image');
                const newImageUrl = avatarUrl + '?t=' + Date.now();
                
                // Preparar header para transición
                $headerContainer.css({
                    transform: 'scale(0.9)',
                    opacity: 0.5,
                    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                });
                
                // Actualizar imagen y animar entrada
                $headerAvatar.attr('src', newImageUrl);
                $headerAvatar.one('load', function() {
                    $headerContainer.css({
                        transform: 'scale(1)',
                        opacity: 1
                    });
                    
                    // Limpiar y ejecutar callback
                    setTimeout(() => {
                        $flyingAvatar.remove();
                        if (callback) callback();
                    }, 300);
                });
                
                if ($headerAvatar[0].complete) {
                    $headerAvatar.trigger('load');
                }
            }, 200);
            });
            
        }
        
        // Iniciar animación con pequeño delay
        setTimeout(() => {
            requestAnimationFrame(animateFrame);
        }, 50);
    }
    
    // Extraer color dominante y crear shadow
    preloadImg.onload = function() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = preloadImg.width;
            canvas.height = preloadImg.height;
            ctx.drawImage(preloadImg, 0, 0);
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            let r = 0, g = 0, b = 0;
            
            for (let i = 0; i < data.length; i += 4) {
                r += data[i];
                g += data[i + 1];
                b += data[i + 2];
            }
            
            const pixelCount = data.length / 4;
            r = Math.floor(r / pixelCount);
            g = Math.floor(g / pixelCount);
            b = Math.floor(b / pixelCount);
            
            // Saturar color
            const saturation = 1.3;
            const avg = (r + g + b) / 3;
            r = Math.min(255, Math.floor(avg + (r - avg) * saturation));
            g = Math.min(255, Math.floor(avg + (g - avg) * saturation));
            b = Math.min(255, Math.floor(avg + (b - avg) * saturation));
            
            shadowColorRGB = { r, g, b };
            imageShadow = `0 8px 24px rgba(${r}, ${g}, ${b}, 0.35)`;
        } catch (e) {
            console.warn('Error al extraer color:', e);
        }
        
        createAndAnimateClone();
    };
    
    preloadImg.onerror = function() {
        console.warn('Error al cargar imagen para color extraction');
        createAndAnimateClone();
    };
};

// CSS PARA ANIMACIÓN
if (!$('#avatar-realtime-flight-css').length) {
    $('head').append(`
        <style id="avatar-realtime-flight-css">
            @keyframes pulse-header {
                0%, 100% { 
                    transform: scale(1); 
                }
                50% { 
                    transform: scale(1.3); 
                }
            }
        </style>
    `);
}

