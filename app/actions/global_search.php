<?php
/**
 * GLOBAL SEARCH API
 * Búsqueda global de productos con coincidencias flexibles
 */

require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

// Verificar que hay un query
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(['products' => []]);
    exit;
}

$search = trim($_GET['q']);
$searchLength = strlen($search);

try {
    // Crear patrón de búsqueda flexible
    $flexiblePattern = '%' . implode('%', str_split($search)) . '%';
    
    // Determinar modo de búsqueda
    if ($searchLength >= 3) {
        // Modo flexible: buscar coincidencias de caracteres
        $minMatches = max(3, ceil($searchLength * 0.6));
        
        $sql = "SELECT DISTINCT
                    p.id_producto,
                    p.nombre_producto,
                    p.precio_producto,
                    p.precio_anterior_producto,
                    p.stock_producto,
                    p.url_imagen_producto,
                    c.nombre_categoria
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.estado_producto = 1
                AND (
                    p.nombre_producto LIKE :flexible
                    OR p.descripcion_producto LIKE :flexible
                    OR m.nombre_marca LIKE :flexible
                    OR c.nombre_categoria LIKE :flexible
                    OR p.nombre_producto LIKE :exact
                    OR p.descripcion_producto LIKE :exact
                )
                ORDER BY 
                    CASE 
                        WHEN p.nombre_producto LIKE :exact THEN 1
                        WHEN p.nombre_producto LIKE :flexible THEN 2
                        WHEN p.descripcion_producto LIKE :exact THEN 3
                        ELSE 4
                    END,
                    p.nombre_producto ASC
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':flexible', $flexiblePattern, PDO::PARAM_STR);
        $stmt->bindValue(':exact', '%' . $search . '%', PDO::PARAM_STR);
        
    } else {
        // Modo exacto: solo coincidencias exactas de substring
        $exactPattern = '%' . $search . '%';
        
        $sql = "SELECT DISTINCT
                    p.id_producto,
                    p.nombre_producto,
                    p.precio_producto,
                    p.precio_anterior_producto,
                    p.stock_producto,
                    p.url_imagen_producto,
                    c.nombre_categoria
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.estado_producto = 1
                AND (
                    p.nombre_producto LIKE :search
                    OR p.descripcion_producto LIKE :search
                    OR m.nombre_marca LIKE :search
                    OR c.nombre_categoria LIKE :search
                )
                ORDER BY p.nombre_producto ASC
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', $exactPattern, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear resultados
    $products = [];
    foreach ($results as $row) {
        $products[] = [
            'id' => $row['id_producto'],
            'nombre' => $row['nombre_producto'],
            'precio' => $row['precio_producto'],
            'precio_anterior' => $row['precio_anterior_producto'],
            'stock' => $row['stock_producto'],
            'imagen' => $row['url_imagen_producto'],
            'categoria' => $row['nombre_categoria'] ?? 'Sin categoría'
        ];
    }
    
    echo json_encode([
        'products' => $products,
        'count' => count($products)
    ]);
    
} catch (Exception $e) {
    error_log('Global search error: ' . $e->getMessage());
    echo json_encode([
        'products' => [],
        'error' => 'Error en la búsqueda'
    ]);
}
