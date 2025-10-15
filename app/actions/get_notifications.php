<?php
/**
 * Obtener todas las notificaciones del usuario
 * Soporta filtros: all, unread
 */

// Determinar si se llama via AJAX o require
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
           (isset($_SERVER['REQUEST_METHOD']) && count($_GET) > 0);

// Prevenir cualquier output antes del JSON solo si es AJAX
if($is_ajax) {
    ob_start();
}

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';

// Limpiar cualquier output accidental solo si es AJAX
if($is_ajax) {
    ob_end_clean();
    header('Content-Type: application/json');
}

// Verificar autenticación (compatible con ambos formatos de sesión)
$id_usuario = null;
if(isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id_usuario'])) {
    $id_usuario = intval($_SESSION['usuario']['id_usuario']);
} elseif(isset($_SESSION['user_id'])) {
    $id_usuario = intval($_SESSION['user_id']);
}

if(!$id_usuario) {
    if($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    } else {
        $notifications_data = [];
        return;
    }
}
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

try {
    // Query base
    $query = "SELECT * FROM notificacion WHERE id_usuario = ? AND estado_notificacion = 'activo'";
    $params = [$id_usuario];
    
    // Aplicar filtro
    if ($filter === 'unread') {
        $query .= " AND leida_notificacion = 0";
    }
    
    $query .= " ORDER BY fecha_creacion_notificacion DESC LIMIT 50";
    
    // Usar PDO en lugar de mysqli
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener contadores
    $count_all_query = "SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND estado_notificacion = 'activo'";
    $count_all_stmt = $conn->prepare($count_all_query);
    $count_all_stmt->execute([$id_usuario]);
    $count_all = intval($count_all_stmt->fetchColumn());
    
    $count_unread_query = "SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'";
    $count_unread_stmt = $conn->prepare($count_unread_query);
    $count_unread_stmt->execute([$id_usuario]);
    $count_unread = intval($count_unread_stmt->fetchColumn());
    
    // Si es AJAX, devolver JSON
    if($is_ajax) {
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'counts' => [
                'all' => $count_all,
                'unread' => $count_unread
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Si es require, asignar a variable
        $notifications_data = $notifications;
    }

} catch (Exception $e) {
    error_log("Error al obtener notificaciones: " . $e->getMessage());
    if($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener notificaciones',
            'error' => $e->getMessage(),
            'notifications' => []
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $notifications_data = [];
    }
}
?>
