<?php
/**
 * Eliminar una dirección
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
        "SELECT id_direccion, es_principal FROM direccion WHERE id_direccion = ? AND id_usuario = ? AND status_direccion = 1",
        [$id_direccion, $id_usuario]
    );

    if (empty($verificar)) {
        echo json_encode(['success' => false, 'error' => 'Dirección no encontrada']);
        exit;
    }

    $direccion = $verificar[0];

    // Eliminar la dirección (soft delete)
    executeQuery(
        "UPDATE direccion SET status_direccion = 0 WHERE id_direccion = ? AND id_usuario = ?",
        [$id_direccion, $id_usuario]
    );

    // Si era la dirección predeterminada, establecer otra como predeterminada
    if ($direccion['es_principal'] == 1) {
        $otra_direccion = executeQuery(
            "SELECT id_direccion FROM direccion WHERE id_usuario = ? AND status_direccion = 1 AND id_direccion != ? LIMIT 1",
            [$id_usuario, $id_direccion]
        );

        if (!empty($otra_direccion)) {
            executeQuery(
                "UPDATE direccion SET es_principal = 1 WHERE id_direccion = ?",
                [$otra_direccion[0]['id_direccion']]
            );
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Dirección eliminada correctamente'
    ]);

} catch (Exception $e) {
    error_log("Error al eliminar dirección: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al eliminar dirección'
    ]);
}
