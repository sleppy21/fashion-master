<?php
/**
 * CONFIGURACIÓN PRINCIPAL DE LA APLICACIÓN
 * Versión unificada - Puerto único 80
 */

// Incluir configuración unificada
require_once __DIR__ . '/unified_config.php';

// URLs y rutas (no redefinir si ya están definidas)
if (!defined('BASE_URL')) {
    // Detectar automáticamente el dominio para que funcione en cualquier hosting
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = '/fashion-master';
    define('BASE_URL', $protocol . '://' . $host . $path);
}
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . '/public/assets');
}

// Configuración de base de datos (no redefinir si ya está definida)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sleppystore');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// Configuración adicional específica del proyecto

// Configuración de sesión
define('SESSION_LIFETIME', 3600 * 24); // 24 horas
define('SESSION_NAME', 'fashion_store_session');

// Configuración de archivos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuración de email (para futuro)
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
define('CACHE_LIFETIME', 3600); // 1 hora

// Configuración de logs
define('LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB

// Configuración de seguridad
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hora
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_TIMEOUT', 900); // 15 minutos

// Rutas de directorios
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Configuración de la tienda
define('STORE_NAME', 'Fashion Store');
define('STORE_DESCRIPTION', 'Tu tienda de moda favorita');
define('STORE_KEYWORDS', 'moda, ropa, accesorios, tendencias');
define('STORE_EMAIL', 'info@fashionstore.com');
define('STORE_PHONE', '+51 123 456 789');
define('STORE_ADDRESS', 'Lima, Perú');

// Configuración de moneda
define('CURRENCY', 'PEN');
define('CURRENCY_SYMBOL', 'S/.');
define('CURRENCY_POSITION', 'before'); // before, after

// Configuración de envío
define('SHIPPING_FREE_THRESHOLD', 100);
define('SHIPPING_COST', 10);
define('TAX_RATE', 0.18); // 18% IGV

// Configuración de redes sociales
define('FACEBOOK_URL', '#');
define('TWITTER_URL', '#');
define('INSTAGRAM_URL', '#');
define('YOUTUBE_URL', '#');

// Configuración de APIs externas
define('GOOGLE_ANALYTICS_ID', '');
define('FACEBOOK_PIXEL_ID', '');

// Configuración de desarrollo
if(APP_ENV === 'development') {
    define('DEBUG_MODE', true);
    define('SHOW_ERRORS', true);
    define('LOG_QUERIES', true);
} else {
    define('DEBUG_MODE', false);
    define('SHOW_ERRORS', false);
    define('LOG_QUERIES', false);
}

// Funciones auxiliares de configuración
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

function isProduction() {
    return APP_ENV === 'production';
}

function isDevelopment() {
    return APP_ENV === 'development';
}
?>