<?php
/**
 * AJAX - GET MODAL DATA
 * Obtiene los datos de los modales en formato JSON
 * @version 1.0 - Octubre 2025
 */

session_start();
header('Content-Type: application/json');

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Verificar que haya usuario logueado
if (!isset($_SESSION['usuario_logueado'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Cargar dependencias
require_once __DIR__ . '/../../config/conexion.php';

try {
    $usuario_logueado = $_SESSION['usuario_logueado'];
    $modal_type = $_GET['modal'] ?? 'all';
    
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // CONTADORES
    if ($modal_type === 'counters' || $modal_type === 'all') {
        // Carrito
        $cart_result = executeQuery("
            SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?
        ", [$usuario_logueado['id_usuario']]);
        $response['cart_count'] = $cart_result ? (int)$cart_result[0]['total'] : 0;
        
        // Favoritos
        $fav_result = executeQuery("
            SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?
        ", [$usuario_logueado['id_usuario']]);
        $response['favorites_count'] = $fav_result ? (int)$fav_result[0]['total'] : 0;
        
        // Notificaciones no leídas
        $notif_result = executeQuery("
            SELECT COUNT(*) as total FROM notificacion 
            WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'
        ", [$usuario_logueado['id_usuario']]);
        $response['notifications_unread'] = $notif_result ? (int)$notif_result[0]['total'] : 0;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener datos',
        'message' => $e->getMessage()
    ]);
}
