<?php
session_start();
require_once '../../config/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para escribir una reseña']);
    exit;
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['id_producto']) || !isset($_POST['calificacion']) || !isset($_POST['comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_producto = (int)$_POST['id_producto'];
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
    // Verificar si el usuario ya escribió una reseña para este producto
    $check_query = "SELECT id_resena FROM resena WHERE id_producto = ? AND id_usuario = ?";
    $existing = executeQuery($check_query, [$id_producto, $id_usuario]);
    
    if (count($existing) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya has escrito una reseña para este producto'
        ]);
        exit;
    }
    
    // Insertar la nueva reseña (aprobada = 1 para publicación automática)
    $insert_query = "INSERT INTO resena (id_producto, id_usuario, calificacion, titulo, comentario, verificada, aprobada) 
                     VALUES (?, ?, ?, ?, ?, 0, 1)";
    executeQuery($insert_query, [$id_producto, $id_usuario, $calificacion, $titulo, $comentario]);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Reseña publicada exitosamente!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar la reseña: ' . $e->getMessage()
    ]);
}
