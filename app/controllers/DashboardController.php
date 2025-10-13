<?php
/**
 * DashboardController
 * 
 * Controlador para el dashboard del panel administrativo
 * Proporciona estadísticas en tiempo real usando Fetch API
 * 
 * @package Fashion Store
 * @version 3.0.0 - Dashboard Mejorado con Estadísticas Completas
 */

session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado'
    ]);
    exit;
}

require_once dirname(__DIR__, 2) . '/config/conexion.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getStats':
        getStats($conn);
        break;
    
    case 'getRecentActivity':
        getRecentActivity($conn);
        break;
    
    case 'getStockAlerts':
        getStockAlerts($conn);
        break;
    
    case 'getSalesData':
        getSalesData($conn);
        break;
    
    case 'getTopProducts':
        getTopProducts($conn);
        break;
    
    case 'getGenderDistribution':
        getGenderDistribution($conn);
        break;
    
    case 'getCategoryStats':
        getCategoryStats($conn);
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Acción no válida'
        ]);
        break;
}

/**
 * Obtener estadísticas generales del dashboard
 */
function getStats($conn) {
    try {
        $stats = [];
        
        // Total de productos ACTIVOS
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE estado = 'activo'");
        $stmt->execute();
        $stats['total_productos'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de usuarios ACTIVOS
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE estado_usuario = 'activo'");
        $stmt->execute();
        $stats['total_usuarios'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de categorías ACTIVAS
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM categoria WHERE estado_categoria = 'activo'");
        $stmt->execute();
        $stats['total_categorias'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de marcas ACTIVAS
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM marca WHERE estado_marca = 'activo'");
        $stmt->execute();
        $stats['total_marcas'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de pedidos
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedido");
        $stmt->execute();
        $stats['total_pedidos'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de reseñas
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM resena WHERE aprobada = 1");
        $stmt->execute();
        $stats['total_resenas'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de favoritos
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM favorito");
        $stmt->execute();
        $stats['total_favoritos'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de items en carritos
        $stmt = $conn->prepare("SELECT COALESCE(SUM(cantidad_carrito), 0) as total FROM carrito");
        $stmt->execute();
        $stats['total_carrito'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos con stock bajo (menos de 10 unidades)
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE stock_actual_producto < 10 AND stock_actual_producto > 0 AND estado = 'activo'");
        $stmt->execute();
        $stats['productos_stock_bajo'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos sin stock
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE stock_actual_producto = 0 AND estado = 'activo'");
        $stmt->execute();
        $stats['productos_sin_stock'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Valor total del inventario
        $stmt = $conn->prepare("SELECT COALESCE(SUM(precio_producto * stock_actual_producto), 0) as total FROM producto WHERE estado = 'activo'");
        $stmt->execute();
        $stats['valor_inventario'] = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ventas del mes (simulado con total de pedidos del mes)
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total_pedido), 0) as total FROM pedido WHERE MONTH(fecha_pedido) = MONTH(CURDATE()) AND YEAR(fecha_pedido) = YEAR(CURDATE())");
        $stmt->execute();
        $stats['ventas_mes'] = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Pedidos pendientes
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedido WHERE estado_pedido = 'pendiente'");
        $stmt->execute();
        $stats['pedidos_pendientes'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Distribución de stock para el gráfico
        $stats['stock_distribution'] = [
            'saludable' => $stats['total_productos'] - $stats['productos_stock_bajo'] - $stats['productos_sin_stock'],
            'bajo' => $stats['productos_stock_bajo'],
            'sin_stock' => $stats['productos_sin_stock']
        ];
        
        // Productos agregados hoy
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE DATE(fecha_creacion_producto) = CURDATE() AND estado = 'activo'");
        $stmt->execute();
        $stats['productos_hoy'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos agregados esta semana
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE YEARWEEK(fecha_creacion_producto) = YEARWEEK(CURDATE()) AND estado = 'activo'");
        $stmt->execute();
        $stats['productos_semana'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Promedio de calificación de productos
        $stmt = $conn->prepare("SELECT COALESCE(AVG(calificacion), 0) as promedio FROM resena WHERE aprobada = 1");
        $stmt->execute();
        $stats['calificacion_promedio'] = round((float) $stmt->fetch(PDO::FETCH_ASSOC)['promedio'], 1);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener actividad reciente del sistema
 */
function getRecentActivity($conn) {
    try {
        $activities = [];
        
        // Últimos 10 productos agregados
        $stmt = $conn->prepare("
            SELECT 
                p.id_producto,
                p.nombre_producto,
                p.fecha_creacion_producto as fecha_registro,
                c.nombre_categoria,
                p.stock_actual_producto,
                p.precio_producto
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            WHERE p.estado = 'activo'
            ORDER BY p.fecha_creacion_producto DESC
            LIMIT 10
        ");
        $stmt->execute();
        $activities['productos_recientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Últimos 10 usuarios registrados
        $stmt = $conn->prepare("
            SELECT 
                id_usuario,
                nombre_usuario,
                apellido_usuario,
                email_usuario,
                rol_usuario,
                fecha_registro
            FROM usuario
            WHERE estado_usuario = 'activo'
            ORDER BY fecha_registro DESC
            LIMIT 10
        ");
        $stmt->execute();
        $activities['usuarios_recientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Últimos 10 pedidos
        $stmt = $conn->prepare("
            SELECT 
                id_pedido,
                nombre_cliente_pedido,
                total_pedido,
                estado_pedido,
                fecha_pedido
            FROM pedido
            ORDER BY fecha_pedido DESC
            LIMIT 10
        ");
        $stmt->execute();
        $activities['pedidos_recientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'activities' => $activities,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener actividad reciente: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener alertas de stock bajo
 */
function getStockAlerts($conn) {
    try {
        // Productos con stock bajo
        $stmt = $conn->prepare("
            SELECT 
                p.id_producto,
                p.nombre_producto,
                p.stock_actual_producto,
                c.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.stock_actual_producto < 10 
            AND p.stock_actual_producto > 0 
            AND p.estado = 'activo'
            ORDER BY p.stock_actual_producto ASC
            LIMIT 20
        ");
        $stmt->execute();
        $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Productos sin stock
        $stmt = $conn->prepare("
            SELECT 
                p.id_producto,
                p.nombre_producto,
                c.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.stock_actual_producto = 0 
            AND p.estado = 'activo'
            ORDER BY p.nombre_producto ASC
            LIMIT 20
        ");
        $stmt->execute();
        $out_of_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'alerts' => [
                'low_stock' => $low_stock,
                'out_of_stock' => $out_of_stock
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener alertas de stock: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener datos de ventas mensuales
 */
function getSalesData($conn) {
    try {
        // Ventas de los últimos 6 meses
        $stmt = $conn->prepare("
            SELECT 
                DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
                COUNT(*) as total_pedidos,
                COALESCE(SUM(total_pedido), 0) as total_ventas
            FROM pedido
            WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(fecha_pedido, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->execute();
        $ventas_mensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ventas por estado
        $stmt = $conn->prepare("
            SELECT 
                estado_pedido,
                COUNT(*) as cantidad,
                COALESCE(SUM(total_pedido), 0) as total
            FROM pedido
            GROUP BY estado_pedido
        ");
        $stmt->execute();
        $ventas_por_estado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'sales' => [
                'mensuales' => $ventas_mensuales,
                'por_estado' => $ventas_por_estado
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener datos de ventas: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener productos más populares
 */
function getTopProducts($conn) {
    try {
        // Productos con más favoritos
        $stmt = $conn->prepare("
            SELECT 
                p.id_producto,
                p.nombre_producto,
                p.precio_producto,
                COUNT(f.id_favorito) as total_favoritos,
                c.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN favorito f ON p.id_producto = f.id_producto
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.estado = 'activo'
            GROUP BY p.id_producto
            ORDER BY total_favoritos DESC
            LIMIT 10
        ");
        $stmt->execute();
        $mas_favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Productos con mejores reseñas
        $stmt = $conn->prepare("
            SELECT 
                p.id_producto,
                p.nombre_producto,
                p.precio_producto,
                AVG(r.calificacion) as calificacion_promedio,
                COUNT(r.id_resena) as total_resenas,
                c.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.estado = 'activo'
            GROUP BY p.id_producto
            HAVING COUNT(r.id_resena) > 0
            ORDER BY calificacion_promedio DESC, total_resenas DESC
            LIMIT 10
        ");
        $stmt->execute();
        $mejor_calificados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Productos más agregados al carrito
        $stmt = $conn->prepare("
            SELECT 
                p.id_producto,
                p.nombre_producto,
                p.precio_producto,
                SUM(c.cantidad_carrito) as total_en_carritos,
                cat.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN carrito c ON p.id_producto = c.id_producto
            LEFT JOIN categoria cat ON p.id_categoria = cat.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.estado = 'activo'
            GROUP BY p.id_producto
            HAVING total_en_carritos > 0
            ORDER BY total_en_carritos DESC
            LIMIT 10
        ");
        $stmt->execute();
        $mas_en_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'top_products' => [
                'favoritos' => $mas_favoritos,
                'calificados' => $mejor_calificados,
                'carrito' => $mas_en_carrito
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener productos populares: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener distribución de productos por género
 */
function getGenderDistribution($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                genero_producto,
                COUNT(*) as cantidad,
                COALESCE(SUM(stock_actual_producto), 0) as stock_total,
                COALESCE(SUM(precio_producto * stock_actual_producto), 0) as valor_total
            FROM producto
            WHERE estado = 'activo'
            GROUP BY genero_producto
        ");
        $stmt->execute();
        $distribucion = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'distribution' => $distribucion,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener distribución por género: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener estadísticas por categoría
 */
function getCategoryStats($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                c.id_categoria,
                c.nombre_categoria,
                COUNT(p.id_producto) as total_productos,
                COALESCE(SUM(p.stock_actual_producto), 0) as stock_total,
                COALESCE(AVG(p.precio_producto), 0) as precio_promedio,
                COALESCE(SUM(p.precio_producto * p.stock_actual_producto), 0) as valor_inventario
            FROM categoria c
            LEFT JOIN producto p ON c.id_categoria = p.id_categoria AND p.estado = 'activo'
            WHERE c.estado_categoria = 'activo'
            GROUP BY c.id_categoria
            ORDER BY total_productos DESC
        ");
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'categories' => $categorias,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener estadísticas de categorías: ' . $e->getMessage()
        ]);
    }
}
?>
