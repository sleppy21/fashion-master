<?php
/**
 * Obtener cantidad de items en el carrito
 */
session_start();
header('Content-Type: application/json');

require_once '../../config/conexion.php';

try {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Obtener cantidad de items en carrito
    $result = executeQuery("
        SELECT COUNT(*) as total 
        FROM carrito 
        WHERE id_usuario = ?
    ", [$user_id]);
    
    $count = ($result && count($result) > 0) ? intval($result[0]['total']) : 0;
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
    
} catch(Exception $e) {
    error_log("Error get_cart_count: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener carrito',
        'count' => 0
    ]);
}
