<?php
/**
 * ACTUALIZAR CANTIDAD EN EL CARRITO
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Aceptar tanto id_carrito como id_producto
$id_carrito = isset($_POST['id_carrito']) ? (int)$_POST['id_carrito'] : 0;
$id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

// Validar que tengamos al menos uno de los IDs
if(($id_carrito <= 0 && $id_producto <= 0) || $cantidad < 1) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Si se proporcionó id_producto en lugar de id_carrito, obtenerlo
    if($id_producto > 0 && $id_carrito <= 0) {
        $carrito_item = executeQuery("
            SELECT id_carrito 
            FROM carrito 
            WHERE id_producto = ? AND id_usuario = ?
        ", [$id_producto, $_SESSION['user_id']]);
        
        if(!$carrito_item || empty($carrito_item)) {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
            exit;
        }
        
        $id_carrito = $carrito_item[0]['id_carrito'];
    }
    
    // Verificar que el item pertenece al usuario y obtener stock
    $item = executeQuery("
        SELECT c.id_carrito, p.stock_actual_producto
        FROM carrito c
        INNER JOIN producto p ON c.id_producto = p.id_producto
        WHERE c.id_carrito = ? AND c.id_usuario = ?
    ", [$id_carrito, $_SESSION['user_id']]);
    
    if(!$item || empty($item)) {
        echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
        exit;
    }
    
    if($cantidad > $item[0]['stock_actual_producto']) {
        echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
        exit;
    }
    
    executeQuery("UPDATE carrito SET cantidad_carrito = ? WHERE id_carrito = ?", [$cantidad, $id_carrito]);
    
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
    
} catch(Exception $e) {
    error_log("Error al actualizar cantidad: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}
