<?php
/**
 * MODELO DE CARRITO
 * Maneja todas las operaciones del carrito de compras
 */

class Cart {
    
    /**
     * Obtener items del carrito
     */
    public static function getItems($user_id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT c.*, p.nombre_producto, p.precio_producto, p.imagen_producto, p.stock_producto,
                  (c.cantidad_carrito * p.precio_producto) as subtotal
                  FROM carrito c
                  INNER JOIN producto p ON c.id_producto = p.id_producto
                  WHERE c.id_usuario = ?
                  ORDER BY c.fecha_agregado_carrito DESC";
        
        return executeQuery($query, [$user_id]);
    }
    
    /**
     * Agregar item al carrito
     */
    public static function addItem($user_id, $producto_id, $cantidad) {
        require_once '../config/conexion.php';
        
        // Verificar si el producto ya está en el carrito
        $existing = executeQuery(
            "SELECT * FROM carrito WHERE id_usuario = ? AND id_producto = ?", 
            [$user_id, $producto_id]
        );
        
        if(!empty($existing)) {
            // Actualizar cantidad si ya existe
            $nueva_cantidad = $existing[0]['cantidad_carrito'] + $cantidad;
            $query = "UPDATE carrito SET cantidad_carrito = ?, fecha_agregado_carrito = NOW() 
                      WHERE id_usuario = ? AND id_producto = ?";
            
            return executeNonQuery($query, [$nueva_cantidad, $user_id, $producto_id]);
        } else {
            // Agregar nuevo item
            $query = "INSERT INTO carrito (id_usuario, id_producto, cantidad_carrito, fecha_agregado_carrito) 
                      VALUES (?, ?, ?, NOW())";
            
            return executeNonQuery($query, [$user_id, $producto_id, $cantidad]);
        }
    }
    
    /**
     * Actualizar cantidad de un item
     */
    public static function updateQuantity($user_id, $producto_id, $cantidad) {
        require_once '../config/conexion.php';
        
        if($cantidad <= 0) {
            return self::removeItem($user_id, $producto_id);
        }
        
        $query = "UPDATE carrito SET cantidad_carrito = ? 
                  WHERE id_usuario = ? AND id_producto = ?";
        
        return executeNonQuery($query, [$cantidad, $user_id, $producto_id]);
    }
    
    /**
     * Remover item del carrito
     */
    public static function removeItem($user_id, $producto_id) {
        require_once '../config/conexion.php';
        
        $query = "DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?";
        
        return executeNonQuery($query, [$user_id, $producto_id]);
    }
    
    /**
     * Limpiar carrito completo
     */
    public static function clearCart($user_id) {
        require_once '../config/conexion.php';
        
        $query = "DELETE FROM carrito WHERE id_usuario = ?";
        
        return executeNonQuery($query, [$user_id]);
    }
    
    /**
     * Obtener cantidad total de items
     */
    public static function getItemCount($user_id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT SUM(cantidad_carrito) as total FROM carrito WHERE id_usuario = ?";
        $result = executeQuery($query, [$user_id]);
        
        return (int)($result[0]['total'] ?? 0);
    }
    
    /**
     * Obtener totales del carrito
     */
    public static function getTotals($user_id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT 
                    SUM(c.cantidad_carrito * p.precio_producto) as subtotal,
                    COUNT(c.id_carrito) as total_items,
                    SUM(c.cantidad_carrito) as total_quantity
                  FROM carrito c
                  INNER JOIN producto p ON c.id_producto = p.id_producto
                  WHERE c.id_usuario = ?";
        
        $result = executeQuery($query, [$user_id]);
        
        if(empty($result) || $result[0]['subtotal'] === null) {
            return [
                'subtotal' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0
            ];
        }
        
        $subtotal = (float)$result[0]['subtotal'];
        
        // Calcular envío (gratis si es mayor a $100)
        $shipping = $subtotal >= 100 ? 0 : 10;
        
        // Calcular impuestos (18% en Perú)
        $tax = $subtotal * 0.18;
        
        // Total final
        $total = $subtotal + $shipping + $tax;
        
        return [
            'subtotal' => $subtotal,
            'total_items' => (int)$result[0]['total_items'],
            'total_quantity' => (int)$result[0]['total_quantity'],
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total
        ];
    }
    
