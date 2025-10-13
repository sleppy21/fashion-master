<?php
/**
 * ELIMINAR ITEM DEL CARRITO
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

if($id_carrito <= 0 && $id_producto <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Si se envía id_producto, buscar el id_carrito
    if($id_producto > 0 && $id_carrito <= 0) {
        $item = executeQuery("
            SELECT id_carrito
            FROM carrito
            WHERE id_producto = ? AND id_usuario = ?
        ", [$id_producto, $_SESSION['user_id']]);
        
        if($item && !empty($item)) {
            $id_carrito = $item[0]['id_carrito'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
            exit;
        }
    }
    
    // Verificar que el item pertenece al usuario
    $item = executeQuery("
        SELECT id_carrito
        FROM carrito
        WHERE id_carrito = ? AND id_usuario = ?
    ", [$id_carrito, $_SESSION['user_id']]);
    
    if(!$item || empty($item)) {
        echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
        exit;
    }
    
    executeQuery("DELETE FROM carrito WHERE id_carrito = ?", [$id_carrito]);
    
    // Obtener nuevo conteo del carrito
    $cart_count_result = executeQuery("
        SELECT COUNT(*) as total 
        FROM carrito 
        WHERE id_usuario = ?
    ", [$_SESSION['user_id']]);
    $cart_count = ($cart_count_result && count($cart_count_result) > 0) ? intval($cart_count_result[0]['total']) : 0;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Producto eliminado del carrito',
        'cart_count' => $cart_count
    ]);
    
} catch(Exception $e) {
    error_log("Error al eliminar del carrito: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}
