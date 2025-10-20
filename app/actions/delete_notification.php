<?php
/**
 * Eliminar una notificación (cambiar estado a eliminado)
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Verificar autenticación (compatible con ambos formatos de sesión)
$id_usuario = null;
if(isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id_usuario'])) {
    $id_usuario = intval($_SESSION['usuario']['id_usuario']);
} elseif(isset($_SESSION['user_id'])) {
    $id_usuario = intval($_SESSION['user_id']);
}

if(!$id_usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del POST (soportar tanto FormData como JSON)
$id_notificacion = 0;

// Intentar obtener desde FormData primero
if (isset($_POST['id'])) {
    $id_notificacion = intval($_POST['id']);
} else {
    // Intentar obtener desde JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $id_notificacion = isset($input['id']) ? intval($input['id']) : 0;
}

if ($id_notificacion <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de notificación inválido']);
    exit;
}

try {
    // Cambiar estado a eliminado
    $query = "UPDATE notificacion 
              SET estado_notificacion = 'eliminado' 
              WHERE id_notificacion = ? 
              AND id_usuario = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_notificacion, $id_usuario]);
    
    if ($stmt->rowCount() > 0) {
        // Obtener el nuevo count de notificaciones NO LEÍDAS
        $count_query = "SELECT COUNT(*) as total 
                       FROM notificacion 
                       WHERE id_usuario = ? 
                       AND leida_notificacion = 0 
                       AND estado_notificacion = 'activo'";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute([$id_usuario]);
        $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
        $notifications_count = $count_result ? intval($count_result['total']) : 0;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Notificación eliminada',
            'notifications_count' => $notifications_count
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la notificación'], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    error_log("Error al eliminar notificación: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar'], JSON_UNESCAPED_UNICODE);
}
?>
