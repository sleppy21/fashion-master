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
    // DEBUG: Log de datos del producto para depuración
    if ($product['id_producto'] == 2) {
        error_log('DEBUG PRODUCTO 2: ' . print_r($product, true));
    }
    // Calcular precio con descuento
    $precio_original = floatval($product['precio_producto']);
    $descuento = floatval($product['descuento_porcentaje_producto'] ?? 0);
    $precio_final = $precio_original - ($precio_original * $descuento / 100);
    $tiene_descuento = $descuento > 0;
    
    // Stock
    $stock = intval($product['stock_actual_producto'] ?? 0);
    $sin_stock = $stock <= 0;
    $stock_bajo = $stock > 0 && $stock <= 5;
    
    // Determinar si es nuevo (deshabilitado temporalmente - falta campo fecha_creacion en BD)
    $es_nuevo = false;
    
    // Rating
    $rating = floatval($product['calificacion_promedio'] ?? 0);
    $total_reviews = intval($product['total_resenas'] ?? 0);
    
    // URLs
    $product_url = "product-details.php?id=" . $product['id_producto'];
    $image_url = !empty($product['url_imagen_producto']) 
        ? $product['url_imagen_producto'] 
        : 'public/assets/img/shop/default-product.jpg';
    
    // Calcular aspect ratio de la imagen para evitar layout shift
    $aspect_ratio_padding = '125%'; // Default vertical (4:5)
    $debug_info = '';
    
    // Construir ruta absoluta del archivo de imagen
    $project_root = dirname(dirname(dirname(__DIR__))); // c:\xampp\htdocs\fashion-master
    
    // Detectar diferentes formatos de URL
    $image_path = null;
    
    if (strpos($image_url, '/fashion-master/public/') !== false) {
        // URL absoluta: /fashion-master/public/assets/...
        $image_path = $project_root . str_replace('/fashion-master/', '/', $image_url);
        $debug_info = "Ruta absoluta detectada";
    } elseif (strpos($image_url, 'public/') === 0) {
        // URL relativa: public/assets/...
        $image_path = $project_root . '/' . $image_url;
        $debug_info = "Ruta relativa detectada";
    }
    
    // Si encontramos una ruta válida, calcular dimensiones
    if ($image_path && file_exists($image_path)) {
        $image_size = @getimagesize($image_path);
        if ($image_size && $image_size[0] > 0) {
            // Calcular padding-top basado en aspect ratio real
            $aspect_ratio_padding = round(($image_size[1] / $image_size[0] * 100), 2) . '%';
            error_log("✅ PHP: {$debug_info} | {$image_url} = {$image_size[0]}x{$image_size[1]} → {$aspect_ratio_padding}");
        }
    } else {
        error_log("⚠️ PHP: No se pudo calcular - URL: {$image_url} | Path probado: " . ($image_path ?: 'null'));
    }
    
    ?>
    <div class="col-lg-3 col-md-4 col-6">
        <div class="product-card-modern" data-product-id="<?= $product['id_producto'] ?>" data-aos="fade-up">
            
            <!-- Imagen del producto -->
            <div class="product-image-wrapper" style="padding-top: <?= $aspect_ratio_padding ?>;">
                <img src="<?= htmlspecialchars($image_url) ?>" 
                     alt="<?= htmlspecialchars($product['nombre_producto']) ?>"
                     class="product-image"
                     crossorigin="anonymous">
                
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
                <div class="product-category" style="display: flex; align-items: center; gap: 8px;">
                    <span><?= strtoupper(htmlspecialchars($product['nombre_categoria'] ?? 'GENERAL')) ?></span>
                    <?php if (!empty($product['nombre_subcategoria'])): ?>
                        <span style="font-size: 0.95em; color: #888; letter-spacing: 1px; vertical-align: middle;">| <?= strtoupper(htmlspecialchars($product['nombre_subcategoria'])) ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Nombre del producto -->
                <h3 class="product-name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
                    <?php
                    $max_chars = 30;
                    $nombre = htmlspecialchars($product['nombre_producto']);
                    if (mb_strlen($nombre) > $max_chars) {
                        echo mb_substr($nombre, 0, $max_chars - 2) . '...';
                    } else {
                        echo $nombre;
                    }
                    ?>
                </h3>
                
                <!-- Rating -->
                <?php if ($total_reviews > 0): ?>
                <div class="product-rating">
                    <div class="stars">
                        <?php
                        // Forzar que ratings como 4.8, 4.9, 4.6, 4.7, etc. muestren 4.5 estrellas
                        $rounded = ($rating == 5.0) ? 5.0 : floor($rating) + (($rating - floor($rating)) >= 0.25 ? 0.5 : 0);
                        $full_stars = floor($rounded);
                        $has_half = ($rounded - $full_stars) == 0.5;
                        for ($i = 0; $i < 5; $i++) {
                            if ($i < $full_stars) {
                                echo '<span class="icon_star star"></span>';
                            } elseif ($i == $full_stars && $has_half) {
                                echo '<span class="icon_star-half_alt star"></span>';
                            } else {
                                echo '<span class="icon_star_alt star empty"></span>';
                            }
                        }
                        ?>
                    </div>
                    <span class="rating-count"><?= number_format($rating, 1) ?></span>
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
