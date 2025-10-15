<?php
/**
 * Obtener datos de una dirección para editar
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

try {
    $id_direccion = $_GET['id_direccion'] ?? null;
    $id_usuario = $_SESSION['user_id'];

    if (!$id_direccion) {
        echo json_encode(['success' => false, 'error' => 'ID de dirección no proporcionado']);
        exit;
    }

    // Obtener la dirección
    $direccion = executeQuery(
        "SELECT * FROM direccion WHERE id_direccion = ? AND id_usuario = ? AND status_direccion = 1",
        [$id_direccion, $id_usuario]
    );

    if (empty($direccion)) {
        echo json_encode(['success' => false, 'error' => 'Dirección no encontrada']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'direccion' => $direccion[0]
    ]);

} catch (Exception $e) {
    error_log("Error al obtener dirección: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener dirección'
    ]);
}
