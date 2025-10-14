<?php
/**
 * CHATBOT WIDGET - Fashion Store
 * 
 * Carga el chatbot completo del proyecto-bot-main que incluye:
 * - Botón flotante (se crea automáticamente)
 * - Modal de chat (lazy loading)
 * - CSS integrado (fashion-chat-modal.css)
 * - Backend API (bot_api.php)
 */

// Cargar configuración de rutas si no está cargada
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/unified_config.php';
}

// Función helper para URLs (por si no está definida)
if (!function_exists('url')) {
    function url($path = '') {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }
}
?>

<!-- CSS del Chatbot - Pre-cargado para evitar FOUC -->
<link rel="stylesheet" href="<?php echo url('public/assets/css/fashion-chat-modal.css'); ?>">

<!-- JavaScript del Chatbot - Crea botón y modal automáticamente -->
<script src="<?php echo url('proyecto-bot-main/src/fashion-chat-widget.js'); ?>"></script>

<script>
// Forzar inicialización del chatbot
(function() {
    'use strict';
    
    function initChatbot() {
        console.log('🤖 Iniciando Fashion Store Chatbot...');
        
        // Verificar si ya se inicializó
        if (window.fashionStoreChat) {
            console.log('✅ Chatbot ya inicializado');
            return;
        }
        
        // Verificar que la clase existe
        if (typeof FashionStoreChatWidget === 'undefined') {
            console.error('❌ FashionStoreChatWidget no está definido');
            return;
        }
        
        // Inicializar
        try {
            window.fashionStoreChat = new FashionStoreChatWidget();
            console.log('✅ Fashion Store Chatbot inicializado correctamente');
            
            // Verificar que el botón se creó
            setTimeout(function() {
                const button = document.getElementById('fsChatButton') || 
                              document.querySelector('.fs-chat-button') ||
                              document.querySelector('.fs-chat-widget');
                
                if (button) {
                    console.log('✅ Botón del chatbot creado correctamente');
                } else {
                    console.warn('⚠️ Botón del chatbot no encontrado en el DOM');
                }
            }, 1500);
            
        } catch (error) {
            console.error('❌ Error inicializando chatbot:', error);
        }
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbot);
    } else {
        // DOM ya está listo
        initChatbot();
    }
})();
</script>
