<?php
/**
 * API del Dashboard - Datos en Tiempo Real
 * Endpoint para obtener estadísticas actualizadas del sistema
 */

// IMPORTANTE: Iniciar sesión ANTES de cualquier output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación PRIMERO - usar 'user_id' que es la variable en admin.php
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode([
        'error' => 'No autorizado',
        'mensaje' => 'Debes iniciar sesión para acceder a esta API',
        'debug' => [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_keys' => array_keys($_SESSION ?? [])
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Headers para JSON (después de verificar sesión)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Incluir configuración de base de datos
require_once __DIR__ . '/../../../config/conexion.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    $response = [];
    
    // ===== ESTADÍSTICAS PRINCIPALES =====
    
    // Total de productos
    $query = "SELECT COUNT(*) as total FROM producto";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $response['total_productos'] = $total_productos;
    
    // Productos activos (estado = 'activo')
    $query = "SELECT COUNT(*) as total FROM producto WHERE estado = 'activo'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['productos_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de categorías
    $query = "SELECT COUNT(*) as total FROM categoria";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_categorias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $response['total_categorias'] = $total_categorias;
    
    // Total de usuarios
    $query = "SELECT COUNT(*) as total FROM usuario";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $response['total_usuarios'] = $total_usuarios;
    
    // Valor total del inventario (precio_producto * stock_actual_producto)
    $query = "SELECT 
                COALESCE(SUM(precio_producto * stock_actual_producto), 0) as valor_total,
                COUNT(*) as total_con_stock
              FROM producto 
              WHERE stock_actual_producto > 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $inventario = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['valor_inventario'] = number_format($inventario['valor_total'], 2);
    $response['productos_con_stock'] = $inventario['total_con_stock'];
    
    // Productos con stock bajo (menor a stock_minimo_producto o menos de 10)
    $query = "SELECT COUNT(*) as total 
              FROM producto 
              WHERE stock_actual_producto < stock_minimo_producto 
              AND stock_actual_producto > 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de marcas
    $query = "SELECT COUNT(*) as total FROM marca";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['total_marcas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pedidos de hoy
    $query = "SELECT COUNT(*) as total FROM pedido WHERE DATE(fecha_pedido) = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['pedidos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de pedidos
    $query = "SELECT COUNT(*) as total FROM pedido";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['total_pedidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ventas de hoy (total_pedido)
    $query = "SELECT 
                COUNT(*) as total, 
                COALESCE(SUM(total_pedido), 0) as monto 
              FROM pedido 
              WHERE DATE(fecha_pedido) = CURDATE() 
              AND estado_pedido NOT IN ('cancelado')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['ventas_hoy'] = $ventas_hoy['total'];
    $response['monto_ventas_hoy'] = number_format($ventas_hoy['monto'], 2);
    
    // Pedidos por estado
    $query = "SELECT 
                estado_pedido, 
                COUNT(*) as cantidad 
              FROM pedido 
              GROUP BY estado_pedido";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['pedidos_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Reseñas
    $query = "SELECT COUNT(*) as total FROM resena";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['total_resenas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Favoritos
    $query = "SELECT COUNT(*) as total FROM favorito";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['total_favoritos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // ===== GRÁFICA DE STOCK POR CATEGORÍA =====
    $query = "SELECT 
                c.nombre_categoria as categoria,
                COUNT(p.id_producto) as cantidad,
                COALESCE(SUM(p.stock_actual_producto), 0) as stock_total
              FROM categoria c
              LEFT JOIN producto p ON c.id_categoria = p.id_categoria
              GROUP BY c.id_categoria, c.nombre_categoria
              ORDER BY stock_total DESC
              LIMIT 7";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['stock_por_categoria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== GRÁFICA DE PRODUCTOS POR GÉNERO =====
    $query = "SELECT 
                genero_producto as genero,
                COUNT(*) as cantidad
              FROM producto
              WHERE genero_producto IS NOT NULL
              GROUP BY genero_producto
              ORDER BY cantidad DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $generos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Traducir códigos de género a nombres legibles
    foreach ($generos as &$genero) {
        switch ($genero['genero']) {
            case 'M':
                $genero['genero'] = 'Masculino';
                break;
            case 'F':
                $genero['genero'] = 'Femenino';
                break;
            case 'Unisex':
                $genero['genero'] = 'Unisex';
                break;
            case 'Kids':
                $genero['genero'] = 'Niños';
                break;
        }
    }
    $response['productos_por_genero'] = $generos;
    
    // ===== PRODUCTOS POR MARCA =====
    $query = "SELECT 
                m.nombre_marca as marca,
                COUNT(p.id_producto) as total
              FROM marca m
              LEFT JOIN producto p ON m.id_marca = p.id_marca AND p.estado = 'activo'
              GROUP BY m.id_marca, m.nombre_marca
              ORDER BY total DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['productos_por_marca'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== TOP 5 PRODUCTOS MÁS VENDIDOS =====
    $query = "SELECT 
                p.id_producto,
                p.nombre_producto as nombre,
                p.precio_producto as precio,
                p.imagen_producto as imagen_principal,
                COUNT(dp.id_detalle_pedido) as ventas,
                COALESCE(SUM(dp.cantidad_detalle), 0) as unidades_vendidas,
                COALESCE(SUM(dp.subtotal_detalle), 0) as ingresos
              FROM producto p
              LEFT JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
              LEFT JOIN pedido ped ON dp.id_pedido = ped.id_pedido
              WHERE ped.estado_pedido NOT IN ('cancelado') OR ped.id_pedido IS NULL
              GROUP BY p.id_producto, p.nombre_producto, p.precio_producto, p.imagen_producto
              ORDER BY unidades_vendidas DESC, p.stock_actual_producto DESC
              LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $response['top_productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== ACTIVIDAD RECIENTE =====
    // Si hay pedidos, mostrar pedidos; si no, mostrar productos recientes
    if ($response['total_pedidos'] > 0) {
        $query = "SELECT 
                    'pedido' as tipo,
                    p.id_pedido as id,
                    CONCAT('Pedido #', p.id_pedido, ' - ', p.nombre_cliente_pedido) as descripcion,
                    p.fecha_pedido as fecha,
                    p.estado_pedido as estado,
                    p.total_pedido as monto
                  FROM pedido p
                  ORDER BY p.fecha_pedido DESC
                  LIMIT 5";
    } else {
        $query = "SELECT 
                    'producto' as tipo,
                    id_producto as id,
                    CONCAT('Producto: ', nombre_producto) as descripcion,
                    fecha_creacion_producto as fecha,
                    estado as estado,
                    precio_producto as monto
                  FROM producto
                  ORDER BY fecha_creacion_producto DESC
                  LIMIT 5";
    }
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['actividad_reciente'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== PRODUCTOS CON STOCK BAJO =====
    $query = "SELECT 
                id_producto,
                nombre_producto as nombre,
                stock_actual_producto as stock,
                stock_minimo_producto as stock_minimo,
                precio_producto as precio
              FROM producto
              WHERE stock_actual_producto < stock_minimo_producto 
              AND stock_actual_producto > 0
              ORDER BY stock_actual_producto ASC
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $response['stock_bajo_productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== ESTADÍSTICAS DEL SISTEMA =====
    // PHP Version
    $response['php_version'] = phpversion();
    
    // ===== INFORMACIÓN DEL SISTEMA (REALES) =====
    // Uso de memoria real
    $response['memoria_uso'] = round(memory_get_usage() / 1024 / 1024, 2);
    $response['memoria_limite'] = ini_get('memory_limit');
    
    // Espacio en disco real (del directorio actual)
    $total_space = disk_total_space(".");
    $free_space = disk_free_space(".");
    $used_space = $total_space - $free_space;
    $response['espacio_usado_porcentaje'] = round(($used_space / $total_space) * 100, 1);
    $response['espacio_usado_gb'] = round($used_space / 1024 / 1024 / 1024, 2);
    $response['espacio_total_gb'] = round($total_space / 1024 / 1024 / 1024, 2);
    
    // Estado del servidor
    $response['servidor_estado'] = 'online';
    $response['servidor_web'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    
    // ===== ACTIVIDAD RECIENTE (REALES - últimas acciones) =====
    $actividades = [];
    
    // Últimos productos creados (últimos 3)
    $stmt = $conn->prepare("
        SELECT nombre_producto, fecha_creacion_producto, stock_actual_producto
        FROM producto 
        WHERE estado = 'activo'
        ORDER BY fecha_creacion_producto DESC 
        LIMIT 3
    ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actividades[] = [
            'tipo' => 'producto_creado',
            'titulo' => 'Producto creado',
            'descripcion' => $row['nombre_producto'] . ' - Stock: ' . $row['stock_actual_producto'],
            'fecha' => $row['fecha_creacion_producto'],
            'icono' => 'fa-plus',
            'color' => '#10b981'
        ];
    }
    
    // Últimos usuarios registrados (últimos 2)
    $stmt = $conn->prepare("
        SELECT nombre_usuario, email_usuario, fecha_registro
        FROM usuario 
        ORDER BY fecha_registro DESC 
        LIMIT 2
    ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actividades[] = [
            'tipo' => 'usuario_registrado',
            'titulo' => 'Usuario registrado',
            'descripcion' => $row['nombre_usuario'] . ' (' . $row['email_usuario'] . ')',
            'fecha' => $row['fecha_registro'],
            'icono' => 'fa-user-plus',
            'color' => '#3b82f6'
        ];
    }
    
    // Productos con stock bajo (últimos 2)
    $stmt = $conn->prepare("
        SELECT nombre_producto, stock_actual_producto, stock_minimo_producto
        FROM producto 
        WHERE stock_actual_producto < stock_minimo_producto
        AND estado = 'activo'
        ORDER BY stock_actual_producto ASC 
        LIMIT 2
    ");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actividades[] = [
            'tipo' => 'stock_bajo',
            'titulo' => 'Alerta de stock bajo',
            'descripcion' => $row['nombre_producto'] . ' - Stock: ' . $row['stock_actual_producto'] . ' (Mínimo: ' . $row['stock_minimo_producto'] . ')',
            'fecha' => date('Y-m-d H:i:s'), // Fecha actual para alertas
            'icono' => 'fa-exclamation-triangle',
            'color' => '#ef4444'
        ];
    }
    
    // Ordenar por fecha descendente y limitar a 5 actividades
    usort($actividades, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    $response['actividades_recientes'] = array_slice($actividades, 0, 5);
    
    // Timestamp de actualización
    $response['ultima_actualizacion'] = date('Y-m-d H:i:s');
    $response['timestamp'] = time();
    
    // ===== RESPUESTA EXITOSA =====
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la base de datos',
        'mensaje' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'mensaje' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
