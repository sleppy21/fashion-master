<?php
/**
 * GET SEARCH SUGGESTIONS
 * Endpoint para búsqueda en tiempo real
 * Devuelve sugerencias de productos basadas en el término de búsqueda
 */

// Evitar que errores de PHP interfieran con el JSON
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar cualquier output previo
ob_start();

header('Content-Type: application/json; charset=utf-8');

// Incluir configuración
require_once '../../config/conexion.php';

try {
    // Obtener término de búsqueda
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    // Validar que haya término de búsqueda
    if (empty($query)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'No se proporcionó término de búsqueda',
            'suggestions' => [],
            'count' => 0
        ]);
        ob_end_flush();
        exit;
    }
    
    // Sanitizar término de búsqueda
    $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
    $searchTerm = "%$query%";
    
    // Query para buscar productos
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.precio,
                p.imagen,
                p.stock,
                c.nombre as categoria
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.estado = 1 
            AND (
                p.nombre LIKE ? 
                OR p.descripcion LIKE ?
                OR c.nombre LIKE ?
            )
            ORDER BY 
                CASE 
                    WHEN p.nombre LIKE ? THEN 1
                    WHEN p.descripcion LIKE ? THEN 2
                    ELSE 3
                END,
                p.nombre ASC
            LIMIT 10";
    
    $suggestions = executeQuery($sql, [
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);
    
    // Formatear resultados
    $formattedSuggestions = array_map(function($product) {
        return [
            'id' => $product['id'],
            'nombre' => $product['nombre'],
            'precio' => $product['precio'],
            'imagen' => $product['imagen'],
            'categoria' => $product['categoria'],
            'stock' => $product['stock']
        ];
    }, $suggestions);
    
    // Limpiar buffer y enviar solo JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'suggestions' => $formattedSuggestions,
        'count' => count($formattedSuggestions)
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_search_suggestions.php: " . $e->getMessage());
    
    // Limpiar buffer
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar productos',
        'error' => $e->getMessage()
    ]);
}

// Limpiar y enviar buffer
ob_end_flush();
exit;
