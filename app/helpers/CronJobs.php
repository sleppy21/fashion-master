<?php
/**
 * TAREAS AUTOMÁTICAS - CRON JOBS
 * 
 * Este archivo debe ejecutarse automáticamente cada cierto tiempo
 * Puedes configurarlo en:
 * - Windows Task Scheduler
 * - Linux Cron (crontab -e)
 * - Panel de hosting (cPanel, Plesk, etc.)
 * 
 * Ejemplo de configuración en Linux:
 * 0 * * * * php /path/to/fashion-master/app/helpers/CronJobs.php >> /path/to/logs/cron.log 2>&1
 * 
 * Esto ejecuta el script cada hora
 */

// Establecer tiempo de ejecución máximo
set_time_limit(300); // 5 minutos

// Cargar dependencias
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/NotificationManager.php';

// Log de inicio
$log_file = __DIR__ . '/../../logs/cron_notifications.log';
$start_time = date('Y-m-d H:i:s');
logMessage("=== INICIO DE TAREAS AUTOMÁTICAS: {$start_time} ===");

// ============================================
// TAREA 1: CARRITOS ABANDONADOS (24 HORAS)
// ============================================
function notifyAbandonedCarts() {
    global $conn;
    
    try {
        // Buscar carritos abandonados (sin actividad en 24 horas)
        // y sin notificación enviada recientemente
        $query = "SELECT DISTINCT c.id_usuario, 
                  COUNT(c.id_carrito) as cantidad_productos,
                  SUM(p.precio_producto * c.cantidad) as total
                  FROM carrito c
                  INNER JOIN producto p ON c.id_producto = p.id_producto
                  LEFT JOIN notificacion n ON n.id_usuario = c.id_usuario 
                      AND n.titulo_notificacion LIKE '%carrito%' 
                      AND n.fecha_creacion_notificacion > DATE_SUB(NOW(), INTERVAL 7 DAY)
                  WHERE c.fecha_agregado < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                  AND n.id_notificacion IS NULL
                  GROUP BY c.id_usuario
                  LIMIT 100";
        
        $stmt = $conn->query($query);
        $carritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $nm = getNotificationManager($conn);
        $enviados = 0;
        
        foreach ($carritos as $carrito) {
            if ($nm->notifyAbandonedCart(
                $carrito['id_usuario'], 
                $carrito['cantidad_productos'], 
                $carrito['total']
            )) {
                $enviados++;
            }
        }
        
        logMessage("Carritos abandonados: {$enviados} notificaciones enviadas");
        return $enviados;
        
    } catch (Exception $e) {
        logMessage("ERROR en carritos abandonados: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// TAREA 2: STOCK BAJO EN FAVORITOS
// ============================================
function notifyLowStockFavorites() {
    global $conn;
    
    try {
        // Buscar productos con stock bajo (≤5) en favoritos
        // Sin notificación enviada en los últimos 3 días
        $query = "SELECT DISTINCT 
                  f.id_usuario,
                  p.id_producto,
                  p.nombre_producto,
                  p.stock_actual_producto
                  FROM favorito f
                  INNER JOIN producto p ON f.id_producto = p.id_producto
                  LEFT JOIN notificacion n ON n.id_usuario = f.id_usuario 
                      AND n.mensaje_notificacion LIKE CONCAT('%', p.nombre_producto, '%')
                      AND n.fecha_creacion_notificacion > DATE_SUB(NOW(), INTERVAL 3 DAY)
                  WHERE p.stock_actual_producto <= 5 
                  AND p.stock_actual_producto > 0
                  AND p.status_producto = 1
                  AND n.id_notificacion IS NULL
                  LIMIT 200";
        
        $stmt = $conn->query($query);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $nm = getNotificationManager($conn);
        $enviados = 0;
        
        foreach ($productos as $item) {
            if ($nm->notifyFavoriteLowStock(
                $item['id_usuario'],
                $item['id_producto'],
                $item['nombre_producto'],
                $item['stock_actual_producto']
            )) {
                $enviados++;
            }
        }
        
        logMessage("Stock bajo en favoritos: {$enviados} notificaciones enviadas");
        return $enviados;
        
    } catch (Exception $e) {
        logMessage("ERROR en stock bajo: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// TAREA 3: PRODUCTOS DE VUELTA EN STOCK
// ============================================
function notifyBackInStock() {
    global $conn;
    
    try {
        // Buscar productos que volvieron a tener stock en las últimas 2 horas
        // y están en favoritos
        $query = "SELECT DISTINCT 
                  f.id_usuario,
                  p.id_producto,
                  p.nombre_producto
                  FROM favorito f
                  INNER JOIN producto p ON f.id_producto = p.id_producto
                  LEFT JOIN notificacion n ON n.id_usuario = f.id_usuario 
                      AND n.mensaje_notificacion LIKE CONCAT('%', p.nombre_producto, '%vuelta%')
                      AND n.fecha_creacion_notificacion > DATE_SUB(NOW(), INTERVAL 1 DAY)
                  WHERE p.stock_actual_producto > 0
                  AND p.status_producto = 1
                  AND n.id_notificacion IS NULL
                  -- Aquí idealmente tendrías un campo de última actualización de stock
                  LIMIT 100";
        
        $stmt = $conn->query($query);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $nm = getNotificationManager($conn);
        $enviados = 0;
        
        foreach ($productos as $item) {
            if ($nm->notifyProductBackInStock(
                $item['id_usuario'],
                $item['id_producto'],
                $item['nombre_producto']
            )) {
                $enviados++;
            }
        }
        
        logMessage("Productos de vuelta en stock: {$enviados} notificaciones enviadas");
        return $enviados;
        
    } catch (Exception $e) {
        logMessage("ERROR en vuelta en stock: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// TAREA 4: RECORDATORIO DE OFERTAS (SEMANAL)
// ============================================
function notifyWeeklyOffers() {
    global $conn;
    
    try {
        // Solo ejecutar los domingos
        if (date('w') != 0) {
            return 0;
        }
        
        // Obtener usuarios activos
        $query = "SELECT id_usuario, nombre_usuario 
                  FROM usuario 
                  WHERE status_usuario = 1 
                  AND ultimo_acceso > DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $conn->query($query);
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $titulo = "🎁 ¡Ofertas de la semana!";
        $mensaje = "Descubre las mejores ofertas de esta semana. Productos seleccionados con hasta 50% de descuento. ¡No te las pierdas!";
        
        $nm = getNotificationManager($conn);
        $enviados = $nm->notifyMultipleUsers($usuarios, $titulo, $mensaje, 'info', 'media', 'shop.php');
        
        logMessage("Ofertas semanales: {$enviados} notificaciones enviadas");
        return $enviados;
        
    } catch (Exception $e) {
        logMessage("ERROR en ofertas semanales: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// TAREA 5: LIMPIAR NOTIFICACIONES ANTIGUAS
// ============================================
function cleanOldNotifications() {
    global $conn;
    
    try {
        // Eliminar notificaciones leídas de más de 30 días
        $query = "UPDATE notificacion 
                  SET estado_notificacion = 'eliminado' 
                  WHERE leida_notificacion = 1 
                  AND fecha_lectura_notificacion < DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND estado_notificacion = 'activo'";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $eliminadas = $stmt->rowCount();
        
        logMessage("Notificaciones antiguas eliminadas: {$eliminadas}");
        return $eliminadas;
        
    } catch (Exception $e) {
        logMessage("ERROR al limpiar notificaciones: " . $e->getMessage());
        return 0;
    }
}

// ============================================
// FUNCIÓN DE LOG
// ============================================
function logMessage($message) {
    global $log_file;
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    
    // Crear directorio de logs si no existe
    $log_dir = dirname($log_file);
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Escribir en el archivo de log
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // También mostrar en consola si se ejecuta desde CLI
    if (php_sapi_name() === 'cli') {
        echo $log_entry;
    }
}

// ============================================
// EJECUTAR TODAS LAS TAREAS
// ============================================
try {
    $resultados = [
        'carritos_abandonados' => notifyAbandonedCarts(),
        'stock_bajo' => notifyLowStockFavorites(),
        'vuelta_stock' => notifyBackInStock(),
        'ofertas_semanales' => notifyWeeklyOffers(),
        'limpieza' => cleanOldNotifications()
    ];
    
    $total_enviadas = array_sum($resultados);
    
    logMessage("Total de notificaciones enviadas: {$total_enviadas}");
    logMessage("Desglose: " . json_encode($resultados));
    
} catch (Exception $e) {
    logMessage("ERROR CRÍTICO: " . $e->getMessage());
}

// Log de fin
$end_time = date('Y-m-d H:i:s');
$execution_time = time() - strtotime($start_time);
logMessage("=== FIN DE TAREAS AUTOMÁTICAS: {$end_time} (Tiempo: {$execution_time}s) ===\n");

?>
