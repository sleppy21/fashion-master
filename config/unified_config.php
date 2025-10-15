<?php
/**
 * Configuración unificada del proyecto Fashion Store
 * Puerto único: 80 (Apache)
 * AUTO-DETECCIÓN DE URL PARA CUALQUIER HOSTING
 */

// ===============================================
// AUTO-DETECCIÓN DE BASE_URL (Compatible con localhost, ngrok, hosting)
// ===============================================
if (!defined('BASE_URL')) {
    // Detectar protocolo (HTTP o HTTPS) - Mejorado para ngrok
    $protocol = 'http'; // Default
    
    // Método 1: HTTPS directo
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    }
    
    // Método 2: X-Forwarded-Proto (usado por proxies como ngrok)
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https';
    }
    
    // Método 3: Detectar ngrok por el host
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false) {
        $protocol = 'https';
    }
    
    // Método 4: Puerto 443
    if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
        $protocol = 'https';
    }
    
    // Detectar host
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Detectar path automáticamente
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = dirname($scriptName);
    
    // Si estamos en la raíz, no agregar path
    if ($scriptDir === '/' || $scriptDir === '\\') {
        $path = '';
    } else {
        // Remover el nombre del archivo y obtener solo el directorio
        $path = rtrim($scriptDir, '/\\');
        
        // Si el path contiene 'fashion-master', obtener solo hasta ese directorio
        if (strpos($path, 'fashion-master') !== false) {
            // Extraer solo hasta fashion-master (incluido)
            $pathParts = explode('/', trim($path, '/'));
            $index = array_search('fashion-master', $pathParts);
            if ($index !== false) {
                $pathParts = array_slice($pathParts, 0, $index + 1);
                $path = '/' . implode('/', $pathParts);
            }
        }
    }
    
    define('BASE_URL', $protocol . '://' . $host . $path);
}

// PROJECT_PATH ya no es necesario porque BASE_URL lo incluye
define('FULL_BASE_URL', BASE_URL); // Mantener compatibilidad

// API del Bot (puerto único 80)
define('BOT_API_URL', FULL_BASE_URL . '/proyecto-bot-main/api/bot_api.php');
define('BOT_HEALTH_URL', BOT_API_URL . '?action=health');
define('BOT_CHAT_URL', BOT_API_URL . '?action=chat');
define('BOT_SUGGESTIONS_URL', BOT_API_URL . '?action=suggestions');

// Configuración de la aplicación
define('APP_NAME', 'Fashion Store');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'development');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sleppystore');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de sesión
define('SESSION_LIFETIME', 3600 * 24);
define('SESSION_NAME', 'fashion_store_session');

// Configuración de archivos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuración de email
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_FROM', 'noreply@fashionstore.com');
define('MAIL_FROM_NAME', 'Fashion Store');

// Configuración de paginación
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Configuración de cache
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600);

// Configuración de logs
define('LOG_LEVEL', 'DEBUG');
define('LOG_MAX_SIZE', 10 * 1024 * 1024);

// Configuración de seguridad
define('CSRF_TOKEN_LIFETIME', 3600);
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_TIMEOUT', 900);

// Configuración del Bot
define('BOT_ENABLED', true);
define('BOT_MAX_RESPONSE_TIME', 30);
define('BOT_CACHE_TTL', 300);

// Assets y recursos
define('ASSETS_URL', FULL_BASE_URL . '/public/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/img');

// Rutas del bot
define('BOT_DATA_PATH', __DIR__ . '/../proyecto-bot-main/data');
define('BOT_CONTEXT_PATH', BOT_DATA_PATH . '/context');
?>