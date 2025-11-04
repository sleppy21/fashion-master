<?php
/**
 * GET FAVORITES - Obtener lista actualizada de favoritos
 * Para actualización en tiempo real del modal
 */

session_start();
header('Content-Type: application/json');

require_once '../../config/conexion.php';

// Verificar usuario logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

$id_usuario = $_SESSION['user_id'];

try {
    // Obtener favoritos del usuario CON información de si están en el carrito
    $favoritos = executeQuery("
        SELECT p.id_producto, p.nombre_producto, p.precio_producto, p.url_imagen_producto,
               COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
               p.stock_actual_producto,
               f.fecha_agregado_favorito,
               CASE WHEN c.id_producto IS NOT NULL THEN 1 ELSE 0 END as en_carrito
        FROM favorito f
        INNER JOIN producto p ON f.id_producto = p.id_producto
        LEFT JOIN carrito c ON c.id_producto = p.id_producto AND c.id_usuario = ?
        WHERE f.id_usuario = ? AND p.status_producto = 1
        ORDER BY f.fecha_agregado_favorito DESC
    ", [$id_usuario, $id_usuario]);


    // Generar HTML y recolectar IDs de productos en carrito
    $ids_en_carrito = [];
    ob_start();
    if (!empty($favoritos)):
        foreach($favoritos as $producto):
            $precio_original = $producto['precio_producto'];
            $descuento = $producto['descuento_porcentaje_producto'];
            $precio_final = $descuento > 0 ? $precio_original - ($precio_original * $descuento / 100) : $precio_original;
            $imagen_url = !empty($producto['url_imagen_producto']) ? $producto['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
            $sin_stock = $producto['stock_actual_producto'] <= 0;
            $en_carrito = isset($producto['en_carrito']) && $producto['en_carrito'] == 1;
            if ($en_carrito) $ids_en_carrito[] = $producto['id_producto'];
    ?>
    <div class="favorite-item" data-id="<?php echo $producto['id_producto']; ?>">
        <div class="favorite-image" onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>';">
            <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
            <?php if($sin_stock): ?>
                <span class="stock-badge out">Sin stock</span>
            <?php elseif($descuento > 0): ?>
                <span class="stock-badge sale">-<?php echo round($descuento); ?>%</span>
            <?php endif; ?>
        </div>
        <div class="favorite-info">
            <h6><span class="favorite-product-name" onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>'"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span></h6>
            <div class="favorite-price">
                <span class="price-current">$<?php echo number_format($precio_final, 2); ?></span>
                <?php if($descuento > 0): ?>
                    <span class="price-old">$<?php echo number_format($precio_original, 2); ?></span>
                <?php endif; ?>
            </div>
            <small class="favorite-date">Agregado: <?php echo date('d/m/Y', strtotime($producto['fecha_agregado_favorito'])); ?></small>
        </div>
        <div class="favorite-actions">
            <?php 
            $icono_carrito = $en_carrito ? 'fa-check-circle' : 'fa-cart-plus';
            $title_carrito = $sin_stock ? 'Sin stock' : ($en_carrito ? 'En carrito - Clic para quitar' : 'Agregar al carrito');
            ?>
            <button class="btn-favorite-cart <?php echo $en_carrito ? 'in-cart' : ''; ?>" 
                    data-id="<?php echo $producto['id_producto']; ?>" 
                    data-in-cart="<?php echo $en_carrito ? 'true' : 'false'; ?>"
                    <?php echo $sin_stock ? 'disabled' : ''; ?> 
                    title="<?php echo $title_carrito; ?>">
                <i class="fa <?php echo $icono_carrito; ?>"></i>
            </button>
            <button class="btn-favorite-remove" data-id="<?php echo $producto['id_producto']; ?>" title="Quitar de favoritos">
                <i class="fa fa-heart-broken"></i>
            </button>
        </div>
    </div>
    <?php
        endforeach;
    else:
    ?>
    <div class="favorites-empty">
        <i class="fa fa-heart-o"></i>
        <p>No tienes productos favoritos</p>
        <a href="shop.php" class="btn-shop-now">Explorar productos</a>
    </div>
    <?php
    endif;
    $html = ob_get_clean();
    $count = count($favoritos);
    $count_text = $count == 1 ? '1 producto' : $count . ' productos';

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => $count,
        'count_text' => $count_text,
        'cart_ids' => $ids_en_carrito
    ]);

} catch (Exception $e) {
    error_log("Error en get_favorites.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener favoritos: ' . $e->getMessage()
    ]);
}
?>
