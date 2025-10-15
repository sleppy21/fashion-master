<?php
/**
 * GET ORDER DETAILS
 * Obtiene los detalles completos de un pedido para mostrar en modal
 */

session_start();
require_once '../../config/conexion.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Validar ID del pedido
if (!isset($_POST['id_pedido']) || empty($_POST['id_pedido'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de pedido no proporcionado'
    ]);
    exit;
}

$id_pedido = (int)$_POST['id_pedido'];
$id_usuario = $_SESSION['user_id'];

try {
    // Obtener datos del pedido (verificando que pertenezca al usuario)
    $pedido_resultado = executeQuery("
        SELECT * FROM pedido 
        WHERE id_pedido = ? AND id_usuario = ?
    ", [$id_pedido, $id_usuario]);
    
    if (!$pedido_resultado || empty($pedido_resultado)) {
        echo json_encode([
            'success' => false,
            'message' => 'Pedido no encontrado'
        ]);
        exit;
    }
    
    $pedido = $pedido_resultado[0];
    
    // Obtener detalles de los productos
    $detalles_resultado = executeQuery("
        SELECT dp.*, p.url_imagen_producto, p.nombre_producto
        FROM detalle_pedido dp
        LEFT JOIN producto p ON dp.id_producto = p.id_producto
        WHERE dp.id_pedido = ?
    ", [$id_pedido]);
    
    $detalles = $detalles_resultado ? $detalles_resultado : [];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'pedido' => $pedido,
            'detalles' => $detalles
        ]
    ]);
    
} catch(Exception $e) {
    error_log("Error al obtener detalles del pedido: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar los detalles'
    ]);
}
?>
