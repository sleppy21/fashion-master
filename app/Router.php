<?php
/**
 * SISTEMA DE RUTAS
 * Maneja el enrutamiento de URLs a controladores y métodos
 */

class Router {
    private static $routes = [];
    
    /**
     * Registrar ruta GET
     */
    public static function get($path, $callback) {
        self::$routes['GET'][$path] = $callback;
    }
    
    /**
     * Registrar ruta POST
     */
    public static function post($path, $callback) {
        self::$routes['POST'][$path] = $callback;
    }
    
    /**
     * Registrar ruta PUT
     */
    public static function put($path, $callback) {
        self::$routes['PUT'][$path] = $callback;
    }
    
    /**
     * Registrar ruta DELETE
     */
    public static function delete($path, $callback) {
        self::$routes['DELETE'][$path] = $callback;
    }
    
    /**
     * Resolver ruta actual
     */
    public static function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover barras al final excepto la raíz
        if($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }
        
        // Buscar ruta exacta primero
        if(isset(self::$routes[$method][$path])) {
            return self::executeCallback(self::$routes[$method][$path], []);
        }
        
        // Buscar rutas con parámetros
        foreach(self::$routes[$method] ?? [] as $route => $callback) {
            $params = self::matchRoute($route, $path);
            if($params !== false) {
                return self::executeCallback($callback, $params);
            }
        }
        
        // Ruta no encontrada
        self::handle404();
    }
    
    /**
     * Verificar si una ruta coincide con el path
     */
    private static function matchRoute($route, $path) {
        // Convertir parámetros de ruta {id} a regex
        $pattern = preg_replace('/\{(\w+)\}/', '(\w+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        if(preg_match($pattern, $path, $matches)) {
            array_shift($matches); // Remover la coincidencia completa
            return $matches;
        }
        
        return false;
    }
    
    /**
     * Ejecutar callback de ruta
     */
    private static function executeCallback($callback, $params = []) {
        if(is_string($callback)) {
            // Formato: "Controller@method"
            if(strpos($callback, '@') !== false) {
                list($controller, $method) = explode('@', $callback);
                
                // Incluir controlador
                $controller_file = "../app/controllers/{$controller}.php";
                if(file_exists($controller_file)) {
                    require_once $controller_file;
                    
                    if(class_exists($controller) && method_exists($controller, $method)) {
                        return call_user_func_array([$controller, $method], $params);
                    } else {
                        error_log("Método $method no encontrado en $controller");
                        self::handle500("Método no encontrado");
                        return;
                    }
                } else {
                    error_log("Controlador $controller no encontrado");
                    self::handle500("Controlador no encontrado");
                    return;
                }
            }
        } elseif(is_callable($callback)) {
            // Función anónima
            return call_user_func_array($callback, $params);
        }
        
        self::handle500("Callback inválido");
    }
    
    /**
     * Manejar error 404
     */
    private static function handle404() {
        http_response_code(404);
        
        $data = [
            'page_title' => 'Página no encontrada',
            'message' => 'La página que buscas no existe'
        ];
        
        if(file_exists('../app/views/errors/404.php')) {
            require_once '../app/views/errors/404.php';
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
        }
    }
    
    /**
     * Manejar error 500
     */
    private static function handle500($message = 'Error interno del servidor') {
        http_response_code(500);
        
        $data = [
            'page_title' => 'Error del servidor',
            'message' => $message
        ];
        
        if(file_exists('../app/views/errors/500.php')) {
            require_once '../app/views/errors/500.php';
        } else {
            echo "<h1>500 - Error del servidor</h1><p>$message</p>";
        }
    }
    
    /**
     * Redirigir a URL
     */
    public static function redirect($url, $status_code = 302) {
        http_response_code($status_code);
        header("Location: $url");
        exit;
    }
    
    /**
     * Obtener URL base
     */
    public static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return "$protocol://$host";
    }
    
    /**
     * Generar URL
     */
    public static function url($path = '') {
        return self::getBaseUrl() . '/' . ltrim($path, '/');
    }
}

