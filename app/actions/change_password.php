<?php
/**
 * CAMBIAR CONTRASEÑA
 * Permite al usuario cambiar su contraseña
 */

session_start();
header('Content-Type: application/json');

require_once '../../config/conexion.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    // Obtener datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Si no hay JSON, intentar con POST
    if (!$data) {
        $data = $_POST;
    }
    
    // Validar datos requeridos
    if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }
    
    // Validar que las contraseñas nuevas coincidan
    if ($data['new_password'] !== $data['confirm_password']) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
        exit;
    }
    
    // Validar longitud mínima
    if (strlen($data['new_password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }
    
    $usuario_id = $_SESSION['user_id'];
    $current_password = $data['current_password'];
    $new_password = $data['new_password'];
    
    // Obtener contraseña actual del usuario
    $query = "SELECT password_usuario FROM usuario WHERE id_usuario = ? AND status_usuario = 1";
    $resultado = executeQuery($query, [$usuario_id]);
    
    if (!$resultado || empty($resultado)) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    $usuario = $resultado[0];
    
    // Verificar contraseña actual
    // NOTA: Este código asume que las contraseñas NO están hasheadas
    // Si usas password_hash, deberías usar password_verify aquí
    if ($usuario['password_usuario'] !== $current_password) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        exit;
    }
    
    // Actualizar contraseña
    // NOTA: Aquí guardamos la contraseña en texto plano como en tu BD actual
    // RECOMENDACIÓN: Usa password_hash() para mayor seguridad
    $update_query = "UPDATE usuario SET password_usuario = ? WHERE id_usuario = ?";
    $resultado_update = executeQuery($update_query, [$new_password, $usuario_id]);
    
    if ($resultado_update !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
    }
    
} catch (Exception $e) {
    error_log("Error al cambiar contraseña: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>
