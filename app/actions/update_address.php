<?php
/**
 * Actualizar una dirección existente
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $id_direccion = $_POST['id_direccion'] ?? null;
    $id_usuario = $_SESSION['user_id'];
    
    $nombre_direccion = trim($_POST['nombre_direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion_completa = trim($_POST['direccion_completa'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $distrito = trim($_POST['distrito'] ?? '');
    $referencia = trim($_POST['referencia'] ?? '');

    // Validaciones
    if (!$id_direccion) {
        echo json_encode(['success' => false, 'error' => 'ID de dirección no proporcionado']);
        exit;
    }

    if (empty($nombre_direccion)) {
        echo json_encode(['success' => false, 'error' => 'El nombre de la dirección es requerido']);
        exit;
    }

    if (empty($direccion_completa)) {
        echo json_encode(['success' => false, 'error' => 'La dirección completa es requerida']);
        exit;
    }

    if (empty($departamento) || empty($provincia) || empty($distrito)) {
        echo json_encode(['success' => false, 'error' => 'Debe seleccionar departamento, provincia y distrito']);
        exit;
    }

    // Verificar que la dirección pertenece al usuario
    $verificar = executeQuery(
        "SELECT id_direccion FROM direccion WHERE id_direccion = ? AND id_usuario = ? AND status_direccion = 1",
        [$id_direccion, $id_usuario]
    );

    if (empty($verificar)) {
        echo json_encode(['success' => false, 'error' => 'Dirección no encontrada']);
        exit;
    }

    // Actualizar la dirección
    executeQuery(
        "UPDATE direccion SET 
            nombre_direccion = ?,
            telefono_direccion = ?,
            direccion_completa_direccion = ?,
            departamento_direccion = ?,
            provincia_direccion = ?,
            distrito_direccion = ?,
            referencia_direccion = ?
        WHERE id_direccion = ? AND id_usuario = ?",
        [
            $nombre_direccion,
            $telefono,
            $direccion_completa,
            $departamento,
            $provincia,
            $distrito,
            $referencia,
            $id_direccion,
            $id_usuario
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Dirección actualizada correctamente'
    ]);

} catch (Exception $e) {
    error_log("Error al actualizar dirección: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al actualizar dirección: ' . $e->getMessage()
    ]);
}
