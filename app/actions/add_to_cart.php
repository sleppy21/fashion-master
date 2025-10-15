<?php
/**
 * AGREGAR PRODUCTO AL CARRITO
 * Endpoint AJAX para agregar productos al carrito
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en logs

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Log de inicio
error_log("=== Inicio add_to_cart.php ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO EXISTE'));

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    error_log("Error: Usuario no logueado");
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para agregar productos al carrito'
    ]);
    exit;
}

// Verificar que se recibió el ID del producto
if (!isset($_POST['id_producto']) || empty($_POST['id_producto'])) {
    error_log("Error: ID de producto no válido");
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto no válido'
    ]);
    exit;
}

$id_usuario = $_SESSION['user_id'];
$id_producto = (int)$_POST['id_producto'];
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

error_log("Procesando carrito - Usuario: $id_usuario, Producto: $id_producto, Cantidad: $cantidad");

$already_in_cart = false; // Inicializar variable

try {
    // Verificar si el producto existe y está activo
    $producto = executeQuery("
        SELECT id_producto, nombre_producto, stock_actual_producto 
        FROM producto 
        WHERE id_producto = ? AND status_producto = 1
    ", [$id_producto]);
    
    error_log("Producto query result: " . print_r($producto, true));
    
    if (!$producto || empty($producto)) {
        error_log("Error: Producto no encontrado o inactivo");
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
        
        $message = 'Producto agregado al carrito';
        $already_in_cart = true;
        error_log("Cantidad actualizada en carrito. Nueva cantidad: $nueva_cantidad");
    } else {
        // Agregar nuevo producto al carrito
        executeQuery("
            INSERT INTO carrito (id_usuario, id_producto, cantidad_carrito) 
            VALUES (?, ?, ?)
        ", [$id_usuario, $id_producto, $cantidad]);
        
        $message = 'Producto agregado al carrito';
        $already_in_cart = false;
        error_log("Nuevo producto agregado al carrito");
    }
    
    // Obtener total de items en el carrito
    $total_items = executeQuery("
        SELECT SUM(cantidad_carrito) as total 
        FROM carrito 
        WHERE id_usuario = ?
    ", [$id_usuario]);
    
    $total = $total_items && !empty($total_items) ? (int)$total_items[0]['total'] : 0;
    
    error_log("Producto agregado/actualizado exitosamente. Total items: $total");
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $total,
        'already_in_cart' => $already_in_cart
    ]);
    
} catch (Exception $e) {
    error_log("=== ERROR EN CARRITO ===");
    error_log("Error al agregar al carrito: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("========================");
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar al carrito: ' . $e->getMessage()
    ]);
}