    /**
     * Verificar disponibilidad de stock para todos los items
     */
    public static function checkStock($user_id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT c.id_producto, c.cantidad_carrito, p.stock_producto, p.nombre_producto
                  FROM carrito c
                  INNER JOIN producto p ON c.id_producto = p.id_producto
                  WHERE c.id_usuario = ? AND c.cantidad_carrito > p.stock_producto";
        
        return executeQuery($query, [$user_id]);
    }
    
    /**
     * Obtener productos del carrito con información completa
     */
    public static function getCartDetails($user_id) {
        require_once '../config/conexion.php';
        
        $query = "SELECT c.*, 
                         p.nombre_producto, 
                         p.descripcion_producto,
                         p.precio_producto, 
                         p.imagen_producto, 
                         p.stock_producto,
                         cat.nombre_categoria,
                         (c.cantidad_carrito * p.precio_producto) as subtotal
                  FROM carrito c
                  INNER JOIN producto p ON c.id_producto = p.id_producto
                  LEFT JOIN categoria cat ON p.id_categoria = cat.id_categoria
                  WHERE c.id_usuario = ?
                  ORDER BY c.fecha_agregado_carrito DESC";
        
        return executeQuery($query, [$user_id]);
    }
    
    /**
     * Migrar carrito de sesión a usuario logueado
     */
    public static function migrateSessionCart($session_cart, $user_id) {
        if(empty($session_cart)) {
            return true;
        }
        
        try {
            foreach($session_cart as $producto_id => $cantidad) {
                self::addItem($user_id, $producto_id, $cantidad);
            }
            return true;
        } catch(Exception $e) {
            error_log("Error migrating cart: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Preparar carrito para checkout
     */
    public static function prepareForCheckout($user_id) {
        require_once '../config/conexion.php';
        
        // Verificar stock y obtener detalles
        $items = self::getCartDetails($user_id);
        $stock_issues = [];
        
        foreach($items as $item) {
            if($item['cantidad_carrito'] > $item['stock_producto']) {
                $stock_issues[] = [
                    'producto' => $item['nombre_producto'],
                    'solicitado' => $item['cantidad_carrito'],
                    'disponible' => $item['stock_producto']
                ];
            }
        }
        
        $totals = self::getTotals($user_id);
        
        return [
            'items' => $items,
            'totals' => $totals,
            'stock_issues' => $stock_issues,
            'can_proceed' => empty($stock_issues)
        ];
    }
    
    /**
     * Obtener estadísticas del carrito
     */
    public static function getStats($user_id = null) {
        require_once '../config/conexion.php';
        
        $stats = [];
        
        if($user_id) {
            // Estadísticas de un usuario específico
            $query = "SELECT COUNT(*) as items, SUM(cantidad_carrito) as cantidad 
                      FROM carrito WHERE id_usuario = ?";
            $result = executeQuery($query, [$user_id]);
            
            $stats['user_items'] = $result[0]['items'];
            $stats['user_quantity'] = $result[0]['cantidad'];
        } else {
            // Estadísticas generales
            $result = executeQuery("SELECT COUNT(*) as total FROM carrito");
            $stats['total_carts'] = $result[0]['total'];
            
            $result = executeQuery("SELECT COUNT(DISTINCT id_usuario) as total FROM carrito");
            $stats['users_with_cart'] = $result[0]['total'];
            
            $result = executeQuery("SELECT AVG(cantidad_carrito) as promedio FROM carrito");
            $stats['avg_quantity'] = round($result[0]['promedio'], 2);
        }
        
        return $stats;
    }
}
?>