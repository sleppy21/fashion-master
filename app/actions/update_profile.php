<?php
/**
 * ACTUALIZAR PERFIL DE USUARIO
 * Actualiza la información personal del usuario
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
    if (empty($data['nombre']) || empty($data['apellido'])) {
        echo json_encode(['success' => false, 'message' => 'Nombre y apellido son requeridos']);
        exit;
    }
    
    $usuario_id = $_SESSION['user_id'];
    $nombre = trim($data['nombre']);
    $apellido = trim($data['apellido']);
    $telefono = isset($data['telefono']) ? trim($data['telefono']) : null;
    $fecha_nacimiento = isset($data['fecha_nacimiento']) && !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null;
    $genero = isset($data['genero']) ? $data['genero'] : 'Otro';
    
    // Validar género
    if (!in_array($genero, ['M', 'F', 'Otro'])) {
        $genero = 'Otro';
    }
    
    // Actualizar en la base de datos
    $query = "UPDATE usuario SET 
              nombre_usuario = ?,
              apellido_usuario = ?,
              telefono_usuario = ?,
              fecha_nacimiento = ?,
              genero_usuario = ?
              WHERE id_usuario = ? AND status_usuario = 1";
    
    $resultado = executeQuery($query, [
        $nombre,
        $apellido,
        $telefono,
        $fecha_nacimiento,
        $genero,
        $usuario_id
    ]);
    
    if ($resultado !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'data' => [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'fecha_nacimiento' => $fecha_nacimiento,
                'genero' => $genero
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
    }
    
} catch (Exception $e) {
    error_log("Error al actualizar perfil: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>
