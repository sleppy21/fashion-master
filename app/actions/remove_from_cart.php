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

$id_carrito = isset($_POST['id_carrito']) ? (int)$_POST['id_carrito'] : 0;

if($id_carrito <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
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
    
    echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
    
} catch(Exception $e) {
    error_log("Error al eliminar del carrito: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
}
