<?php
/**
 * ShopController
 * Controlador para la página de tienda
 * Maneja filtros, búsqueda y paginación de productos
 */

class ShopController {
    
    /**
     * Procesar datos de la página shop
     */
    public static function index() {
        // Verificar usuario logueado
        $usuario_logueado = self::getLoggedUser();
        
        // Obtener contadores
        $counters = self::getCounters($usuario_logueado);
        
        // Obtener favoritos del usuario
        $favoritos_usuario = self::getUserFavorites($usuario_logueado);
        
        // Obtener productos en carrito del usuario
        $carrito_usuario = self::getUserCart($usuario_logueado);
        
        // Procesar filtros de URL
        $filters = self::getFiltersFromURL();
        
        // Obtener productos filtrados
        $productos = self::getFilteredProducts($filters);
        
        // Obtener datos para filtros
        $categorias = self::getCategories();
        $marcas = self::getBrands();
        
        // IDs de productos favoritos
        $favoritos_ids = array_column($favoritos_usuario, 'id_producto');
        
        // IDs de productos en carrito
        $carrito_ids = array_column($carrito_usuario, 'id_producto');
        
        return [
            'usuario_logueado' => $usuario_logueado,
            'cart_count' => $counters['cart_count'],
            'favorites_count' => $counters['favorites_count'],
            'notifications_count' => $counters['notifications_count'],
            'favoritos_usuario' => $favoritos_usuario,
            'favoritos_ids' => $favoritos_ids,
            'carrito_ids' => $carrito_ids,
            'filters' => $filters,
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas
        ];
    }
    
