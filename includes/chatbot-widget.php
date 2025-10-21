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

// Función helper para URLs con protocolo seguro
if (!function_exists('url')) {
    function url($path = '') {
        $base = defined('BASE_URL') ? BASE_URL : '';
        
        // Detectar HTTPS correctamente (incluyendo túneles)
        $is_https = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $is_https = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $is_https = true;
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $host_lower = strtolower($_SERVER['HTTP_HOST']);
            if (strpos($host_lower, 'ngrok') !== false || 
                strpos($host_lower, 'serveo.net') !== false ||
                strpos($host_lower, 'trycloudflare.com') !== false ||
                strpos($host_lower, 'loca.lt') !== false) {
                $is_https = true;
            }
        }
        
        if ($is_https) {
            $base = str_replace('http://', 'https://', $base);
        }
        
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }
}
?>

<!-- CSS del Chatbot - Pre-cargado para evitar FOUC -->
<link rel="stylesheet" href="<?php echo url('public/assets/css/fashion-chat-modal.css'); ?>">

<!-- JavaScript del Chatbot - Crea botón y modal automáticamente -->
<script src="<?php echo url('proyecto-bot-main/src/fashion-chat-widget.js'); ?>"></script>

<script>
// Inicialización del chatbot
(function() {
    'use strict';
    
    function initChatbot() {

        
        // Inicializar
        try {
            window.fashionStoreChat = new FashionStoreChatWidget();
            
            // OCULTAR el botón flotante en móvil
            setTimeout(function() {
                const button = document.getElementById('fsChatButton') || 
                              document.querySelector('.fs-chat-button') ||
                              document.querySelector('.fs-chat-widget');
                
                if (button) {
                    // Ocultar en móvil con CSS
                    if (window.innerWidth <= 767) {
                        button.style.display = 'none';
                    }
                    
                    // Mostrar/ocultar según resize
                    window.addEventListener('resize', function() {
                        if (window.innerWidth <= 767) {
                            button.style.display = 'none';
                        } else {
                            button.style.display = 'block';
                        }
                    });
                    
                }
            }, 500);
            
        } catch (error) {
        }
    }
    
    // Manejador para abrir chatbot desde el menú móvil
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'open-chatbot-mobile' || e.target.closest('#open-chatbot-mobile'))) {
            e.preventDefault();
            
            // Cerrar offcanvas
            const offcanvas = document.querySelector('.offcanvas-menu-wrapper');
            const overlay = document.querySelector('.offcanvas-menu-overlay');
            if (offcanvas) offcanvas.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            
            // Esperar a que el chatbot esté listo
            setTimeout(function() {
                if (window.fashionStoreChat) {
                    
                    // Forzar creación de la interfaz si no existe
                    if (!window.fashionStoreChat.chatContainer) {
                        window.fashionStoreChat.createChatInterface().then(function() {
                            window.fashionStoreChat.showChatInterface();
                            window.fashionStoreChat.isOpen = true;
                            
                            // Enviar mensaje de bienvenida si es la primera vez
                            if (window.fashionStoreChat.messageHistory.length === 0) {
                                window.fashionStoreChat.addWelcomeMessage();
                            }
                        }).catch(function(error) {
                            alert('No se pudo abrir el asistente virtual. Por favor, intenta de nuevo.');
                        });
                    } else {
                        // Si ya existe, solo mostrar
                        window.fashionStoreChat.showChatInterface();
                        window.fashionStoreChat.isOpen = true;
                    }
                } else {
                    alert('El asistente virtual aún no está listo. Por favor, espera un momento.');
                }
            }, 350);
        }
    });
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbot);
    } else {
        initChatbot();
    }
})();
</script>
