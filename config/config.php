<?php
/**
 * CONFIGURACIÓN PRINCIPAL DE LA APLICACIÓN
 * Versión unificada - Puerto único 80
 */

// Incluir configuración unificada (ya tiene BASE_URL, DB, y constantes básicas)
require_once __DIR__ . '/unified_config.php';

// ===============================================
// CONSTANTES ADICIONALES (no duplicadas)
// ===============================================

// URLs adicionales (ASSETS_URL ya está en unified_config.php)
// No se redefinen aquí

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