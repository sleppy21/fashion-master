<?php
session_start();
require_once '../../config/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['id_resena']) || !isset($_POST['razon'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_resena = (int)$_POST['id_resena'];
$id_usuario = (int)$_SESSION['user_id'];
$razon = trim($_POST['razon']);

// Validar que la razón no esté vacía
if (empty($razon)) {
    echo json_encode(['success' => false, 'message' => 'Debes proporcionar una razón']);
    exit;
}

try {
    // Verificar si el usuario ya reportó esta reseña
    $check_query = "SELECT id_reporte FROM resena_reportes WHERE id_resena = ? AND id_usuario = ?";
    $existing = executeQuery($check_query, [$id_resena, $id_usuario]);
    
    if (count($existing) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya has reportado esta reseña anteriormente'
        ]);
        exit;
    }
    
    // Insertar el reporte
    $insert_query = "INSERT INTO resena_reportes (id_resena, id_usuario, razon) VALUES (?, ?, ?)";
    executeQuery($insert_query, [$id_resena, $id_usuario, $razon]);
    
    // Incrementar el contador de reportes
    $update_query = "UPDATE resena SET reportes_count = reportes_count + 1 WHERE id_resena = ?";
    executeQuery($update_query, [$id_resena]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reporte enviado correctamente. Gracias por tu colaboración.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el reporte: ' . $e->getMessage()
    ]);
}
