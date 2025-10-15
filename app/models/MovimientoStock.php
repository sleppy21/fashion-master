<?php
/**
 * Clase para gestionar movimientos de stock
 * Proporciona funciones para registrar ventas, compras, ajustes y devoluciones
 * 
 * @author SleepyStore
 * @date 2025-10-08
 */

class MovimientoStock {
    
    private $conn;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
    }
    
    /**
     * Registrar un movimiento de stock
     * 
     * @param int $id_producto ID del producto
     * @param string $tipo Tipo de movimiento: 'venta', 'compra', 'ajuste', 'devolucion'
     * @param int $cantidad Cantidad (positiva para aumentar, negativa para disminuir)
     * @param string $motivo Razón del movimiento
     * @param int $id_usuario ID del usuario que realiza el movimiento
     * @param string $referencia Referencia externa (ID de orden, factura, etc.)
     * @return bool|array Retorna true si es exitoso, array con error si falla
     */
    public function registrarMovimiento($id_producto, $tipo, $cantidad, $motivo = '', $id_usuario = null, $referencia = null) {
        
        // Validar tipo de movimiento
        $tipos_validos = ['venta', 'compra', 'ajuste', 'devolucion'];
        if (!in_array($tipo, $tipos_validos)) {
            return ['error' => 'Tipo de movimiento inválido'];
        }
        
        // Obtener stock actual
        $stmt = $this->conn->prepare("SELECT stock_actual_producto, nombre_producto FROM producto WHERE id_producto = ?");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return ['error' => 'Producto no encontrado'];
        }
        
        $producto = $result->fetch_assoc();
        $stock_anterior = $producto['stock_actual_producto'];
        $stock_nuevo = $stock_anterior + $cantidad;
        
        // Validar que el stock no sea negativo
        if ($stock_nuevo < 0) {
            return [
                'error' => 'Stock insuficiente',
                'stock_actual' => $stock_anterior,
                'cantidad_solicitada' => abs($cantidad)
            ];
        }
        
        // Iniciar transacción
        $this->conn->begin_transaction();
        
        try {
            // Actualizar stock del producto
            $stmt = $this->conn->prepare("UPDATE producto SET stock_actual_producto = ? WHERE id_producto = ?");
            $stmt->bind_param("ii", $stock_nuevo, $id_producto);
            $stmt->execute();
            
            // Registrar movimiento (el trigger también lo hará, pero esto da más control)
            $stmt = $this->conn->prepare("
                INSERT INTO movimiento_stock 
                (id_producto, id_usuario, tipo_movimiento, cantidad_movimiento, stock_anterior, stock_nuevo, motivo_movimiento, referencia_movimiento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iisiisss", $id_producto, $id_usuario, $tipo, $cantidad, $stock_anterior, $stock_nuevo, $motivo, $referencia);
            $stmt->execute();
            
            // Confirmar transacción
            $this->conn->commit();
            
            return [
                'success' => true,
                'stock_anterior' => $stock_anterior,
                'stock_nuevo' => $stock_nuevo,
                'movimiento_id' => $this->conn->insert_id
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['error' => 'Error al registrar movimiento: ' . $e->getMessage()];
        }
    }
    
    /**
     * Registrar una venta (disminuye stock)
     */
    public function registrarVenta($id_producto, $cantidad, $id_usuario = null, $id_orden = null) {
        return $this->registrarMovimiento(
            $id_producto, 
            'venta', 
            -abs($cantidad), 
            'Venta de producto',
            $id_usuario,
            $id_orden ? "ORDEN-$id_orden" : null
        );
    }
    
    /**
     * Registrar una compra (aumenta stock)
     */
    public function registrarCompra($id_producto, $cantidad, $motivo = 'Compra de inventario', $id_usuario = null) {
        return $this->registrarMovimiento(
            $id_producto, 
            'compra', 
            abs($cantidad), 
            $motivo,
            $id_usuario
        );
    }
    
    /**
     * Registrar un ajuste de inventario
     */
    public function registrarAjuste($id_producto, $cantidad, $motivo, $id_usuario = null) {
        return $this->registrarMovimiento(
            $id_producto, 
            'ajuste', 
            $cantidad, 
            $motivo,
            $id_usuario
        );
    }
    
    /**
     * Registrar una devolución (aumenta stock)
     */
    public function registrarDevolucion($id_producto, $cantidad, $motivo = 'Devolución de cliente', $id_usuario = null, $id_orden = null) {
        return $this->registrarMovimiento(
            $id_producto, 
            'devolucion', 
            abs($cantidad), 
            $motivo,
            $id_usuario,
            $id_orden ? "ORDEN-$id_orden" : null
        );
    }
    
    /**
     * Obtener historial de movimientos de un producto
     */
    public function obtenerHistorial($id_producto, $limite = 50) {
        $stmt = $this->conn->prepare("
            SELECT 
                m.*,
                u.username_usuario,
                u.nombre_usuario,
                u.apellido_usuario
            FROM movimiento_stock m
            LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
            WHERE m.id_producto = ?
            ORDER BY m.fecha_movimiento DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $id_producto, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Verificar productos con stock bajo
     */
    public function obtenerProductosStockBajo() {
        $query = "
            SELECT 
                p.*,
                c.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.stock_actual_producto <= p.stock_minimo_producto
            AND p.status_producto = 1
            ORDER BY (p.stock_actual_producto / p.stock_minimo_producto) ASC
        ";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtener estadísticas de movimientos por periodo
     */
    public function obtenerEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        $where = "";
        if ($fecha_inicio && $fecha_fin) {
            $where = "WHERE fecha_movimiento BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        }
        
        $query = "
            SELECT 
                tipo_movimiento,
                COUNT(*) as total_movimientos,
                SUM(ABS(cantidad_movimiento)) as total_cantidad,
                COUNT(DISTINCT id_producto) as productos_afectados
            FROM movimiento_stock
            $where
            GROUP BY tipo_movimiento
        ";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Ejemplo de uso:
/*
require_once 'database.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$stock = new MovimientoStock($conn);

// Registrar una venta
$resultado = $stock->registrarVenta(
    id_producto: 1,
    cantidad: 2,
    id_usuario: 1,
    id_orden: 123
);

if (isset($resultado['success'])) {
    echo "Venta registrada exitosamente";
} else {
    echo "Error: " . $resultado['error'];
}

// Registrar una compra
$stock->registrarCompra(
    id_producto: 1,
    cantidad: 50,
    motivo: 'Reabastecimiento mensual',
    id_usuario: 1
);

// Ver productos con stock bajo
$productos_bajo = $stock->obtenerProductosStockBajo();
foreach ($productos_bajo as $producto) {
    echo "Producto: {$producto['nombre_producto']} - Stock: {$producto['stock_actual_producto']}\n";
}

// Ver historial de un producto
$historial = $stock->obtenerHistorial(1, 20);
*/
?>
