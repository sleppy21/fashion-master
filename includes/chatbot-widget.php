<?php
/**
 * CHATBOT WIDGET - Fashion Store
 * Integración simplificada del chatbot
 */

// Cargar configuración
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/unified_config.php';
}

// Función helper para URLs
if (!function_exists('url')) {
    function url($path = '') {
        $base = defined('BASE_URL') ? BASE_URL : '';
        
        // Detectar HTTPS
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                 || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        
        if ($is_https) {
            $base = str_replace('http://', 'https://', $base);
        }
        
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }
}
?>

<!-- CSS del Chatbot -->
<link rel="stylesheet" href="<?php echo url('public/assets/css/fashion-chat-modal.css'); ?>">

<!-- JavaScript del Chatbot -->
<script src="<?php echo url('proyecto-bot-main/src/fashion-chat-widget.js'); ?>"></script>

<script>
// Inicialización simplificada del chatbot
(function() {
    'use strict';
    
    function initChatbot() {
        try {
            if (typeof FashionStoreChatWidget !== 'undefined') {
                window.fashionStoreChat = new FashionStoreChatWidget();
                console.log('[Chatbot] ✅ Inicializado');
            }
        } catch (error) {
            console.error('[Chatbot] Error:', error);
        }
    }
    
    // Inicializar cuando esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbot);
    } else {
        initChatbot();
    }
})();
</script>
