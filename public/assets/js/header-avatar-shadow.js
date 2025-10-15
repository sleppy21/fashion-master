/**
 * HEADER AVATAR SHADOW MANAGER
 * Gestiona el shadow dinámico del avatar en el header
 * Se carga al iniciar la página y se actualiza en tiempo real
 */

(function() {
    'use strict';

    console.log('🎨 Header Avatar Shadow Manager cargado');

    /**
     * Aplicar shadow al avatar del header usando el RGB proporcionado
     */
    function applyHeaderShadowWithColor(r, g, b) {
        const headerAvatar = document.querySelector('.header-user-avatar .avatar-image');
        if (!headerAvatar) return false;

        const shadowValue = `0 4px 12px rgba(${r}, ${g}, ${b}, 0.4), 0 8px 24px rgba(${r}, ${g}, ${b}, 0.3), 0 0 30px rgba(${r}, ${g}, ${b}, 0.2)`;
        
        // Remover cualquier box-shadow previo del style attribute
        const currentStyle = headerAvatar.getAttribute('style') || '';
        const styleWithoutShadow = currentStyle.replace(/box-shadow:[^;]+;?/gi, '').trim();
        
        // Aplicar el nuevo shadow con !important
        headerAvatar.setAttribute('style', 
            `${styleWithoutShadow}${styleWithoutShadow ? '; ' : ''}box-shadow: ${shadowValue} !important;`
        );
        
        // Guardar el color en data attribute
        headerAvatar.dataset.shadowColor = `${r}, ${g}, ${b}`;
        
        return true;
    }

    /**
     * Aplicar shadow al avatar del header
     */
    function applyHeaderAvatarShadow() {
        const headerAvatar = document.querySelector('.header-user-avatar .avatar-image');
        
        if (!headerAvatar) {
            console.log('⚠️ Avatar del header no encontrado');
            return;
        }

        // Intentar obtener el color del data-shadow-color
        const shadowColor = headerAvatar.dataset.shadowColor;
        
        if (shadowColor) {
            const rgb = shadowColor.split(',').map(n => parseInt(n.trim()));
            
            if (rgb.length === 3) {
                const [r, g, b] = rgb;
                applyHeaderShadowWithColor(r, g, b);
                console.log(`✅ Shadow del header restaurado: rgb(${r}, ${g}, ${b})`);
                return;
            }
        }
        
        // Si no hay data-shadow-color, intentar leer del avatar del profile
        const profileAvatar = document.querySelector('.profile-avatar .avatar-image');
        
        if (profileAvatar && profileAvatar.dataset.shadowColor) {
            const profileShadowColor = profileAvatar.dataset.shadowColor;
            const rgb = profileShadowColor.split(',').map(n => parseInt(n.trim()));
            
            if (rgb.length === 3) {
                const [r, g, b] = rgb;
                applyHeaderShadowWithColor(r, g, b);
                console.log(`✅ Shadow del header copiado del perfil: rgb(${r}, ${g}, ${b})`);
                return;
            }
        }
        
        console.log('ℹ️ No se encontró color guardado');
    }

    /**
     * Escuchar eventos personalizados
     */
    function setupEventListeners() {
        // Cuando se actualiza el shadow del avatar (INMEDIATAMENTE al calcular)
        document.addEventListener('avatarShadowUpdated', function(e) {
            console.log('🎨 avatarShadowUpdated recibido en header:', e.detail);
            
            const { r, g, b } = e.detail;
            
            if (applyHeaderShadowWithColor(r, g, b)) {
                console.log(`✨ Shadow del header actualizado INMEDIATAMENTE: rgb(${r}, ${g}, ${b})`);
            }
        });

        // Cuando termina la animación de vuelo (CONFIRMACIÓN)
        document.addEventListener('avatarColorUpdated', function(e) {
            console.log('🎯 avatarColorUpdated recibido en header:', e.detail);
            
            const { r, g, b } = e.detail;
            
            if (applyHeaderShadowWithColor(r, g, b)) {
                console.log(`✅ Shadow del header CONFIRMADO: rgb(${r}, ${g}, ${b})`);
            }
        });
    }

    /**
     * Inicializar
     */
    function init() {
        console.log('🚀 Inicializando Header Avatar Shadow Manager...');
        
        // Aplicar shadow guardado al cargar la página
        applyHeaderAvatarShadow();
        
        // Configurar listeners de eventos
        setupEventListeners();
        
        console.log('✅ Header Avatar Shadow Manager inicializado');
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
