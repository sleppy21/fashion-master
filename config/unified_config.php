<?php
/**
 * Configuración unificada del proyecto Fashion Store
 * Puerto único: 80 (Apache)
 */

// URLs y configuración principal
define('BASE_URL', 'http://localhost');
define('PROJECT_PATH', '/fashion-master');
define('FULL_BASE_URL', BASE_URL . PROJECT_PATH);

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