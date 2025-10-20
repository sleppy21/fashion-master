<?php
/**
 * GLOBAL SEARCH API
 * Búsqueda global de productos con coincidencias flexibles
 */

require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Log de inicio
error_log('Global Search - Query: ' . ($_GET['q'] ?? 'NONE'));

// Verificar que hay un query
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(['products' => [], 'debug' => 'No query provided']);
    exit;
}

$search = trim($_GET['q']);
$searchLength = strlen($search);

try {
    // Búsqueda simple y efectiva
    $searchPattern = '%' . $search . '%';
    
    $sql = "SELECT DISTINCT
                p.id_producto,
                p.nombre_producto,
                p.precio_producto,
                p.descuento_porcentaje_producto,
                p.stock_actual_producto,
                p.url_imagen_producto,
                c.nombre_categoria,
                CASE 
                    WHEN p.descuento_porcentaje_producto > 0 THEN 
                        p.precio_producto / (1 - (p.descuento_porcentaje_producto / 100))
                    ELSE NULL 
                END as precio_anterior
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.status_producto = 1
            AND p.estado = 'activo'
            AND p.stock_actual_producto > 0
            AND (
                p.nombre_producto LIKE ?
                OR p.descripcion_producto LIKE ?
                OR m.nombre_marca LIKE ?
                OR c.nombre_categoria LIKE ?
            )
            ORDER BY p.nombre_producto ASC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log de resultados
    error_log('Global Search - Results count: ' . count($results));
    
    // Formatear resultados
    $products = [];
    foreach ($results as $row) {
        $products[] = [
            'id' => $row['id_producto'],
            'nombre' => $row['nombre_producto'],
            'precio' => $row['precio_producto'],
            'precio_anterior' => $row['precio_anterior'],
            'descuento' => $row['descuento_porcentaje_producto'],
            'stock' => $row['stock_actual_producto'],
            'imagen' => $row['url_imagen_producto'],
            'categoria' => $row['nombre_categoria'] ?? 'Sin categoría'
        ];
    }
    
    echo json_encode([
        'products' => $products,
        'count' => count($products),
        'debug' => [
            'search' => $search,
            'pattern' => $searchPattern,
            'sql_results' => count($results)
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Global search error: ' . $e->getMessage());
    error_log('Global search trace: ' . $e->getTraceAsString());
    echo json_encode([
        'products' => [],
        'error' => 'Error en la búsqueda',
        'debug' => $e->getMessage()
    ]);
}
