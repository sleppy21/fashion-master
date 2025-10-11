<?php
/**
 * BOOTSTRAP DE LA APLICACIÓN
 * Inicializa la aplicación y maneja las rutas
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
session_start();

// Zona horaria
date_default_timezone_set('America/Lima');

// Incluir configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';

// Autoloader simple para modelos y controladores
spl_autoload_register(function($class) {
    $directories = [
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/middleware/'
    ];
    
    foreach($directories as $directory) {
        $file = $directory . $class . '.php';
        if(file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Incluir router
require_once __DIR__ . '/Router.php';

// Función helper para incluir vistas
function view($view, $data = []) {
    extract($data);
    $view_file = __DIR__ . "/views/{$view}.php";
    
    if(file_exists($view_file)) {
        require_once $view_file;
    } else {
        throw new Exception("Vista no encontrada: {$view}");
    }
}

// Función helper para redireccionar
function redirect($url, $status_code = 302) {
    Router::redirect($url, $status_code);
}

// Función helper para obtener URL
function url($path = '') {
    return Router::url($path);
}

// Función helper para datos de sesión
function session($key = null, $default = null) {
    if($key === null) {
        return $_SESSION;
    }
    
    return $_SESSION[$key] ?? $default;
}

// Función helper para obtener usuario actual
function auth() {
    require_once __DIR__ . '/controllers/AuthController.php';
    return AuthController::getCurrentUser();
}

// Función helper para verificar autenticación
function isAuth() {
    require_once __DIR__ . '/controllers/AuthController.php';
    return AuthController::isLoggedIn();
}

// Función helper para verificar rol
function hasRole($role) {
    require_once __DIR__ . '/controllers/AuthController.php';
    return AuthController::hasRole($role);
}

// Función helper para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Función helper para formatear precio
function formatPrice($price) {
    return 'S/. ' . number_format($price, 2);
}

// Función helper para formatear fecha
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Función helper para asset URLs
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

// Función helper para debugging (solo en desarrollo)
function dd($var) {
    if($_SERVER['SERVER_NAME'] === 'localhost') {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        die();
    }
}

// Middleware para verificar conexión a base de datos
try {
    // Probar conexión
    $test_connection = new Database();
    $test_connection = null;
} catch(Exception $e) {
    error_log("Error de conexión a BD: " . $e->getMessage());
    
    http_response_code(503);
    $data = [
        'page_title' => 'Servicio no disponible',
        'message' => 'El sitio está temporalmente fuera de servicio. Intente más tarde.'
    ];
    
    if(file_exists(__DIR__ . '/views/errors/503.php')) {
        require_once __DIR__ . '/views/errors/503.php';
    } else {
        echo "<h1>503 - Servicio no disponible</h1>";
    }
    exit;
}

// Manejar errores no capturados
set_exception_handler(function($exception) {
    error_log("Excepción no capturada: " . $exception->getMessage());
    
    http_response_code(500);
    
    if($_SERVER['SERVER_NAME'] === 'localhost') {
        // Mostrar detalles en desarrollo
        echo "<h1>Error 500</h1>";
        echo "<p><strong>Mensaje:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>Archivo:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Línea:</strong> " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        // Mensaje genérico en producción
        $data = [
            'page_title' => 'Error del servidor',
            'message' => 'Ha ocurrido un error inesperado'
        ];
        
        if(file_exists(__DIR__ . '/views/errors/500.php')) {
            require_once __DIR__ . '/views/errors/500.php';
        } else {
            echo "<h1>500 - Error del servidor</h1>";
        }
    }
});

// Resolver ruta actual
try {
    Router::resolve();
} catch(Exception $e) {
    error_log("Error en router: " . $e->getMessage());
    throw $e;
}
?>