    /**
     * Obtener usuario logueado
     */
    private static function getLoggedUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $resultado = executeQuery(
                "SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1",
                [$_SESSION['user_id']]
            );
            return ($resultado && count($resultado) > 0) ? $resultado[0] : null;
        } catch(Exception $e) {
            error_log("Error al verificar usuario: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener contadores de carrito, favoritos y notificaciones
     */
    private static function getCounters($usuario_logueado) {
        $counters = [
            'cart_count' => 0,
            'favorites_count' => 0,
            'notifications_count' => 0
        ];
        
        if (!$usuario_logueado) {
            return $counters;
        }
        
        try {
            // Carrito
            $cart = executeQuery(
                "SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?",
                [$usuario_logueado['id_usuario']]
            );
            $counters['cart_count'] = $cart[0]['total'] ?? 0;
            
            // Favoritos
            $favorites = executeQuery(
                "SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?",
                [$usuario_logueado['id_usuario']]
            );
            $counters['favorites_count'] = $favorites[0]['total'] ?? 0;
            
            // Notificaciones
            $notifications = executeQuery(
                "SELECT COUNT(*) as total FROM notificacion 
                 WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'",
                [$usuario_logueado['id_usuario']]
            );
            $counters['notifications_count'] = $notifications[0]['total'] ?? 0;
            
        } catch(Exception $e) {
            error_log("Error al obtener contadores: " . $e->getMessage());
        }
        
        return $counters;
    }
    
    /**
     * Obtener favoritos del usuario
     */
    private static function getUserFavorites($usuario_logueado) {
        if (!$usuario_logueado) {
            return [];
        }
        
        try {
            $resultado = executeQuery("
                SELECT p.id_producto, p.nombre_producto, p.precio_producto, 
                       p.url_imagen_producto,
                       COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto
                FROM favorito f
                INNER JOIN producto p ON f.id_producto = p.id_producto
                WHERE f.id_usuario = ? AND p.status_producto = 1
                ORDER BY f.fecha_agregado_favorito DESC
            ", [$usuario_logueado['id_usuario']]);
            
            return $resultado ?? [];
        } catch(Exception $e) {
            error_log("Error al obtener favoritos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener productos en carrito del usuario
     */
    private static function getUserCart($usuario_logueado) {
        if (!$usuario_logueado) {
            return [];
        }
        
        try {
            $resultado = executeQuery(
                "SELECT id_producto FROM carrito WHERE id_usuario = ?",
                [$usuario_logueado['id_usuario']]
            );
            return $resultado ?? [];
        } catch(Exception $e) {
            error_log("Error al obtener carrito: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener filtros desde URL
     */
    private static function getFiltersFromURL() {
        return [
            'categoria' => isset($_GET['c']) ? intval($_GET['c']) : (isset($_GET['categoria']) ? intval($_GET['categoria']) : null),
            'genero' => isset($_GET['g']) ? $_GET['g'] : (isset($_GET['genero']) ? $_GET['genero'] : null),
            'marca' => isset($_GET['m']) ? intval($_GET['m']) : (isset($_GET['marca']) ? intval($_GET['marca']) : null),
            'precio_min' => isset($_GET['pmin']) ? floatval($_GET['pmin']) : (isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : 0),
            'precio_max' => isset($_GET['pmax']) ? floatval($_GET['pmax']) : (isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 10000),
            'buscar' => isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['buscar']) ? trim($_GET['buscar']) : ''),
            'ordenar' => isset($_GET['sort']) ? $_GET['sort'] : 'newest'
        ];
    }
    
    /**
     * Obtener productos filtrados
     */
    private static function getFilteredProducts($filters) {
        $query = "
            SELECT p.id_producto, p.nombre_producto, p.precio_producto,
                   p.codigo, p.descripcion_producto,
                   COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
                   p.genero_producto, p.en_oferta_producto, p.stock_actual_producto,
                   p.url_imagen_producto,
                   COALESCE(m.nombre_marca, 'Sin marca') as nombre_marca, 
                   COALESCE(c.nombre_categoria, 'General') as nombre_categoria,
                   c.id_categoria, m.id_marca,
                   COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
                   COUNT(r.id_resena) as total_resenas
            FROM producto p
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
            WHERE p.status_producto = 1 AND p.estado = 'activo'
        ";
        
        $params = [];
        
        // Aplicar filtros
        if ($filters['categoria']) {
            $query .= " AND p.id_categoria = ?";
            $params[] = $filters['categoria'];
        }
        
        if ($filters['genero'] && $filters['genero'] !== 'all') {
            $query .= " AND p.genero_producto = ?";
            $params[] = $filters['genero'];
        }
        
        if ($filters['marca']) {
            $query .= " AND p.id_marca = ?";
            $params[] = $filters['marca'];
        }
        
        if ($filters['precio_min'] > 0 || $filters['precio_max'] < 10000) {
            $query .= " AND p.precio_producto BETWEEN ? AND ?";
            $params[] = $filters['precio_min'];
            $params[] = $filters['precio_max'];
        }
        
        if ($filters['buscar']) {
            $query .= " AND (p.nombre_producto LIKE ? OR p.descripcion_producto LIKE ? OR m.nombre_marca LIKE ?)";
            $buscar_param = '%' . $filters['buscar'] . '%';
            $params[] = $buscar_param;
            $params[] = $buscar_param;
            $params[] = $buscar_param;
        }
        
        $query .= " GROUP BY p.id_producto, p.nombre_producto, p.precio_producto, p.codigo, 
                    p.descripcion_producto, p.descuento_porcentaje_producto, p.genero_producto, 
                    p.en_oferta_producto, p.stock_actual_producto, p.url_imagen_producto,
                    m.nombre_marca, c.nombre_categoria, c.id_categoria, m.id_marca";
        
        // Ordenamiento
        $query .= self::getOrderByClause($filters['ordenar']);
        
        try {
            $resultado = executeQuery($query, $params);
            return $resultado ?? [];
        } catch(Exception $e) {
            error_log("Error al obtener productos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener cláusula ORDER BY según filtro
     */
    private static function getOrderByClause($ordenar) {
        switch($ordenar) {
            case 'price_asc':
                return " ORDER BY (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) ASC";
            case 'price_desc':
                return " ORDER BY (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) DESC";
            case 'name_asc':
                return " ORDER BY p.nombre_producto ASC";
            case 'name_desc':
                return " ORDER BY p.nombre_producto DESC";
            case 'rating':
                return " ORDER BY calificacion_promedio DESC, total_resenas DESC";
            case 'newest':
            default:
                return " ORDER BY p.id_producto DESC";
        }
    }
    
    /**
     * Obtener categorías activas
     */
    private static function getCategories() {
        try {
            $resultado = executeQuery(
                "SELECT id_categoria, nombre_categoria 
                 FROM categoria 
                 WHERE status_categoria = 1 
                 ORDER BY nombre_categoria ASC"
            );
            return $resultado ?? [];
        } catch(Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener marcas activas
     */
    private static function getBrands() {
        try {
            $resultado = executeQuery(
                "SELECT id_marca, nombre_marca, url_imagen_marca 
                 FROM marca 
                 WHERE status_marca = 1 
                 ORDER BY nombre_marca ASC"
            );
            return $resultado ?? [];
        } catch(Exception $e) {
            error_log("Error al obtener marcas: " . $e->getMessage());
            return [];
        }
    }
}
