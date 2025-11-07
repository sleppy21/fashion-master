<?php
ob_start(); // Capturar cualquier output no deseado
session_start();
require_once '../../config/conexion.php';
ob_end_clean(); // Limpiar el buffer

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['id_resena']) || !isset($_POST['calificacion']) || !isset($_POST['comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_resena = (int)$_POST['id_resena'];
$id_usuario = (int)$_SESSION['user_id'];
$calificacion = (int)$_POST['calificacion'];
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : null;
$comentario = trim($_POST['comentario']);

// Validaciones
if ($calificacion < 1 || $calificacion > 5) {
    echo json_encode(['success' => false, 'message' => 'Calificación inválida (debe ser entre 1 y 5)']);
    exit;
}

if (empty($comentario)) {
    echo json_encode(['success' => false, 'message' => 'El comentario es obligatorio']);
    exit;
}

if (strlen($comentario) < 10) {
    echo json_encode(['success' => false, 'message' => 'El comentario debe tener al menos 10 caracteres']);
    exit;
}

try {
    // Verificar que la reseña pertenece al usuario
    $check_query = "SELECT id_resena FROM resena WHERE id_resena = ? AND id_usuario = ?";
    $review = executeQuery($check_query, [$id_resena, $id_usuario]);
    
    if (!$review || empty($review)) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar esta reseña']);
        exit;
    }
    
    // Actualizar la reseña
    $update_query = "UPDATE resena SET calificacion = ?, titulo = ?, comentario = ? 
                     WHERE id_resena = ? AND id_usuario = ?";
    executeQuery($update_query, [$calificacion, $titulo, $comentario, $id_resena, $id_usuario]);
    
    echo json_encode(['success' => true, 'message' => 'Reseña actualizada exitosamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la reseña: ' . $e->getMessage()]);
}
