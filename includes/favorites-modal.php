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
                <div class="favorite-item-wrapper">
                    <div class="favorite-delete-bg-left">
                        <i class="fa fa-heart-broken"></i>
                    </div>
                    <div class="favorite-delete-bg-right">
                        <i class="fa fa-heart-broken"></i>
                    </div>
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
                            <h6><span class="favorite-product-name" style="cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $fav['id_producto']; ?>'"><?php echo htmlspecialchars($fav['nombre_producto']); ?></span></h6>
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

<script>
(function() {
    'use strict';
    
    // ==========================================
    // SWIPE TO DELETE EN FAVORITOS
    // ==========================================
    
    function initFavoritesSwipe() {
        const items = document.querySelectorAll('.favorite-item');
        
        items.forEach(item => {
            const wrapper = item.parentElement;
            const deleteBgLeft = wrapper.querySelector('.favorite-delete-bg-left');
            const deleteBgRight = wrapper.querySelector('.favorite-delete-bg-right');
            
            let startX = 0;
            let startY = 0;
            let currentX = 0;
            let currentY = 0;
            let isDragging = false;
            let isHorizontalSwipe = null;
            let startTime = 0;
            
            const SWIPE_THRESHOLD = 100;
            const VELOCITY_THRESHOLD = 0.3;
            const DIRECTION_THRESHOLD = 10;
            
            // Mouse Events
            item.addEventListener('mousedown', handleStart);
            document.addEventListener('mousemove', handleMove);
            document.addEventListener('mouseup', handleEnd);
            
            // Touch Events
            item.addEventListener('touchstart', handleStart, { passive: true });
            document.addEventListener('touchmove', handleMove, { passive: false });
            document.addEventListener('touchend', handleEnd);
            
            function handleStart(e) {
                startTime = Date.now();
                item.style.cursor = 'grabbing';
                
                if (e.type === 'mousedown') {
                    startX = e.clientX;
                    startY = e.clientY;
                    isDragging = true;
                    isHorizontalSwipe = null;
                } else if (e.type === 'touchstart') {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                    isDragging = true;
                    isHorizontalSwipe = null;
                }
            }
            
            function handleMove(e) {
                if (!isDragging) return;
                
                let clientX, clientY;
                if (e.type === 'mousemove') {
                    clientX = e.clientX;
                    clientY = e.clientY;
                } else if (e.type === 'touchmove') {
                    clientX = e.touches[0].clientX;
                    clientY = e.touches[0].clientY;
                }
                
                const deltaX = clientX - startX;
                const deltaY = clientY - startY;
                
                // Determinar dirección del gesto
                if (isHorizontalSwipe === null && (Math.abs(deltaX) > DIRECTION_THRESHOLD || Math.abs(deltaY) > DIRECTION_THRESHOLD)) {
                    isHorizontalSwipe = Math.abs(deltaX) > Math.abs(deltaY);
                }
                
                // Solo manejar swipe horizontal
                if (isHorizontalSwipe === true) {
                    e.preventDefault();
                    
                    item.style.transition = 'none';
                    deleteBgLeft.style.transition = 'none';
                    deleteBgRight.style.transition = 'none';
                    
                    currentX = deltaX;
                    currentY = deltaY;
                    
                    const maxSwipe = 150;
                    if (currentX > maxSwipe) {
                        currentX = maxSwipe;
                    } else if (currentX < -maxSwipe) {
                        currentX = -maxSwipe;
                    }
                    
                    item.style.transform = `translateX(${currentX}px)`;
                    
                    const distance = Math.abs(currentX);
                    const opacity = Math.min(distance / SWIPE_THRESHOLD, 1);
                    
                    if (currentX > 0) {
                        deleteBgLeft.style.opacity = opacity;
                        deleteBgRight.style.opacity = 0;
                    } else if (currentX < 0) {
                        deleteBgRight.style.opacity = opacity;
                        deleteBgLeft.style.opacity = 0;
                    } else {
                        deleteBgLeft.style.opacity = 0;
                        deleteBgRight.style.opacity = 0;
                    }
                }
            }
            
            function handleEnd(e) {
                if (!isDragging) return;
                
                isDragging = false;
                item.style.cursor = 'grab';
                item.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                deleteBgLeft.style.transition = 'opacity 0.2s';
                deleteBgRight.style.transition = 'opacity 0.2s';
                
                if (isHorizontalSwipe === true) {
                    const endTime = Date.now();
                    const timeDiff = endTime - startTime;
                    const velocity = Math.abs(currentX) / timeDiff;
                    
                    const shouldDelete = Math.abs(currentX) >= SWIPE_THRESHOLD || velocity >= VELOCITY_THRESHOLD;
                    
                    if (shouldDelete) {
                        const id = item.getAttribute('data-id');
                        const direction = currentX > 0 ? 1 : -1;
                        removeFavoriteSwipe(id, wrapper, item, direction);
                    } else {
                        item.style.transform = 'translateX(0)';
                        deleteBgLeft.style.opacity = '0';
                        deleteBgRight.style.opacity = '0';
                    }
                } else {
                    item.style.transform = 'translateX(0)';
                    deleteBgLeft.style.opacity = '0';
                    deleteBgRight.style.opacity = '0';
                }
                
                currentX = 0;
                currentY = 0;
                isHorizontalSwipe = null;
            }
        });
    }
    
    // Eliminar favorito con animación
    function removeFavoriteSwipe(productId, wrapper, item, direction) {
        const translateValue = direction > 0 ? '100%' : '-100%';
        item.style.transform = `translateX(${translateValue})`;
        item.style.opacity = '0';
        
        // Llamar a la función global de eliminación
        if (typeof window.removeFavorite === 'function') {
            window.removeFavorite(productId, false); // false para no recargar
            
            setTimeout(() => {
                wrapper.remove();
                
                // Verificar si quedan favoritos
                const remainingItems = document.querySelectorAll('.favorite-item');
                if (remainingItems.length === 0) {
                    const container = document.getElementById('favorites-list');
                    if (container) {
                        container.innerHTML = `
                            <div class="favorites-empty" style="text-align: center; padding: 60px 20px;">
                                <i class="fa fa-heart-o" style="font-size: 80px; margin-bottom: 20px;"></i>
                                <p style="font-size: 16px; margin-bottom: 20px;">No tienes productos favoritos</p>
                                <a href="shop.php" class="btn-shop-now" style="display: inline-block; padding: 10px 24px; text-decoration: none; border-radius: 20px; font-weight: 600; font-size: 13px;">Explorar productos</a>
                            </div>
                        `;
                    }
                }
                
                // Actualizar contador
                const countEl = document.querySelector('.favorites-count');
                if (countEl) {
                    const remainingCount = document.querySelectorAll('.favorite-item').length;
                    const countNumber = countEl.querySelector('.fav-count-number');
                    const countText = remainingCount === 1 ? 'producto favorito' : 'productos favoritos';
                    
                    if (countNumber) {
                        countNumber.textContent = remainingCount;
                    }
                }
            }, 300);
        }
    }
    
    // Inicializar cuando el modal se abre
    const favModal = document.getElementById('favorites-modal');
    if (favModal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (favModal.classList.contains('active')) {
                        setTimeout(() => initFavoritesSwipe(), 100);
                    }
                }
            });
        });
        observer.observe(favModal, { attributes: true });
        
        // Inicializar si ya está abierto
        if (favModal.classList.contains('active')) {
            initFavoritesSwipe();
        }
    }
})();
</script>
<?php endif; ?>
