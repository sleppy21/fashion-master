<?php
session_start();
require_once '../../config/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
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
    // Verificar si el usuario ya dio like a esta reseña
    $check_query = "SELECT id_like FROM resena_likes WHERE id_resena = ? AND id_usuario = ?";
    $existing = executeQuery($check_query, [$id_resena, $id_usuario]);
    
    if (count($existing) > 0) {
        // Ya dio like, entonces quitarlo
        $delete_query = "DELETE FROM resena_likes WHERE id_resena = ? AND id_usuario = ?";
        executeQuery($delete_query, [$id_resena, $id_usuario]);
        
        // Decrementar el contador
        $update_query = "UPDATE resena SET likes_count = likes_count - 1 WHERE id_resena = ?";
        executeQuery($update_query, [$id_resena]);
        
        // Obtener el nuevo contador
        $count_query = "SELECT likes_count FROM resena WHERE id_resena = ?";
        $result = executeQuery($count_query, [$id_resena]);
        $new_count = $result[0]['likes_count'];
        
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'likes_count' => $new_count,
            'message' => 'Like eliminado'
        ]);
    } else {
        // No ha dado like, entonces agregarlo
        $insert_query = "INSERT INTO resena_likes (id_resena, id_usuario) VALUES (?, ?)";
        executeQuery($insert_query, [$id_resena, $id_usuario]);
        
        // Incrementar el contador
        $update_query = "UPDATE resena SET likes_count = likes_count + 1 WHERE id_resena = ?";
        executeQuery($update_query, [$id_resena]);
        
        // Obtener el nuevo contador
        $count_query = "SELECT likes_count FROM resena WHERE id_resena = ?";
        $result = executeQuery($count_query, [$id_resena]);
        $new_count = $result[0]['likes_count'];
        
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'likes_count' => $new_count,
            'message' => '¡Reseña marcada como útil!'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
