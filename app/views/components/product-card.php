<?php
/**
 * Componente: Tarjeta de Producto
 * Renderiza una tarjeta moderna de producto para el catálogo
 * 
 * @param array $product - Datos del producto
 * @param bool $is_favorite - Si el producto está en favoritos
 * @param bool $user_logged - Si hay usuario logueado
 */

function renderProductCard($product, $is_favorite = false, $user_logged = false, $in_cart = false) {
    // Calcular precio con descuento
    $precio_original = floatval($product['precio_producto']);
    $descuento = floatval($product['descuento_porcentaje_producto'] ?? 0);
    $precio_final = $precio_original - ($precio_original * $descuento / 100);
    $tiene_descuento = $descuento > 0;
    
    // Stock
    $stock = intval($product['stock_actual_producto'] ?? 0);
    $sin_stock = $stock <= 0;
    $stock_bajo = $stock > 0 && $stock <= 5;
    
    // Determinar si es nuevo (últimos 30 días)
    $es_nuevo = (time() - strtotime($product['fecha_creacion'] ?? 'now')) < (30 * 24 * 60 * 60);
    
    // Rating
    $rating = floatval($product['calificacion_promedio'] ?? 0);
    $total_reviews = intval($product['total_resenas'] ?? 0);
    
    // URLs
    $product_url = "product-details.php?id=" . $product['id_producto'];
    $image_url = !empty($product['url_imagen_producto']) 
        ? $product['url_imagen_producto'] 
        : 'public/assets/img/shop/default-product.jpg';
    
    ?>
    <div class="col-lg-4 col-md-6 col-6">
        <div class="product-card-modern" data-product-id="<?= $product['id_producto'] ?>" data-aos="fade-up">
            
            <!-- Imagen del producto -->
            <div class="product-image-wrapper">
                <a href="<?= $product_url ?>">
                    <img src="<?= htmlspecialchars($image_url) ?>" 
                         alt="<?= htmlspecialchars($product['nombre_producto']) ?>"
                         loading="lazy"
                         class="product-image"
                         crossorigin="anonymous">
                </a>
                
                <!-- Badges superiores -->
                <div class="product-badges">
                    <?php if ($sin_stock): ?>
                        <span class="badge-modern badge-out-of-stock">
                            AGOTADO
                        </span>
                    <?php elseif ($tiene_descuento): ?>
                        <span class="badge-modern badge-sale">
                            -<?= intval($descuento) ?>%
                        </span>
                    <?php elseif ($es_nuevo): ?>
                        <span class="badge-modern badge-new">
                            NUEVO
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Hover con botones circulares -->
                <ul class="product__hover">
                    <li>
                        <a href="<?= $product_url ?>" class="view-details-btn" title="Ver detalles">
                            <span class="icon_search"></span>
                        </a>
                    </li>
                    <?php if ($user_logged): ?>
                        <li>
                            <a href="#" 
                               class="add-to-favorites <?= $is_favorite ? 'active' : '' ?>" 
                               data-id="<?= $product['id_producto'] ?>"
                               title="<?= $is_favorite ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
                                <span class="icon_heart<?= $is_favorite ? '' : '_alt' ?>"></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="#" 
                           class="add-to-cart <?= $in_cart ? 'in-cart' : '' ?>" 
                           data-id="<?= $product['id_producto'] ?>"
                           data-in-cart="<?= $in_cart ? 'true' : 'false' ?>"
                           <?= $sin_stock ? 'style="opacity:0.5;cursor:not-allowed;" data-disabled="true"' : '' ?>
                           title="<?= $sin_stock ? 'Sin stock' : ($in_cart ? 'Quitar del carrito' : 'Agregar al carrito') ?>">
                            <span class="<?= $in_cart ? 'icon_check' : 'icon_bag_alt' ?>"></span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Información del producto -->
            <div class="product-info">
                <!-- Categoría -->
                <div class="product-category">
                    <?= strtoupper(htmlspecialchars($product['nombre_categoria'] ?? 'GENERAL')) ?>
                </div>
                
                <!-- Nombre del producto -->
                <h3 class="product-name">
                    <a href="<?= $product_url ?>">
                        <?= htmlspecialchars($product['nombre_producto']) ?>
                    </a>
                </h3>
                
                <!-- Rating -->
                <?php if ($total_reviews > 0): ?>
                    <div class="product-rating">
                        <div class="stars">
                            <?php 
                            $full_stars = floor($rating);
                            $has_half = ($rating - $full_stars) >= 0.5;
                            $empty_stars = 5 - $full_stars - ($has_half ? 1 : 0);
                            
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<i class="fa fa-star star"></i>';
                            }
                            if ($has_half) {
                                echo '<i class="fa fa-star-half-o star"></i>';
                            }
                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '<i class="fa fa-star-o star empty"></i>';
                            }
                            ?>
                        </div>
                        <span class="rating-count">(<?= $total_reviews ?>)</span>
                    </div>
                <?php endif; ?>
                
                <!-- Precio -->
                <div class="product-price">
                    <span class="price-current">S/ <?= number_format($precio_final, 2) ?></span>
                    <?php if ($tiene_descuento): ?>
                        <span class="price-original">S/ <?= number_format($precio_original, 2) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Warning de stock bajo -->
                <?php if ($stock_bajo && !$sin_stock): ?>
                    <div class="stock-warning">
                        <i class="fa fa-exclamation-circle"></i>
                        ¡Solo quedan <?= $stock ?>!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
