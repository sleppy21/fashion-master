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
        <button class="favorites-modal-close" aria-label="Cerrar modal" style="position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border: none; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header -->
        <div class="favorites-modal-header" style="padding: 15px; border-bottom: 2px solid; flex-shrink: 0;">
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;"><i class="fa fa-heart"></i> Mis Favoritos</h3>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <p class="favorites-count" style="margin: 0; font-size: 12px; font-weight: 500;">
                    <span class="fav-count-number"><?php echo count($favoritos_usuario); ?></span> <?php echo count($favoritos_usuario) == 1 ? 'producto favorito' : 'productos favoritos'; ?>
                </p>
                <p class="favorites-update" style="margin: 0; font-size: 11px; opacity: 0.8;">
                    <i class="fa fa-clock-o" style="margin-right: 4px;"></i>Actualizado hoy
                </p>
            </div>
        </div>

        <!-- Body -->
        <div class="favorites-modal-body" id="favorites-list" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; min-height: 0;">
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
                <div class="favorite-item" data-id="<?php echo $fav['id_producto']; ?>" style="display: flex; gap: 10px; padding: 10px; border-radius: 8px; margin-bottom: 8px; border: 1px solid;">
                    <div class="favorite-image" 
                         style="position: relative; width: 70px; height: 70px; flex-shrink: 0; border-radius: 6px; overflow: hidden; cursor: pointer;"
                         onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>';">
                        <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($fav['nombre_producto']); ?>" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;">
                        <?php if($sin_stock): ?>
                            <span class="stock-badge out" style="position: absolute; top: 5px; left: 5px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">Sin stock</span>
                        <?php elseif($tiene_descuento): ?>
                            <span class="stock-badge sale" style="position: absolute; top: 5px; left: 5px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;">-<?php echo round($fav['descuento_porcentaje_producto']); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="favorite-info" style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 600; line-height: 1.3;"><span class="favorite-product-name" style="cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>'"><?php echo htmlspecialchars($fav['nombre_producto']); ?></span></h6>
                        <div class="favorite-price" style="display: flex; align-items: center; gap: 6px;">
                            <span class="price-current" style="font-size: 15px; font-weight: 700;">$<?php echo number_format($precio_final, 2); ?></span>
                            <?php if($tiene_descuento): ?>
                                <span class="price-old" style="font-size: 12px; text-decoration: line-through;">$<?php echo number_format($precio_original, 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <small class="favorite-date" style="font-size: 10px; margin-top: auto;">Agregado: <?php echo date('d/m/Y', strtotime($fav['fecha_agregado_favorito'])); ?></small>
                    </div>
                    <div class="favorite-actions" style="display: flex; flex-direction: column; gap: 5px; justify-content: center;">
                        <?php if($sin_stock): ?>
                            <button class="btn-favorite-cart" disabled style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: not-allowed; display: flex; align-items: center; justify-content: center; font-size: 13px; opacity: 0.5;">
                                <i class="fa fa-shopping-cart"></i>
                            </button>
                        <?php elseif($en_carrito): ?>
                            <button class="btn-favorite-cart in-cart" data-id="<?php echo $fav['id_producto']; ?>" data-in-cart="true" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.3s ease;" title="En carrito - Clic para quitar">
                                <i class="fa fa-check-circle"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn-favorite-cart" data-id="<?php echo $fav['id_producto']; ?>" data-in-cart="false" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.3s ease;" title="Agregar al carrito">
                                <i class="fa fa-cart-plus"></i>
                            </button>
                        <?php endif; ?>
                        <button class="btn-favorite-remove" data-id="<?php echo $fav['id_producto']; ?>" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.3s ease;" title="Quitar de favoritos">
                            <i class="fa fa-heart-broken"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="favorites-empty" style="text-align: center; padding: 60px 20px;">
                    <i class="fa fa-heart-o" style="font-size: 80px; margin-bottom: 20px;"></i>
                    <p style="font-size: 16px; margin-bottom: 20px;">No tienes productos favoritos</p>
                    <a href="shop.php" class="btn-shop-now" style="display: inline-block; padding: 10px 24px; text-decoration: none; border-radius: 20px; font-weight: 600; font-size: 13px;">Explorar productos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Favorites Modal End -->
<?php endif; ?>
