<?php
/**
 * AJAX - REFRESH MODALS
 * Actualiza el contenido de los modales sin recargar la página
 * @version 1.0 - Octubre 2025
 */

session_start();
header('Content-Type: application/json');

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Verificar que haya usuario logueado
if (!isset($_SESSION['usuario_logueado'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Cargar dependencias
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/config.php';

try {
    $usuario_logueado = $_SESSION['usuario_logueado'];
    
    // Obtener qué modal actualizar
    $modal_type = $_GET['modal'] ?? 'all';
    
    $response = [];
    
    // FAVORITOS
    if ($modal_type === 'favorites' || $modal_type === 'all') {
        $favoritos_usuario = executeQuery("
            SELECT p.id_producto, p.nombre_producto, p.precio_producto,
                   p.descuento_porcentaje_producto, p.url_imagen_producto,
                   p.stock_actual_producto,
                   f.fecha_agregado_favorito
            FROM favorito f
            INNER JOIN producto p ON f.id_producto = p.id_producto
            WHERE f.id_usuario = ? AND p.status_producto = 1
            ORDER BY f.fecha_agregado_favorito DESC
        ", [$usuario_logueado['id_usuario']]);
        
        $favoritos_usuario = $favoritos_usuario ? $favoritos_usuario : [];
        
        // Obtener IDs de productos en carrito
        $productos_en_carrito = [];
        $carrito_items = executeQuery("
            SELECT id_producto FROM carrito WHERE id_usuario = ?
        ", [$usuario_logueado['id_usuario']]);
        if ($carrito_items) {
            $productos_en_carrito = array_column($carrito_items, 'id_producto');
        }
        
        // Contar favoritos
        $favorites_count = count($favoritos_usuario);
        
        ob_start();
        ?>
        <!-- Favorites Modal Body Content -->
        <div class="favorites-modal-content">
            <button class="favorites-modal-close" aria-label="Cerrar modal" style="position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border: none; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-times"></i>
            </button>
            
            <div class="favorites-modal-header" style="padding: 15px; border-bottom: 2px solid; flex-shrink: 0;">
                <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-heart"></i> Mis Favoritos
                </h3>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p class="favorites-count-text" style="margin: 0; font-size: 12px; font-weight: 500;">
                        <?php echo $favorites_count; ?> <?php echo $favorites_count == 1 ? 'producto' : 'productos'; ?>
                    </p>
                    <p style="margin: 0; font-size: 11px; opacity: 0.8;">
                        <i class="fa fa-clock-o" style="margin-right: 4px;"></i>Actualizado ahora
                    </p>
                </div>
            </div>

            <div class="favorites-modal-body" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; min-height: 0;">
                <?php if(!empty($favoritos_usuario)): ?>
                    <?php foreach($favoritos_usuario as $producto): 
                        $precio_final = $producto['descuento_porcentaje_producto'] > 0 
                            ? $producto['precio_producto'] * (1 - $producto['descuento_porcentaje_producto'] / 100)
                            : $producto['precio_producto'];
                        $en_carrito = in_array($producto['id_producto'], $productos_en_carrito);
                        $imagen_url = !empty($producto['url_imagen_producto']) 
                            ? BASE_URL . $producto['url_imagen_producto']
                            : BASE_URL . 'public/assets/img/no-image.png';
                    ?>
                        <div class="favorite-item" data-product-id="<?php echo $producto['id_producto']; ?>">
                            <img src="<?php echo $imagen_url; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            <div class="favorite-item-info">
                                <div class="favorite-item-name"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                                <div class="favorite-item-price">
                                    <?php if($producto['descuento_porcentaje_producto'] > 0): ?>
                                        <span class="old-price">$<?php echo number_format($producto['precio_producto'], 2); ?></span>
                                        <span class="new-price">$<?php echo number_format($precio_final, 2); ?></span>
                                        <span class="discount">-<?php echo $producto['descuento_porcentaje_producto']; ?>%</span>
                                    <?php else: ?>
                                        <span class="price">$<?php echo number_format($producto['precio_producto'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if($producto['stock_actual_producto'] <= 0): ?>
                                    <span class="out-of-stock">Sin stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="favorite-item-actions">
                                <?php if($producto['stock_actual_producto'] > 0): ?>
                                    <button class="btn-add-to-cart <?php echo $en_carrito ? 'in-cart' : ''; ?>" 
                                            data-product-id="<?php echo $producto['id_producto']; ?>"
                                            title="<?php echo $en_carrito ? 'En el carrito' : 'Agregar al carrito'; ?>">
                                        <i class="fa <?php echo $en_carrito ? 'fa-check' : 'fa-shopping-cart'; ?>"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn-remove-favorite" 
                                        data-product-id="<?php echo $producto['id_producto']; ?>"
                                        title="Eliminar de favoritos">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-favorites">
                        <i class="fa fa-heart-o"></i>
                        <p>No tienes productos favoritos</p>
                        <a href="<?php echo BASE_URL; ?>shop.php" class="btn-browse">Explorar productos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $response['favorites_html'] = ob_get_clean();
        $response['favorites_count'] = $favorites_count;
    }
    
    // NOTIFICACIONES
    if ($modal_type === 'notifications' || $modal_type === 'all') {
        $notificaciones_usuario = executeQuery("
            SELECT id_notificacion, titulo_notificacion, mensaje_notificacion,
                   tipo_notificacion, prioridad_notificacion, leida_notificacion,
                   fecha_creacion_notificacion
            FROM notificacion
            WHERE id_usuario = ? AND estado_notificacion = 'activo'
            ORDER BY fecha_creacion_notificacion DESC
            LIMIT 50
        ", [$usuario_logueado['id_usuario']]);
        
        $notificaciones_usuario = $notificaciones_usuario ? $notificaciones_usuario : [];
        
        ob_start();
        include __DIR__ . '/../../includes/notifications-modal.php';
        $response['notifications_html'] = ob_get_clean();
        $response['notifications_count'] = count($notificaciones_usuario);
    }
    
    // USUARIO
    if ($modal_type === 'user' || $modal_type === 'all') {
        // Obtener contadores actualizados
        $cart_count = 0;
        $cart_result = executeQuery("
            SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?
        ", [$usuario_logueado['id_usuario']]);
        if ($cart_result) {
            $cart_count = (int)$cart_result[0]['total'];
        }
        
        $favorites_count = 0;
        $fav_result = executeQuery("
            SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?
        ", [$usuario_logueado['id_usuario']]);
        if ($fav_result) {
            $favorites_count = (int)$fav_result[0]['total'];
        }
        
        ob_start();
        include __DIR__ . '/../../includes/user-account-modal.php';
        $response['user_html'] = ob_get_clean();
        $response['cart_count'] = $cart_count;
        $response['favorites_count'] = $favorites_count;
    }
    
    $response['success'] = true;
    $response['timestamp'] = date('Y-m-d H:i:s');
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al actualizar modales',
        'message' => $e->getMessage()
    ]);
}
