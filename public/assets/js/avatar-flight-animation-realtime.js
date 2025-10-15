/**
 * ANIMACIÓN DE VUELO DEL AVATAR CON TRACKING EN TIEMPO REAL
 * Sistema de comunicación continua entre perfil y header
 * Usa requestAnimationFrame para máxima fluidez (60 FPS)
 */

window.flyAvatarToHeaderRealTime = function(avatarUrl, callback) {
    console.log('═══════════════════════════════════════════════');
    console.log('🚀 ANIMACIÓN CON TRACKING EN TIEMPO REAL');
    console.log('═══════════════════════════════════════════════');
    console.log('📸 URL recibida:', avatarUrl);
    
    // ENCONTRAR ELEMENTOS
    const $profileContainer = $('.profile-sidebar .profile-avatar').first();
    const $profileAvatar = $profileContainer.find('.avatar-image').first();
    const $headerContainer = $('.header-user-avatar').first();
    
    if (!$profileContainer.length || !$headerContainer.length) {
        console.error('❌ Elementos no encontrados');
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
    
    console.log('📍 Posición ORIGEN:', initialPos.source);
    console.log('📍 Posición DESTINO:', initialPos.dest);
    
    // USAR LA URL QUE SE PASÓ COMO PARÁMETRO (no leer del DOM)
    let currentImageUrl = avatarUrl;
    
    console.log('🖼️ URL de imagen a usar:', currentImageUrl);
    
    // Pre-cargar la imagen para asegurar que esté lista
    const preloadImg = new Image();
    preloadImg.crossOrigin = 'anonymous';
    preloadImg.src = currentImageUrl;
    
    console.log('⏳ Verificando si la imagen está lista...');
    
    // Variable para guardar el color dominante
    let imageShadow = '0 0 15px rgba(0,0,0,0.25), 0 0 30px rgba(0,0,0,0.15)'; // Shadow circular muy sutil
    let shadowColorRGB = null; // Guardar RGB para aplicar después
    
    // Esperar a que la imagen se cargue antes de crear el clon
    preloadImg.onload = function() {
        console.log('✅ Imagen confirmada lista para animación');
        
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
            
            console.log(`🎨 Color extraído: rgb(${r}, ${g}, ${b})`);
            console.log(`✨ Shadow circular aplicado: ${imageShadow}`);
            
        } catch (e) {
            console.warn('⚠️ No se pudo extraer color, usando shadow por defecto');
        }
        
        createAndAnimateClone();
    };
    
    preloadImg.onerror = function() {
        console.warn('⚠️ Error al verificar imagen, continuando de todos modos');
        createAndAnimateClone();
    };
    
    // FUNCIÓN PRINCIPAL DE ANIMACIÓN
    function createAndAnimateClone() {
    
    // CLONAR AVATAR VOLADOR USANDO IMG REAL (no background-image)
    const $flyingAvatar = $('<div>').css({
        position: 'fixed',
        top: initialPos.source.top + 'px',
        left: initialPos.source.left + 'px',
        width: initialPos.source.width + 'px',
        height: initialPos.source.height + 'px',
        borderRadius: '50%',
        overflow: 'hidden',
        zIndex: 9999999,
        pointerEvents: 'none',
        boxShadow: imageShadow, // Usar shadow dinámico basado en color de imagen
        willChange: 'transform, top, left, width, height',
        transformOrigin: 'center center'
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
    console.log('✅ Avatar volador creado con IMG real:', currentImageUrl);
    
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
    
    console.log(`✈️ Distancia: ${Math.round(distance)}px | Duración: ${duration}ms`);
    
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
        console.log('🎯 ATERRIZAJE COMPLETADO');
        
        // Primero hacer que el clon se fusione visualmente
        $flyingAvatar.css({
            opacity: 0,
            transform: 'scale(0.8)',
            transition: 'all 0.4s ease-out'
        });
        
        // Restaurar perfil inmediatamente
        $profileAvatar.css({
            opacity: 1,
            filter: 'none'
        });
        
        // ESPERAR a que el clon "desaparezca" antes de actualizar el header
        setTimeout(() => {
            // AHORA SÍ actualizar la imagen del header
            const $headerAvatar = $headerContainer.find('.avatar-image');
            const newImageUrl = avatarUrl + '?t=' + Date.now();
            
            $headerAvatar.off('load').one('load', function() {
                console.log('✅ Nueva imagen cargada en header');
                
                // NO aplicar shadow aquí - ya se aplicó desde avatarShadowUpdated
                // Solo asegurarnos de que tenga el data-shadow-color
                if (shadowColorRGB && !this.dataset.shadowColor) {
                    const r = shadowColorRGB.r;
                    const g = shadowColorRGB.g;
                    const b = shadowColorRGB.b;
                    
                    this.dataset.shadowColor = `${r}, ${g}, ${b}`;
                    this.dataset.shadowApplied = 'true';
                    
                    console.log(`📝 data-shadow-color guardado en header: rgb(${r}, ${g}, ${b})`);
                }
                
                // ACTUALIZAR TAMBIÉN EL AVATAR DEL MODAL CON SU SHADOW
                const $modalAvatar = $('#user-account-modal .modal-avatar-img');
                if ($modalAvatar.length > 0) {
                    console.log('🔄 Actualizando avatar en modal...');
                    $modalAvatar.attr('src', newImageUrl);
                    
                    // Aplicar shadow al modal también
                    if (shadowColorRGB) {
                        const $modalContainer = $('#modal-user-avatar');
                        const r = shadowColorRGB.r;
                        const g = shadowColorRGB.g;
                        const b = shadowColorRGB.b;
                        
                        $modalContainer.css({
                            'box-shadow': `0 8px 24px rgba(${r}, ${g}, ${b}, 0.35)`
                        });
                        
                        console.log(`✨ Shadow aplicado al modal: rgba(${r}, ${g}, ${b}, 0.35)`);
                    }
                }
                
                // Disparar evento de confirmación
                if (shadowColorRGB) {
                    const event = new CustomEvent('avatarColorUpdated', {
                        detail: { 
                            r: shadowColorRGB.r, 
                            g: shadowColorRGB.g, 
                            b: shadowColorRGB.b 
                        }
                    });
                    document.dispatchEvent(event);
                }
                
                // Efectos finales header (sin sombras de colores)
                $headerContainer.css({
                    animation: 'none',
                    transform: 'scale(1.15)',
                    transition: 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)'
                });
                
                setTimeout(() => {
                    $headerContainer.css({
                        transform: 'scale(1)'
                    });
                }, 400);
                
                console.log('✨ ANIMACIÓN COMPLETADA CON ÉXITO');
                
                if (callback) callback();
            });
            
            // Actualizar src DESPUÉS de que el clon haya desaparecido
            $headerAvatar.attr('src', newImageUrl);
            if ($headerAvatar[0].complete) {
                $headerAvatar.trigger('load');
            }
            
            // Remover avatar volador
            $flyingAvatar.remove();
            
        }, 400); // Esperar 400ms a que el clon se desvanezca
    }
    
    // INICIAR ANIMACIÓN
    setTimeout(() => {
        console.log('🎬 ¡INICIANDO VUELO CON TRACKING EN TIEMPO REAL!');
        
        animationId = requestAnimationFrame(animateFrame);
    }, 100); // Delay inicial reducido para inicio más rápido
    
    } // FIN de createAndAnimateClone
}; // FIN de flyAvatarToHeaderRealTime

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

console.log('✅ Avatar Flight Animation (Real-Time) cargado');
