<?php
/**
 * Marcar todas las notificaciones como leídas
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

try {
    // Actualizar todas las notificaciones no leídas
    $query = "UPDATE notificacion 
              SET leida_notificacion = 1, 
                  fecha_lectura_notificacion = CURRENT_TIMESTAMP 
              WHERE id_usuario = ? 
              AND leida_notificacion = 0 
              AND estado_notificacion = 'activo'";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_usuario]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Todas las notificaciones marcadas como leídas',
        'affected' => $stmt->rowCount()
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Error al marcar todas las notificaciones: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar'], JSON_UNESCAPED_UNICODE);
}
?>
