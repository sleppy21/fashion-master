<?php
/**
 * Crear una nueva dirección
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
    $id_usuario = $_SESSION['user_id'];
    
    $nombre_cliente_direccion = trim($_POST['nombre_direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion_completa = trim($_POST['direccion_completa'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $distrito = trim($_POST['distrito'] ?? '');
    $referencia = trim($_POST['referencia'] ?? '');

    // Validaciones
    if (empty($nombre_cliente_direccion)) {
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

    // Verificar si es la primera dirección (será predeterminada automáticamente)
    $direcciones_existentes = executeQuery(
        "SELECT COUNT(*) as total FROM direccion WHERE id_usuario = ? AND status_direccion = 1",
        [$id_usuario]
    );
    
    $es_primera = empty($direcciones_existentes) || $direcciones_existentes[0]['total'] == 0;

    // Insertar la nueva dirección
    executeQuery(
        "INSERT INTO direccion (
            id_usuario,
            nombre_cliente_direccion,
            telefono_direccion,
            direccion_completa_direccion,
            departamento_direccion,
            provincia_direccion,
            distrito_direccion,
            referencia_direccion,
            es_principal,
            status_direccion,
            fecha_creacion_direccion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())",
        [
            $id_usuario,
            $nombre_cliente_direccion,
            $telefono,
            $direccion_completa,
            $departamento,
            $provincia,
            $distrito,
            $referencia,
            $es_primera ? 1 : 0
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Dirección agregada correctamente'
    ]);

} catch (Exception $e) {
    error_log("Error al crear dirección: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al crear dirección: ' . $e->getMessage()
    ]);
}
