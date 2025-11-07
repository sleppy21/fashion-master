<?php
ob_start(); // Capturar cualquier output no deseado
session_start();
require_once '../../config/conexion.php';
ob_end_clean(); // Limpiar el buffer

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar que se recibió el ID de la reseña
if (!isset($_POST['id_resena'])) {
    echo json_encode(['success' => false, 'message' => 'ID de reseña no proporcionado']);
    exit;
}

$id_resena = (int)$_POST['id_resena'];
$id_usuario = (int)$_SESSION['user_id'];

try {
    // Verificar que la reseña pertenece al usuario
    $check_query = "SELECT id_resena FROM resena WHERE id_resena = ? AND id_usuario = ?";
    $review = executeQuery($check_query, [$id_resena, $id_usuario]);
    
    if (!$review || empty($review)) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta reseña']);
        exit;
    }
    
    // Eliminar la reseña
    $delete_query = "DELETE FROM resena WHERE id_resena = ? AND id_usuario = ?";
    executeQuery($delete_query, [$id_resena, $id_usuario]);
    
    echo json_encode(['success' => true, 'message' => 'Reseña eliminada exitosamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la reseña: ' . $e->getMessage()]);
}
