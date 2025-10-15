<?php
/**
 * EJEMPLOS DE INTEGRACIÓN DEL SISTEMA DE NOTIFICACIONES
 * 
 * Este archivo muestra cómo integrar el NotificationManager
 * en diferentes partes del sistema
 */

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../helpers/NotificationManager.php';

// ============================================
// EJEMPLO 1: PROCESO DE CHECKOUT
// ============================================
/*
En checkout.php o process_checkout.php, después de crear el pedido:

session_start();
$id_usuario = $_SESSION['user_id'];
$id_pedido = ...; // ID del pedido recién creado
$total = ...; // Total del pedido

// Crear notificación de pedido confirmado
$nm = getNotificationManager();
$nm->notifyOrderConfirmed($id_usuario, $id_pedido, $total);
*/

// ============================================
// EJEMPLO 2: ACTUALIZACIÓN DE ESTADO DE PEDIDO
// ============================================
/*
Cuando cambias el estado de un pedido en el admin:

function actualizarEstadoPedido($id_pedido, $nuevo_estado) {
    global $conn;
    
    // Actualizar estado en DB
    $query = "UPDATE pedido SET estado = ? WHERE id_pedido = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$nuevo_estado, $id_pedido]);
    
    // Obtener ID del usuario del pedido
    $query = "SELECT id_usuario FROM pedido WHERE id_pedido = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_pedido]);
    $id_usuario = $stmt->fetchColumn();
    
    // Enviar notificación según el nuevo estado
    $nm = getNotificationManager();
    
    switch($nuevo_estado) {
        case 'procesando':
            $nm->notifyOrderProcessing($id_usuario, $id_pedido);
            break;
        case 'enviado':
            $numero_seguimiento = "1234567890"; // Obtener de DB
            $nm->notifyOrderShipped($id_usuario, $id_pedido, $numero_seguimiento);
            break;
        case 'entregado':
            $nm->notifyOrderDelivered($id_usuario, $id_pedido);
            break;
        case 'cancelado':
            $nm->notifyOrderCancelled($id_usuario, $id_pedido);
            break;
    }
}
*/

// ============================================
// EJEMPLO 3: PRODUCTO DE VUELTA EN STOCK
// ============================================
/*
Cuando actualizas el stock de un producto en el admin:

function actualizarStockProducto($id_producto, $nuevo_stock) {
    global $conn;
    
    // Obtener stock anterior
    $query = "SELECT stock_actual_producto FROM producto WHERE id_producto = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id_producto]);
    $stock_anterior = $stmt->fetchColumn();
    
    // Actualizar stock
    $query = "UPDATE producto SET stock_actual_producto = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$nuevo_stock, $id_producto]);
    
    // Si el producto estaba agotado y ahora hay stock
    if ($stock_anterior == 0 && $nuevo_stock > 0) {
        // Obtener nombre del producto
        $query = "SELECT nombre_producto FROM producto WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_producto]);
        $nombre_producto = $stmt->fetchColumn();
        
        // Notificar a todos los usuarios que tienen el producto en favoritos
        $query = "SELECT DISTINCT id_usuario FROM favorito WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_producto]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $nm = getNotificationManager();
        foreach ($usuarios as $id_usuario) {
            $nm->notifyProductBackInStock($id_usuario, $id_producto, $nombre_producto);
        }
    }
}
*/

// ============================================
// EJEMPLO 4: DESCUENTO EN FAVORITOS
// ============================================
/*
Cuando aplicas un descuento a un producto:

function aplicarDescuento($id_producto, $porcentaje_descuento) {
    global $conn;
    
    // Actualizar descuento
    $query = "UPDATE producto SET descuento_porcentaje_producto = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$porcentaje_descuento, $id_producto]);
    
    if ($porcentaje_descuento > 0) {
        // Obtener nombre del producto
        $query = "SELECT nombre_producto FROM producto WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_producto]);
        $nombre_producto = $stmt->fetchColumn();
        
        // Notificar a usuarios con el producto en favoritos
        $query = "SELECT DISTINCT id_usuario FROM favorito WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id_producto]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $nm = getNotificationManager();
        foreach ($usuarios as $id_usuario) {
            $nm->notifyFavoriteOnSale($id_usuario, $id_producto, $nombre_producto, $porcentaje_descuento);
        }
    }
}
*/

