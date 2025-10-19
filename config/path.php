<?php
/**
 * CONFIGURACIÓN DE RUTAS
 * Define la ruta base del proyecto para que funcione en local, ngrok y producción
 * Detecta automáticamente si está en subdirectorio o raíz
 */

// Incluir configuración unificada si no está cargada
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/unified_config.php';
}

// Función helper para generar URLs relativas
function url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

// Función helper para generar URLs absolutas (con protocolo y dominio)
// Compatible con localhost, ngrok, y cualquier dominio
function absolute_url($path = '') {
    // Detectar protocolo (http o https)
    $protocol = 'http';
    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    ) {
        $protocol = 'https';
    }
    
    // Obtener el host (funciona con localhost, ngrok, y dominios personalizados)
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    
    // Construir URL base completa
    $base_url = $protocol . '://' . $host;
    
    // Agregar el path si BASE_URL contiene subdirectorios
    if (defined('BASE_URL') && BASE_URL !== '') {
        // Si BASE_URL ya contiene el protocolo y host, usarlo directamente
        if (strpos(BASE_URL, 'http://') === 0 || strpos(BASE_URL, 'https://') === 0) {
            $base_url = BASE_URL;
        } else {
            // Si BASE_URL es solo un path, agregarlo al dominio
            $base_url .= BASE_URL;
        }
    }
    
    // Agregar el path específico
    if ($path) {
        $base_url .= '/' . ltrim($path, '/');
    }
    
    return $base_url;
}

// Función helper para assets
// Genera rutas a /assets/ que el .htaccess redirige a /public/assets/
function asset($path) {
    $base = defined('BASE_URL') ? BASE_URL : '';
    return $base . '/public/assets/' . ltrim($path, '/');
}
?>
