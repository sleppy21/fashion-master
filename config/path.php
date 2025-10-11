<?php
/**
 * CONFIGURACIÓN DE RUTAS
 * Define la ruta base del proyecto para que funcione en local y producción
 */

// Detectar si estamos en local o producción
$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = (strpos($http_host, 'localhost') !== false || 
             strpos($http_host, '127.0.0.1') !== false);

// Definir ruta base según el entorno
if ($is_local) {
    // En local: /fashion-master/
    define('BASE_URL', '/fashion-master');
} else {
    // En producción: raíz del dominio
    define('BASE_URL', '');
}

// Función helper para generar URLs relativas
function url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

// Función helper para generar URLs absolutas (con http://)
function absolute_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host . url($path);
}

// Función helper para assets
function asset($path) {
    return BASE_URL . '/public/assets/' . ltrim($path, '/');
}
?>