// ============================================
// EJEMPLO 5: CARRITO ABANDONADO (CRON JOB)
// ============================================
/*
Crear un archivo cron_abandoned_cart.php para ejecutar diariamente:

<?php
require_once 'config/conexion.php';
require_once 'app/helpers/NotificationManager.php';

// Buscar usuarios con carritos abandonados (sin actividad en 24 horas)
$query = "SELECT DISTINCT c.id_usuario, 
          COUNT(c.id_carrito) as cantidad_productos,
          SUM(p.precio_producto * c.cantidad) as total
          FROM carrito c
          INNER JOIN producto p ON c.id_producto = p.id_producto
          WHERE c.fecha_agregado < DATE_SUB(NOW(), INTERVAL 24 HOUR)
          GROUP BY c.id_usuario";

$stmt = $conn->query($query);
$carritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nm = getNotificationManager();
foreach ($carritos as $carrito) {
    $nm->notifyAbandonedCart(
        $carrito['id_usuario'], 
        $carrito['cantidad_productos'], 
        $carrito['total']
    );
}

echo "Notificaciones enviadas: " . count($carritos);
?>
*/

// ============================================
// EJEMPLO 6: PROMOCIÓN FLASH (ADMIN)
// ============================================
/*
En el panel de admin, para enviar una promoción a todos:

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_promo'])) {
    $titulo = $_POST['titulo'];
    $mensaje = $_POST['mensaje'];
    $url = $_POST['url'] ?? 'shop.php';
    
    $nm = getNotificationManager();
    $enviados = $nm->notifyAllUsers($titulo, $mensaje, 'info', 'alta', $url);
    
    echo "Notificación enviada a {$enviados} usuarios";
}
*/

// ============================================
// EJEMPLO 7: STOCK BAJO EN FAVORITOS (CRON)
// ============================================
/*
Ejecutar diariamente para alertar sobre stock bajo:

<?php
require_once 'config/conexion.php';
require_once 'app/helpers/NotificationManager.php';

// Buscar productos con stock bajo que están en favoritos
$query = "SELECT DISTINCT 
          f.id_usuario,
          p.id_producto,
          p.nombre_producto,
          p.stock_actual_producto
          FROM favorito f
          INNER JOIN producto p ON f.id_producto = p.id_producto
          WHERE p.stock_actual_producto <= 5 AND p.stock_actual_producto > 0";

$stmt = $conn->query($query);
$productos_bajo_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nm = getNotificationManager();
foreach ($productos_bajo_stock as $item) {
    $nm->notifyFavoriteLowStock(
        $item['id_usuario'],
        $item['id_producto'],
        $item['nombre_producto'],
        $item['stock_actual_producto']
    );
}
?>
*/

// ============================================
// EJEMPLO 8: CAMBIO DE CONTRASEÑA
// ============================================
/*
En change-password.php, después de cambiar la contraseña:

if ($password_changed_successfully) {
    session_start();
    $id_usuario = $_SESSION['user_id'];
    
    $nm = getNotificationManager();
    $nm->notifyPasswordChanged($id_usuario);
}
*/

// ============================================
// EJEMPLO 9: PUNTOS DE RECOMPENSA
// ============================================
/*
Después de completar un pedido:

function otorgarPuntos($id_usuario, $total_compra) {
    // 1 punto por cada $10 gastados
    $puntos = floor($total_compra / 10);
    
    // Actualizar puntos en DB
    global $conn;
    $query = "UPDATE usuario SET puntos_recompensa = puntos_recompensa + ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$puntos, $id_usuario]);
    
    // Notificar al usuario
    $nm = getNotificationManager();
    $nm->notifyRewardPoints($id_usuario, $puntos, 'ganado');
}
*/

// ============================================
// EJEMPLO 10: MANTENIMIENTO PROGRAMADO (ADMIN)
// ============================================
/*
Cuando programas un mantenimiento:

$fecha_mantenimiento = "Sábado 20 de Octubre, 2:00 AM";
$duracion = "2 horas";

$nm = getNotificationManager();
$enviados = $nm->notifyAllUsers(
    "🛠️ Mantenimiento programado",
    "El sistema estará en mantenimiento el {$fecha_mantenimiento} durante aproximadamente {$duracion}.",
    'sistema',
    'media'
);
*/

?>
