<?php
/**
 * AGREGAR PRODUCTO AL CARRITO
 * Endpoint AJAX para agregar productos al carrito
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para agregar productos al carrito'
    ]);
    exit;
}

// Verificar que se recibió el ID del producto
if (!isset($_POST['id_producto']) || empty($_POST['id_producto'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto no válido'
    ]);
    exit;
}

$id_usuario = $_SESSION['user_id'];
$id_producto = (int)$_POST['id_producto'];
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

try {
    // Verificar si el producto existe y está activo
    $producto = executeQuery("
        SELECT id_producto, nombre_producto, stock_actual_producto 
        FROM producto 
        WHERE id_producto = ? AND status_producto = 1 AND estado = 'activo'
    ", [$id_producto]);
    
    if (!$producto || empty($producto)) {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no disponible'
        ]);
        exit;
    }
    
    // Verificar stock
    if ($producto[0]['stock_actual_producto'] < $cantidad) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuficiente'
        ]);
        exit;
    }
    
    // Verificar si el producto ya está en el carrito
    $existe = executeQuery("
        SELECT id_carrito, cantidad_carrito 
        FROM carrito 
        WHERE id_usuario = ? AND id_producto = ?
    ", [$id_usuario, $id_producto]);
    
    if ($existe && !empty($existe)) {
        // Actualizar cantidad
        $nueva_cantidad = $existe[0]['cantidad_carrito'] + $cantidad;
        
        // Verificar stock para la nueva cantidad
        if ($producto[0]['stock_actual_producto'] < $nueva_cantidad) {
            $stock_disponible = $producto[0]['stock_actual_producto'];
            $en_carrito = $existe[0]['cantidad_carrito'];
            echo json_encode([
                'success' => false,
                'message' => "Ya tienes {$en_carrito} unidad(es) en el carrito. Stock disponible: {$stock_disponible}"
            ]);
            exit;
        }
        
        executeQuery("
            UPDATE carrito 
            SET cantidad_carrito = ? 
            WHERE id_carrito = ?
        ", [$nueva_cantidad, $existe[0]['id_carrito']]);
        
        $message = 'Cantidad actualizada en el carrito';
    } else {
        // Agregar nuevo producto al carrito
        executeQuery("
            INSERT INTO carrito (id_usuario, id_producto, cantidad_carrito) 
            VALUES (?, ?, ?)
        ", [$id_usuario, $id_producto, $cantidad]);
        
        $message = 'Producto agregado al carrito';
    }
    
    // Obtener total de items en el carrito
    $total_items = executeQuery("
        SELECT SUM(cantidad_carrito) as total 
        FROM carrito 
        WHERE id_usuario = ?
    ", [$id_usuario]);
    
    $total = $total_items && !empty($total_items) ? (int)$total_items[0]['total'] : 0;
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $total
    ]);
    
} catch (Exception $e) {
    error_log("Error al agregar al carrito: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar al carrito. Intenta nuevamente.'
    ]);
}
