<?php
/**
 * OBTENER PRODUCTOS FILTRADOS VÍA AJAX
 * Evita el refresh completo de la página
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

try {
    // Obtener filtros de la URL
    // Manejar múltiples categorías
    $filtro_categoria = [];
    if (isset($_GET['c']) && is_array($_GET['c'])) {
        $filtro_categoria = array_map('intval', $_GET['c']);
    } elseif (isset($_GET['c']) && !empty($_GET['c'])) {
        // Si viene como string separado por comas
        $filtro_categoria = array_map('intval', explode(',', $_GET['c']));
    }
    
    $filtro_genero = isset($_GET['g']) ? $_GET['g'] : (isset($_GET['genero']) ? $_GET['genero'] : null);
    $filtro_marca = isset($_GET['m']) ? (int)$_GET['m'] : (isset($_GET['marca']) ? (int)$_GET['marca'] : null);
    $filtro_precio_min = isset($_GET['pmin']) ? (float)$_GET['pmin'] : (isset($_GET['precio_min']) ? (float)$_GET['precio_min'] : 0);
    $filtro_precio_max = isset($_GET['pmax']) ? (float)$_GET['pmax'] : (isset($_GET['precio_max']) ? (float)$_GET['precio_max'] : 9999);
    $filtro_buscar = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['buscar']) ? trim($_GET['buscar']) : '');
    $filtro_ordenar = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

    // Construir query con filtros
    $sql = "
        SELECT DISTINCT
            p.id_producto,
            p.nombre_producto,
            p.descripcion_producto,
            p.precio_producto,
            p.descuento_porcentaje_producto,
            p.stock_actual_producto,
            p.url_imagen_producto,
            p.genero_producto,
            m.nombre_marca,
            c.nombre_categoria,
            COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
            COALESCE(COUNT(DISTINCT r.id_resena), 0) as total_resenas,
            (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) as precio_final
        FROM producto p
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
        LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
        WHERE p.status_producto = 1
          AND p.estado = 'activo'
          AND p.stock_actual_producto > 0
    ";

    $params = [];

    // Múltiples categorías
    if (!empty($filtro_categoria)) {
        $placeholders = implode(',', array_fill(0, count($filtro_categoria), '?'));
        $sql .= " AND p.id_categoria IN ($placeholders)";
        $params = array_merge($params, $filtro_categoria);
    }

    if ($filtro_genero && $filtro_genero !== 'all') {
        $sql .= " AND p.genero_producto = ?";
        $params[] = $filtro_genero;
    }

    if ($filtro_marca) {
        $sql .= " AND p.id_marca = ?";
        $params[] = $filtro_marca;
    }

    if (!empty($filtro_buscar)) {
        // Calcular longitud mínima de coincidencia (mínimo 3 caracteres o 60% de la búsqueda)
        $min_match_length = max(3, ceil(strlen($filtro_buscar) * 0.6));
        
        // Búsqueda por similitud solo si la búsqueda tiene al menos 3 caracteres
        if (strlen($filtro_buscar) >= 3) {
            // Crear patrón para buscar por caracteres individuales (con límite de relevancia)
            $search_chars = str_split($filtro_buscar);
            $search_pattern = '%' . implode('%', array_slice($search_chars, 0, $min_match_length)) . '%';
            
            $sql .= " AND (
                p.nombre_producto LIKE ? OR 
                p.descripcion_producto LIKE ? OR 
                m.nombre_marca LIKE ? OR
                c.nombre_categoria LIKE ? OR
                (
                    CHAR_LENGTH(p.nombre_producto) - CHAR_LENGTH(REPLACE(LOWER(p.nombre_producto), ?, '')) >= ? OR
                    p.nombre_producto LIKE ?
                )
            )";
            
            // Primera coincidencia: búsqueda normal (más relevante)
            $search_term = "%{$filtro_buscar}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            
            // Verificar que contenga suficientes caracteres del término de búsqueda
            $params[] = strtolower($filtro_buscar);
            $params[] = $min_match_length;
            
            // Patrón de caracteres espaciados (para coincidencias flexibles)
            $params[] = $search_pattern;
            
        } else {
            // Para búsquedas cortas (1-2 caracteres), solo coincidencia exacta
            $sql .= " AND (
                p.nombre_producto LIKE ? OR 
                p.descripcion_producto LIKE ? OR 
                m.nombre_marca LIKE ? OR
                c.nombre_categoria LIKE ?
            )";
            $search_term = "%{$filtro_buscar}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
    }

    $sql .= " GROUP BY p.id_producto";
    $sql .= " HAVING (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) BETWEEN ? AND ?";
    $params[] = $filtro_precio_min;
    $params[] = $filtro_precio_max;

    // Agregar ordenamiento según el filtro seleccionado
    switch($filtro_ordenar) {
        case 'price_asc':
            $sql .= " ORDER BY precio_final ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY precio_final DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY p.nombre_producto ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY p.nombre_producto DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY p.id_producto DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY calificacion_promedio DESC, total_resenas DESC";
            break;
        default:
            $sql .= " ORDER BY p.id_producto DESC";
            break;
    }

    $productos = executeQuery($sql, $params);

    // Obtener favoritos del usuario si está logueado
    $favoritos_ids = [];
    if (isset($_SESSION['user_id'])) {
        error_log("=== GET_PRODUCTS_FILTERED DEBUG ===");
        error_log("Usuario logueado ID: " . $_SESSION['user_id']);
        
        $favoritos = executeQuery("
            SELECT id_producto 
            FROM favorito 
            WHERE id_usuario = ?
        ", [$_SESSION['user_id']]);
        
        error_log("Favoritos encontrados: " . count($favoritos));
        
        if ($favoritos && !empty($favoritos)) {
            $favoritos_ids = array_column($favoritos, 'id_producto');
            error_log("IDs de favoritos: " . implode(', ', $favoritos_ids));
        }
    } else {
        error_log("=== GET_PRODUCTS_FILTERED DEBUG ===");
        error_log("⚠️ Usuario NO logueado - SESSION user_id no existe");
    }

    // Agregar favorito flag a cada producto
    foreach($productos as &$producto) {
        $producto['es_favorito'] = in_array($producto['id_producto'], $favoritos_ids);
        error_log("Producto ID {$producto['id_producto']}: es_favorito = " . ($producto['es_favorito'] ? 'true' : 'false'));
    }

    // Retornar JSON con productos
    echo json_encode([
        'success' => true,
        'products' => $productos,
        'count' => count($productos)
    ]);

} catch (Exception $e) {
    error_log("Error en get_products_filtered.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al filtrar productos: ' . $e->getMessage()
    ]);
}

exit; // Evitar que se ejecute el código HTML de abajo
