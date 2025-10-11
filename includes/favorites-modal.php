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
?>
<!-- Favorites Modal Begin -->
<div id="favorites-modal" class="favorites-modal" style="display: none; position: absolute; z-index: 99999;">
    <div class="favorites-modal-content" style="position: fixed; top: 60px; right: 150px; background: #ffffff; width: 380px; height: 480px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); overflow: hidden; display: flex; flex-direction: column;">
        <button class="favorites-modal-close" aria-label="Cerrar modal" style="position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border: none; background: rgba(0,0,0,0.05); color: #666; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header -->
        <div class="favorites-modal-header" style="padding: 15px; background: #ffffff; border-bottom: 2px solid #f0f0f0; flex-shrink: 0;">
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #2c3e50;"><i class="fa fa-heart" style="color: #2c3e50;"></i> Mis Favoritos</h3>
            <p class="favorites-count" style="margin: 0; font-size: 12px; color: #7f8c8d; font-weight: 500;"><?php echo count($favoritos_usuario); ?> productos</p>
        </div>

        <!-- Body -->
        <div class="favorites-modal-body" id="favorites-list" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; background: #f8f9fa; min-height: 0;">
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
                ?>
                <div class="favorite-item" data-id="<?php echo $fav['id_producto']; ?>" style="display: flex; gap: 10px; padding: 10px; background: white; border-radius: 8px; margin-bottom: 8px; border: 1px solid #e9ecef;">
                    <div class="favorite-image" 
                         style="position: relative; width: 70px; height: 70px; flex-shrink: 0; border-radius: 6px; overflow: hidden; cursor: pointer;"
                         onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>';">
                        <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($fav['nombre_producto']); ?>" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;">
                        <?php if($sin_stock): ?>
                            <span class="stock-badge out" style="position: absolute; top: 5px; left: 5px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; background: #dc3545; color: white;">Sin stock</span>
                        <?php elseif($tiene_descuento): ?>
                            <span class="stock-badge sale" style="position: absolute; top: 5px; left: 5px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; background: #28a745; color: white;">-<?php echo round($fav['descuento_porcentaje_producto']); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="favorite-info" style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 600; line-height: 1.3;"><span style="color: #2c3e50; cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>'"><?php echo htmlspecialchars($fav['nombre_producto']); ?></span></h6>
                        <div class="favorite-price" style="display: flex; align-items: center; gap: 6px;">
                            <span class="price-current" style="font-size: 15px; font-weight: 700; color: #2c3e50;">$<?php echo number_format($precio_final, 2); ?></span>
                            <?php if($tiene_descuento): ?>
                                <span class="price-old" style="font-size: 12px; color: #999; text-decoration: line-through;">$<?php echo number_format($precio_original, 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <small class="favorite-date" style="font-size: 10px; color: #999; margin-top: auto;">Agregado: <?php echo date('d/m/Y', strtotime($fav['fecha_agregado_favorito'])); ?></small>
                    </div>
                    <div class="favorite-actions" style="display: flex; flex-direction: column; gap: 5px; justify-content: center;">
                        <button class="btn-favorite-cart" data-id="<?php echo $fav['id_producto']; ?>" <?php echo $sin_stock ? 'disabled' : ''; ?> style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; background: #2c3e50; color: white; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <i class="fa fa-shopping-cart"></i>
                        </button>
                        <button class="btn-favorite-remove" data-id="<?php echo $fav['id_producto']; ?>" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; background: #fee; color: #dc3545; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="favorites-empty" style="text-align: center; padding: 60px 20px;">
                    <i class="fa fa-heart-o" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
                    <p style="font-size: 16px; color: #999; margin-bottom: 20px;">No tienes productos favoritos</p>
                    <a href="shop.php" class="btn-shop-now" style="display: inline-block; padding: 10px 24px; background: #2c3e50; color: white; text-decoration: none; border-radius: 20px; font-weight: 600; font-size: 13px;">Explorar productos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Favorites Modal End -->
<?php endif; ?>
