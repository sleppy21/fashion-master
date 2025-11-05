<?php
/**
 * MODAL DE FAVORITOS
 * Muestra los productos favoritos del usuario
 * Se incluye en las páginas principales
 */

// Solo mostrar si hay un usuario logueado
if($usuario_logueado): 
    // Obtener favoritos del usuario
    try {
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
    } catch(Exception $e) {
        error_log("Error al obtener favoritos: " . $e->getMessage());
        $favoritos_usuario = [];
    }

    // Obtener IDs de productos que están en el carrito
    $productos_en_carrito = [];
    try {
        $carrito_items = executeQuery("
            SELECT id_producto 
            FROM carrito 
            WHERE id_usuario = ?
        ", [$usuario_logueado['id_usuario']]);
        if($carrito_items) {
            $productos_en_carrito = array_column($carrito_items, 'id_producto');
        }
    } catch(Exception $e) {
        error_log("Error al obtener carrito: " . $e->getMessage());
    }
?>
<!-- Favorites Modal Begin -->
<div id="favorites-modal" class="favorites-modal">
    <div class="favorites-modal-content">

        
        <!-- Header -->
        <div class="favorites-modal-header">
            <div class="favorites-modal-title">
                <i class="fa fa-heart"></i>
                <span>Mis Favoritos</span>
            </div>
            <div class="favorites-header-meta">
                <div class="favorites-count">
                    <span class="fav-count-number"><?php echo count($favoritos_usuario); ?></span> <?php echo count($favoritos_usuario) == 1 ? 'producto favorito' : 'productos favoritos'; ?>
                </div>
                <div class="favorites-update">
                    <i class="fa fa-clock-o"></i>
                    <span>Actualizado hoy</span>
                </div>
            </div>
        </div>

        <!-- Body -->
    <div class="favorites-modal-body" id="favorites-list">
            <?php if(!empty($favoritos_usuario)): ?>
                <?php foreach($favoritos_usuario as $fav): 
                    $precio_original = $fav['precio_producto'];
                    $tiene_descuento = $fav['descuento_porcentaje_producto'] > 0;
                    $precio_final = $precio_original;
                    if($tiene_descuento) {
                        $precio_final = $precio_original - ($precio_original * $fav['descuento_porcentaje_producto'] / 100);
                    }
                    $imagen_url = !empty($fav['url_imagen_producto']) ? $fav['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                    $sin_stock = $fav['stock_actual_producto'] <= 0;
                    $en_carrito = in_array($fav['id_producto'], $productos_en_carrito);
                ?>
                <div class="favorite-item-wrapper">
                    <div class="favorite-delete-bg-left"><i class="fa fa-trash"></i></div>
                    <div class="favorite-delete-bg-right"><i class="fa fa-trash"></i></div>
                    <div class="favorite-item" data-id="<?php echo $fav['id_producto']; ?>">
                        <div class="favorite-image" onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>';">
                            <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($fav['nombre_producto']); ?>">
                            <?php if($sin_stock): ?>
                                <span class="stock-badge out">Sin stock</span>
                            <?php elseif($tiene_descuento): ?>
                                <span class="stock-badge sale">-<?php echo round($fav['descuento_porcentaje_producto']); ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="favorite-info">
                            <h6><span class="favorite-product-name" onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>'" style="cursor:pointer;"><?php echo htmlspecialchars($fav['nombre_producto']); ?></span></h6>
                            <div class="favorite-price">
                                <span class="price-current">$<?php echo number_format($precio_final, 2); ?></span>
                                <?php if($tiene_descuento): ?>
                                    <span class="price-old">$<?php echo number_format($precio_original, 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <small class="favorite-date">Agregado: <?php echo date('d/m/Y', strtotime($fav['fecha_agregado_favorito'])); ?></small>
                        </div>
                        <div class="favorite-actions">
                            <?php if($sin_stock): ?>
                                <button class="btn-favorite-cart" disabled title="Sin stock">
                                    <i class="fa fa-shopping-cart"></i>
                                </button>
                            <?php elseif($en_carrito): ?>
                                <button class="btn-favorite-cart in-cart" data-id="<?php echo $fav['id_producto']; ?>" data-in-cart="true" title="En carrito - Clic para quitar">
                                    <i class="fa fa-check-circle"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn-favorite-cart" data-id="<?php echo $fav['id_producto']; ?>" data-in-cart="false" title="Agregar al carrito">
                                    <i class="fa fa-cart-plus"></i>
                                </button>
                            <?php endif; ?>
                            <button class="btn-favorite-remove" data-id="<?php echo $fav['id_producto']; ?>" title="Quitar de favoritos">
                                <i class="fa fa-heart-broken"></i>
                            </button>
                        </div>
                    </div>
                </div> <!-- Fin de favorite-item-wrapper -->
                <?php endforeach; ?>
            <?php else: ?>
                <div class="favorites-empty">
                    <i class="fa fa-heart-o"></i>
                    <p>No tienes productos favoritos</p>
                    <a href="shop.php" class="btn-shop-now">Explorar productos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Favorites Modal End -->

<!-- Script de manipulación de íconos eliminado. Todo el control visual lo gestiona real-time-updates.js -->
<?php endif; ?>
