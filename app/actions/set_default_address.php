<?php
/**
 * Establecer una dirección como predeterminada
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

    if (!$id_direccion) {
        echo json_encode(['success' => false, 'error' => 'ID de dirección no proporcionado']);
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

    // Quitar la marca de predeterminada de todas las direcciones del usuario
    $stmt1 = $conexion->prepare("UPDATE direccion SET es_principal = 0 WHERE id_usuario = ?");
    $stmt1->execute([$id_usuario]);

    // Establecer la nueva dirección como predeterminada
    $stmt2 = $conexion->prepare("UPDATE direccion SET es_principal = 1 WHERE id_direccion = ? AND id_usuario = ?");
    $stmt2->execute([$id_direccion, $id_usuario]);

    echo json_encode([
        'success' => true,
        'message' => 'Dirección establecida como predeterminada'
    ]);

} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log("Error al establecer dirección predeterminada: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al establecer dirección predeterminada'
    ]);
}