/**
 * DEFINICIÓN DE RUTAS
 */

// Página principal
Router::get('/', function() {
    require_once '../app/controllers/ProductController.php';
    require_once '../config/conexion.php';
    
    try {
        // Obtener productos destacados de la base de datos
        $productos_destacados = executeQuery("
            SELECT p.*, c.nombre_categoria
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            WHERE (p.destacado_producto = 1 OR p.id_producto <= 8)
            AND p.status_producto = 1
            AND p.estado = 'activo'
            AND p.stock_actual_producto > 0
            ORDER BY p.fecha_agregado_producto DESC
            LIMIT 8
        ");
        
        // Obtener productos en oferta
        $productos_oferta = executeQuery("
            SELECT p.*, c.nombre_categoria
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            WHERE p.precio_oferta IS NOT NULL 
            AND p.precio_oferta < p.precio_producto
            AND p.status_producto = 1
            AND p.estado = 'activo'
            AND p.stock_actual_producto > 0
            ORDER BY ((p.precio_producto - p.precio_oferta) / p.precio_producto) DESC
            LIMIT 6
        ");
        
        // Si no hay productos en oferta, obtener algunos productos aleatorios
        if(empty($productos_oferta)) {
            $productos_oferta = executeQuery("
                SELECT p.*, c.nombre_categoria
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                WHERE p.status_producto = 1
                AND p.estado = 'activo'
                AND p.stock_actual_producto > 0
                ORDER BY RAND()
                LIMIT 6
            ");
        }
        
        $data = [
            'page_title' => 'Inicio - Fashion Store',
            'productos_destacados' => $productos_destacados,
            'productos_oferta' => $productos_oferta
        ];
        
    } catch(Exception $e) {
        // En caso de error, usar datos vacíos
        error_log("Error cargando productos para home: " . $e->getMessage());
        $data = [
            'page_title' => 'Inicio - Fashion Store',
            'productos_destacados' => [],
            'productos_oferta' => []
        ];
    }
    
    require_once '../app/views/home/index.php';
});

// Autenticación
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@processLogin');
Router::post('/logout', 'AuthController@logout');

// Productos y tienda
Router::get('/shop', 'ProductController@showShop');
Router::get('/product/{id}', 'ProductController@showDetails');

// Búsqueda AJAX
Router::get('/api/search', 'ProductController@searchAjax');
Router::get('/api/products/category/{id}', 'ProductController@getByCategory');
Router::get('/api/products/{id}/stock', 'ProductController@checkStock');
Router::get('/api/products/filter/price', 'ProductController@filterByPrice');

// Carrito
Router::get('/cart', 'CartController@showCart');
Router::post('/api/cart/add', 'CartController@addItem');
Router::post('/api/cart/update', 'CartController@updateQuantity');
Router::post('/api/cart/remove', 'CartController@removeItem');
Router::post('/api/cart/clear', 'CartController@clearCart');
Router::get('/api/cart/count', 'CartController@getCartCount');
Router::get('/api/cart/summary', 'CartController@getCartSummary');

// Páginas estáticas (manteniendo compatibilidad)
Router::get('/contact', function() {
    $data = ['page_title' => 'Contacto'];
    require_once '../app/views/pages/contact.php';
});

Router::get('/about', function() {
    $data = ['page_title' => 'Acerca de'];
    require_once '../app/views/pages/about.php';
});

Router::get('/blog', function() {
    $data = ['page_title' => 'Blog'];
    require_once '../app/views/pages/blog.php';
});

Router::get('/checkout', function() {
    require_once '../app/controllers/AuthController.php';
    AuthController::requireAuth();
    
    $data = ['page_title' => 'Checkout'];
    require_once '../app/views/pages/checkout.php';
});
?>