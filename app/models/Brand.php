<?php
/**
 * MODELO DE PRODUCTO
 * Maneja todas las operaciones de datos relacionadas con productos
 */

class Product {
    
    /**
     * Buscar producto por ID
     */
    public static function findById($id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT 
                    p.*, 
                    c.nombre_categoria, 
                    c.descripcion_categoria,
                    m.nombre_marca
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                  LEFT JOIN marca m ON p.id_marca = m.id_marca
                  WHERE p.id_producto = ?";
        
        $result = executeQuery($query, [$id]);
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Obtener todos los productos con filtros
     */
    public static function getFiltered($filters = []) {
        require_once '../config/conexion.php';
        
        $conditions = [];
        $params = [];
        
        // Construir WHERE dinámicamente
        if(!empty($filters['categoria'])) {
            $conditions[] = "p.id_categoria = ?";
            $params[] = $filters['categoria'];
        }
        
        if(isset($filters['precio_min']) && $filters['precio_min'] !== '') {
            $conditions[] = "p.precio_producto >= ?";
            $params[] = $filters['precio_min'];
        }
        
        if(isset($filters['precio_max']) && $filters['precio_max'] !== '') {
            $conditions[] = "p.precio_producto <= ?";
            $params[] = $filters['precio_max'];
        }
        
        if(!empty($filters['busqueda'])) {
            $conditions[] = "(p.nombre_producto LIKE ? OR p.descripcion_producto LIKE ?)";
            $search_term = "%" . $filters['busqueda'] . "%";
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Base query con campos correctos del schema
        $query = "SELECT 
                    p.*,
                    c.nombre_categoria,
                    m.nombre_marca
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                  LEFT JOIN marca m ON p.id_marca = m.id_marca";
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Ordenamiento
        switch($filters['orden'] ?? 'fecha_desc') {
            case 'precio_asc':
                $query .= " ORDER BY p.precio_producto ASC";
                break;
            case 'precio_desc':
                $query .= " ORDER BY p.precio_producto DESC";
                break;
            case 'nombre_asc':
                $query .= " ORDER BY p.nombre_producto ASC";
                break;
            case 'nombre_desc':
                $query .= " ORDER BY p.nombre_producto DESC";
                break;
            default:
                $query .= " ORDER BY p.fecha_creacion_producto DESC";
        }
        
        // Paginación
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, (int)($filters['limit'] ?? 12));
        $offset = ($page - 1) * $limit;
        
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return executeQuery($query, $params);
    }
    
    /**
     * Contar productos con filtros
     */
    public static function countFiltered($filters = []) {
        require_once '../config/conexion.php';
        
        $conditions = [];
        $params = [];
        
        if(!empty($filters['categoria'])) {
            $conditions[] = "id_categoria = ?";
            $params[] = $filters['categoria'];
        }
        
        if(isset($filters['precio_min']) && $filters['precio_min'] !== '') {
            $conditions[] = "precio_producto >= ?";
            $params[] = $filters['precio_min'];
        }
        
        if(isset($filters['precio_max']) && $filters['precio_max'] !== '') {
            $conditions[] = "precio_producto <= ?";
            $params[] = $filters['precio_max'];
        }
        
        if(!empty($filters['busqueda'])) {
            $conditions[] = "(nombre_producto LIKE ? OR descripcion_producto LIKE ?)";
            $search_term = "%" . $filters['busqueda'] . "%";
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $query = "SELECT COUNT(*) as total FROM producto";
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $result = executeQuery($query, $params);
        return !empty($result) ? (int)$result[0]['total'] : 0;
    }
    
    /**
     * Buscar productos
     */
    public static function search($term, $page = 1, $limit = 10) {
        require_once '../config/conexion.php';
        
        $offset = ($page - 1) * $limit;
        $search_term = "%$term%";
        
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.nombre_producto LIKE ? 
                  OR p.descripcion_producto LIKE ?
                  ORDER BY p.nombre_producto ASC 
                  LIMIT ? OFFSET ?";
        
        return executeQuery($query, [$search_term, $search_term, (int)$limit, (int)$offset]);
    }
    
    /**
     * Obtener productos por categoría
     */
    public static function getByCategory($categoria_id, $limit = null) {
        require_once '../config/conexion.php';
        
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                  WHERE p.id_categoria = ? 
                  ORDER BY p.fecha_creacion_producto DESC";
        $params = [$categoria_id];
        
        if($limit) {
            $query .= " LIMIT ?";
            $params[] = (int)$limit;
        }
        
        return executeQuery($query, $params);
    }
    
    /**
     * Obtener productos relacionados
     */
    public static function getRelated($categoria_id, $exclude_id, $limit = 4) {
        require_once '../config/conexion.php';
        
        $query = "SELECT * FROM producto 
                  WHERE id_categoria = ? AND id_producto != ? 
                  ORDER BY RAND() 
                  LIMIT ?";
        
        return executeQuery($query, [$categoria_id, $exclude_id, (int)$limit]);
    }
    
    /**
     * Obtener productos destacados
     */
    public static function getFeatured($limit = 8) {
        require_once '../config/conexion.php';
        
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.destacado_producto = 1 
                  ORDER BY p.fecha_creacion_producto DESC 
                  LIMIT ?";
        
        return executeQuery($query, [(int)$limit]);
    }
    
    /**
     * Obtener productos en oferta
     */
    public static function getOnSale($limit = 6) {
        require_once '../config/conexion.php';
        
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.precio_oferta IS NOT NULL AND p.precio_oferta < p.precio_producto 
                  ORDER BY ((p.precio_producto - p.precio_oferta) / p.precio_producto) DESC 
                  LIMIT ?";
        
        return executeQuery($query, [(int)$limit]);
    }
    
    /**
     * Obtener productos por rango de precio
     */
    public static function getByPriceRange($min, $max, $categoria = null) {
        require_once '../config/conexion.php';
        
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM producto p 
                  LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                  WHERE p.precio_producto BETWEEN ? AND ?";
        
        $params = [$min, $max];
        
        if($categoria) {
            $query .= " AND p.id_categoria = ?";
            $params[] = $categoria;
        }
        
        $query .= " ORDER BY p.precio_producto ASC";
        
        return executeQuery($query, $params);
    }
    
    /**
     * Obtener imágenes del producto (si hay tabla product_images)
     */
    public static function getImages($producto_id) {
        require_once '../config/conexion.php';
        
        // Verificar si existe tabla product_images
        $tables = executeQuery("SHOW TABLES LIKE 'product_images'");
        
        if(empty($tables)) {
            // Si no hay tabla de imágenes, usar imagen principal
            $producto = self::findById($producto_id);
            return $producto ? [$producto['imagen_producto']] : [];
        }
        
        $query = "SELECT ruta_imagen FROM product_images WHERE id_producto = ? ORDER BY orden ASC";
        $result = executeQuery($query, [$producto_id]);
        
        return array_column($result, 'ruta_imagen');
    }
    
    /**
     * Crear producto
     */
    public static function create($data) {
        require_once '../config/conexion.php';
        
        $query = "INSERT INTO producto (
            nombre_producto, 
            codigo,
            descripcion_producto, 
            precio_producto, 
            descuento_porcentaje_producto,
            stock_actual_producto, 
            id_categoria,
            id_marca,
            genero_producto,
            en_oferta_producto,
            imagen_producto,
            url_imagen_producto,
            status_producto,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nombre'] ?? null,
            $data['codigo'] ?? null,
            $data['descripcion'] ?? null,
            $data['precio'] ?? 0,
            $data['descuento'] ?? 0,
            $data['stock'] ?? 0,
            $data['categoria'] ?? null,
            $data['marca'] ?? null,
            $data['genero'] ?? 'Unisex',
            $data['en_oferta'] ?? 0,
            $data['imagen'] ?? 'default-product.png',
            $data['url_imagen'] ?? '',
            $data['status'] ?? 1,
            $data['estado'] ?? 'activo'
        ];
    
        return executeNonQuery($query, $params);
    }
    
    /**
     * Actualizar producto
     */
    public static function update($id, $data) {
        require_once '../config/conexion.php';
        
        $fields = [];
        $params = [];
        
        if(isset($data['nombre'])) {
            $fields[] = "nombre_producto = ?";
            $params[] = $data['nombre'];
        }
        if(isset($data['descripcion'])) {
            $fields[] = "descripcion_producto = ?";
            $params[] = $data['descripcion'];
        }
        if(isset($data['precio'])) {
            $fields[] = "precio_producto = ?";
            $params[] = $data['precio'];
        }
        if(isset($data['precio_oferta'])) {
            $fields[] = "precio_oferta = ?";
            $params[] = $data['precio_oferta'];
        }
        if(isset($data['stock'])) {
            $fields[] = "stock_actual_producto = ?";
            $params[] = $data['stock'];
        }
        if(isset($data['categoria'])) {
            $fields[] = "id_categoria = ?";
            $params[] = $data['categoria'];
        }
        if(isset($data['imagen'])) {
            $fields[] = "imagen_producto = ?";
            $params[] = $data['imagen'];
        }
        if(isset($data['destacado'])) {
            $fields[] = "destacado_producto = ?";
            $params[] = $data['destacado'];
        }
        if(isset($data['status'])) {
            $fields[] = "status_producto = ?";
            $params[] = $data['status'];
        }
        if(isset($data['estado'])) {
            $fields[] = "estado = ?";
            $params[] = $data['estado'];
        }
        
        if(empty($fields)) {
            return false;
        }
        
        $query = "UPDATE producto SET " . implode(", ", $fields) . " WHERE id_producto = ?";
        $params[] = $id;
        
        return executeNonQuery($query, $params);
    }
    
    /**
     * Eliminar producto
     */
    public static function delete($id) {
        require_once '../config/conexion.php';
        
        $query = "DELETE FROM producto WHERE id_producto = ?";
        return executeNonQuery($query, [$id]);
    }
    
    /**
     * Actualizar stock (incrementa o decrementa según cantidad, cantidad puede ser negativa)
     */
    public static function updateStock($id, $cantidad) {
        require_once '../config/conexion.php';
        
        $query = "UPDATE producto SET stock_actual_producto = stock_actual_producto + ? WHERE id_producto = ?";
        return executeNonQuery($query, [$cantidad, $id]);
    }
}
?>
