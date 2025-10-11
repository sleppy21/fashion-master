<?php
/**
 * AGREGAR/QUITAR PRODUCTO DE FAVORITOS
 * Endpoint AJAX para gestionar favoritos
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para agregar favoritos'
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

try {
    // Verificar si el producto existe
    $producto = executeQuery("
        SELECT id_producto, nombre_producto 
        FROM producto 
        WHERE id_producto = ? AND status_producto = 1
    ", [$id_producto]);
    
    if (!$producto || empty($producto)) {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
        exit;
    }
    
    // Verificar si ya está en favoritos
    $existe = executeQuery("
        SELECT id_favorito 
        FROM favorito 
        WHERE id_usuario = ? AND id_producto = ?
    ", [$id_usuario, $id_producto]);
    
    if ($existe && !empty($existe)) {
        // Eliminar de favoritos
        executeQuery("
            DELETE FROM favorito 
            WHERE id_favorito = ?
        ", [$existe[0]['id_favorito']]);
        
        $message = 'Eliminado de favoritos';
        $action = 'removed';
    } else {
        // Agregar a favoritos
        executeQuery("
            INSERT INTO favorito (id_usuario, id_producto) 
            VALUES (?, ?)
        ", [$id_usuario, $id_producto]);
        
        $message = 'Agregado a favoritos';
        $action = 'added';
    }
    
    // Obtener total de favoritos
    $total_favoritos = executeQuery("
        SELECT COUNT(*) as total 
        FROM favorito 
        WHERE id_usuario = ?
    ", [$id_usuario]);
    
    $total = $total_favoritos && !empty($total_favoritos) ? (int)$total_favoritos[0]['total'] : 0;
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action,
        'favorites_count' => $total
    ]);
    
} catch (Exception $e) {
    error_log("Error al gestionar favoritos: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud. Intenta nuevamente.'
    ]);
}
