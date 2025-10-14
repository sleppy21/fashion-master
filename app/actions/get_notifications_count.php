<?php
/**
 * Obtener el contador de notificaciones no leídas del usuario
 * Retorna JSON con el contador
 */

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado', 'count' => 0]);
    exit;
}

try {
    // Obtener el número de notificaciones no leídas y activas
    $query = "SELECT COUNT(*) as total 
              FROM notificacion 
              WHERE id_usuario = ? 
              AND leida_notificacion = 0 
              AND estado_notificacion = 'activo'";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_usuario]);
    $count = intval($stmt->fetchColumn());
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Error al obtener el contador de notificaciones: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener notificaciones',
        'count' => 0
    ], JSON_UNESCAPED_UNICODE);
}
?>
