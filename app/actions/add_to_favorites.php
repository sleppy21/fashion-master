<?php
/**
 * AGREGAR/QUITAR PRODUCTO DE FAVORITOS
 * Endpoint AJAX para gestionar favoritos
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en logs

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Log de inicio
error_log("=== Inicio add_to_favorites.php ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO EXISTE'));

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    error_log("Error: Usuario no logueado");
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para agregar favoritos'
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

error_log("Procesando favorito - Usuario: $id_usuario, Producto: $id_producto");

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
        error_log("Producto YA está en favoritos, eliminando...");
        executeQuery("
            DELETE FROM favorito 
            WHERE id_favorito = ?
        ", [$existe[0]['id_favorito']]);
        
        $message = 'Eliminado de favoritos';
        $action = 'removed';
        error_log("Favorito eliminado exitosamente");
    } else {
        // Agregar a favoritos
        error_log("Producto NO está en favoritos, agregando...");
        executeQuery("
            INSERT INTO favorito (id_usuario, id_producto) 
            VALUES (?, ?)
        ", [$id_usuario, $id_producto]);
        
        $message = 'Agregado a favoritos';
        $action = 'added';
        error_log("Favorito agregado exitosamente");
    }
    
    // Obtener total de favoritos
    $total_favoritos = executeQuery("
        SELECT COUNT(*) as total 
        FROM favorito 
        WHERE id_usuario = ?
    ", [$id_usuario]);
    
    $total = $total_favoritos && !empty($total_favoritos) ? (int)$total_favoritos[0]['total'] : 0;
    
    error_log("Total favoritos después de operación: $total");
    error_log("Action: $action");
    error_log("Message: $message");
    
    $response = [
        'success' => true,
        'message' => $message,
        'action' => $action,
        'favorites_count' => $total
    ];
    
    error_log("Response JSON: " . json_encode($response));
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("=== ERROR EN FAVORITOS ===");
    error_log("Error al gestionar favoritos: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("========================");
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar favoritos: ' . $e->getMessage()
    ]);
}
