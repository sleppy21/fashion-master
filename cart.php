<?php
/**
 * PÁGINA DE CARRITO DE COMPRAS
 * Muestra todos los productos en el carrito del usuario
 */

session_start();
require_once 'config/conexion.php';
require_once 'config/config.php'; // <-- Para BASE_URL global
require_once 'app/views/components/product-card.php';

$page_title = "Carrito de Compras";

// Verificar si el usuario está logueado
$usuario_logueado = null;
if (isset($_SESSION['user_id'])) {
    try {
        $usuario_resultado = executeQuery("SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1", [$_SESSION['user_id']]);
        $usuario_logueado = $usuario_resultado && !empty($usuario_resultado) ? $usuario_resultado[0] : null;
        
        if (!$usuario_logueado) {
            session_destroy();
            header('Location: login.php');
            exit;
        }
    } catch(Exception $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        session_destroy();
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php?redirect=cart.php');
    exit;
}

// Obtener items del carrito
$cart_items = [];
$subtotal_sin_descuento = 0;
$descuento_total = 0;
$total_carrito = 0;

try {
    $cart_resultado = executeQuery("
        SELECT c.id_carrito, c.cantidad_carrito,
               p.id_producto, p.nombre_producto, p.precio_producto,
               p.descuento_porcentaje_producto, p.url_imagen_producto,
               p.stock_actual_producto,
               m.nombre_marca
        FROM carrito c
        INNER JOIN producto p ON c.id_producto = p.id_producto
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        WHERE c.id_usuario = ? AND p.status_producto = 1
        ORDER BY c.fecha_agregado_carrito DESC
    ", [$usuario_logueado['id_usuario']]);
    
    $cart_items = $cart_resultado ? $cart_resultado : [];
    
    // Calcular subtotal sin descuento, descuento total y total con descuento
    foreach($cart_items as $item) {
        $precio_original = $item['precio_producto'];
        $cantidad = $item['cantidad_carrito'];
        
        // Subtotal sin descuento (precio original)
        $subtotal_sin_descuento += $precio_original * $cantidad;
        
        // Si tiene descuento, calcular el ahorro
        if($item['descuento_porcentaje_producto'] > 0) {
            $descuento_item = ($precio_original * $item['descuento_porcentaje_producto'] / 100) * $cantidad;
            $descuento_total += $descuento_item;
        }
    }
    
    // Total final = Subtotal - Descuentos
    $total_carrito = $subtotal_sin_descuento - $descuento_total;
    
} catch(Exception $e) {
    error_log("Error al obtener carrito: " . $e->getMessage());
}

// Verificar si el usuario tiene dirección predeterminada
$tiene_direccion_predeterminada = false;
try {
    $direccion_check = executeQuery("
        SELECT COUNT(*) as total 
        FROM direccion 
        WHERE id_usuario = ? AND es_principal = 1 AND status_direccion = 1
    ", [$usuario_logueado['id_usuario']]);
    
    $tiene_direccion_predeterminada = ($direccion_check && $direccion_check[0]['total'] > 0);
} catch(Exception $e) {
    error_log("Error al verificar dirección: " . $e->getMessage());
}

// Obtener contadores para el header
$cart_count = count($cart_items);
$favorites_count = 0;
$notifications_count = 0;
try {
    $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $favorites_count = $favorites && !empty($favorites) ? (int)$favorites[0]['total'] : 0;
    
    $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
    $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
} catch(Exception $e) {
    error_log("Error al obtener favoritos/notificaciones: " . $e->getMessage());
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY id_categoria ASC LIMIT 5");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
}

// Obtener marcas para el menú
$marcas = [];
try {
    $marcas_resultado = executeQuery("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
    $marcas = $marcas_resultado ? $marcas_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener marcas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Carrito de Compras - SleppyStore">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SleppyStore</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- 6. SPECIFIC: Estilos específicos de página -->
    <link rel="stylesheet" href="public/assets/css/cart-improvements.css?v=<?= time() ?>" type="text/css">
    
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- Estilos específicos del carrito -->
    <link rel="stylesheet" href="public/assets/css/cart/cart.css?v=<?= time() ?>" type="text/css">
    
    <!-- Estilos de tarjetas de productos (productos relacionados) -->
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=3.0" type="text/css">

</head>

<body class="cart-page">

    <!-- Header Section Begin -->

    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>
    <?php include 'includes/header-section.php'; ?>
    <!-- Header Section End -->

    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Shop Cart Section Begin -->
    <section class="shop-cart spad">
        <div class="container-fluid px-lg-5" style="max-width: 1600px;">
            <?php if(!empty($cart_items)): ?>
            <div class="row">
                <!-- Lista de Productos (Izquierda) -->
                <div class="col-lg-9 col-md-8 col-12 ps-lg-4">
                    <div class="shop__cart__table">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="select-all-items">
                                    </th>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart_items as $item): 
                                    $precio_original = $item['precio_producto'];
                                    $tiene_descuento = $item['descuento_porcentaje_producto'] > 0;
                                    $precio_final = $precio_original;
                                    if($tiene_descuento) {
                                        $precio_final = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                                    }
                                    $subtotal = $precio_final * $item['cantidad_carrito'];
                                    $imagen_url = !empty($item['url_imagen_producto']) ? $item['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                                ?>
                                <tr data-cart-id="<?php echo $item['id_carrito']; ?>" 
                                    data-price="<?php echo $precio_final; ?>" 
                                    data-quantity="<?php echo $item['cantidad_carrito']; ?>"
                                    data-subtotal="<?php echo $subtotal; ?>">
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input type="checkbox" 
                                               class="item-checkbox" 
                                               data-cart-id="<?php echo $item['id_carrito']; ?>"
                                               checked>
                                    </td>
                                    <td class="cart__product__item">
                                        <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>" style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px;">
                                        <div class="cart__product__item__title">
                                            <h6><a href="product-details.php?id=<?php echo $item['id_producto']; ?>"><?php echo htmlspecialchars($item['nombre_producto']); ?></a></h6>
                                            <div class="rating">
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star-o"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart__price">
                                        $<?php echo number_format($precio_final, 2); ?>
                                        <?php if($tiene_descuento): ?>
                                            <br><small style="text-decoration: line-through; color: #999;">$<?php echo number_format($precio_original, 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="cart__quantity">
                                        <div class="qty-select-wrapper">
                                            <button class="qty-display-btn" 
                                                    type="button"
                                                    data-id="<?php echo $item['id_carrito']; ?>" 
                                                    data-max="<?php echo $item['stock_actual_producto']; ?>"
                                                    data-current="<?php echo $item['cantidad_carrito']; ?>">
                                                <span class="qty-value"><?php echo $item['cantidad_carrito']; ?></span>
                                                <span class="arrow">▼</span>
                                            </button>
                                            <div class="qty-dropdown">
                                                <?php 
                                                $max_qty = min(30, $item['stock_actual_producto']);
                                                for($i = 1; $i <= $max_qty; $i++): 
                                                ?>
                                                    <div class="qty-option <?php echo ($i == $item['cantidad_carrito']) ? 'selected' : ''; ?>" 
                                                         data-value="<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart__total">$<?php echo number_format($subtotal, 2); ?></td>
                                    <td class="cart__close" style="text-align: center;">
                                        <button class="remove-cart-item" data-id="<?php echo $item['id_carrito']; ?>" style="background: #fee; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-trash" style="color: #dc3545; font-size: 14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vista Móvil: Tarjetas (oculta en desktop, visible en móvil) -->
                    <div class="cart-mobile-view" style="display: none;">
                        <?php foreach($cart_items as $item): 
                            $precio_original = $item['precio_producto'];
                            $tiene_descuento = $item['descuento_porcentaje_producto'] > 0;
                            $precio_final = $precio_original;
                            if($tiene_descuento) {
                                $precio_final = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                            }
                            $subtotal = $precio_final * $item['cantidad_carrito'];
                            $imagen_url = !empty($item['url_imagen_producto']) ? $item['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                        ?>
                        <div class="cart-mobile-item" 
                             data-cart-id="<?php echo $item['id_carrito']; ?>"
                             data-price="<?php echo $precio_final; ?>" 
                             data-quantity="<?php echo $item['cantidad_carrito']; ?>"
                             data-subtotal="<?php echo $subtotal; ?>">
                            <div class="cart-mobile-item__header">
                                <!-- Checkbox a la izquierda -->
                                <div class="cart-mobile-item__checkbox">
                                    <input type="checkbox" 
                                           class="item-checkbox-mobile" 
                                           data-cart-id="<?php echo $item['id_carrito']; ?>"
                                           checked>
                                </div>
                                <!-- Imagen del producto -->
                                <div class="cart-mobile-item__image">
                                    <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>">
                                </div>
                                <!-- Info del producto -->
                                <div class="cart-mobile-item__info">
                                    <div class="cart-mobile-item__title">
                                        <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                    </div>
                                    <div class="cart-mobile-item__brand">
                                        <?php echo htmlspecialchars($item['nombre_marca'] ?? 'Sin marca'); ?>
                                    </div>
                                    <div class="cart-mobile-item__price-row">
                                        <div class="cart-mobile-item__price">
                                            $<?php echo number_format($precio_final, 2); ?>
                                            <?php if($tiene_descuento): ?>
                                                <span class="cart-mobile-item__price-original">$<?php echo number_format($precio_original, 2); ?></span>
                                                <span class="cart-mobile-item__discount-badge">-<?php echo $item['descuento_porcentaje_producto']; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cart-mobile-item__quantity">
                                            <span class="cart-mobile-item__quantity-label">Cantidad:</span>
                                            <div class="qty-select-wrapper">
                                                <button class="qty-display-btn" 
                                                        type="button"
                                                        data-id="<?php echo $item['id_carrito']; ?>" 
                                                        data-max="<?php echo $item['stock_actual_producto']; ?>"
                                                        data-current="<?php echo $item['cantidad_carrito']; ?>">
                                                    <span class="qty-value"><?php echo $item['cantidad_carrito']; ?></span>
                                                    <span class="arrow">▼</span>
                                                </button>
                                                <div class="qty-dropdown">
                                                    <?php 
                                                    $max_qty = min(30, $item['stock_actual_producto']);
                                                    for($i = 1; $i <= $max_qty; $i++): 
                                                    ?>
                                                        <div class="qty-option <?php echo ($i == $item['cantidad_carrito']) ? 'selected' : ''; ?>" 
                                                             data-value="<?php echo $i; ?>">
                                                            <?php echo $i; ?>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cart-mobile-item__remove">
                                    <button class="cart-mobile-item__remove-btn remove-cart-item" data-id="<?php echo $item['id_carrito']; ?>">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Productos Relacionados Dentro de la Columna Izquierda -->
                    <?php
                    // Obtener IDs de productos en el carrito y sus categorías
                    $product_ids_in_cart = array_column($cart_items, 'id_producto');
                    $category_ids = [];
                    
                    foreach($cart_items as $item) {
                        $cat_result = executeQuery("SELECT id_categoria FROM producto WHERE id_producto = ?", [$item['id_producto']]);
                        if($cat_result && !empty($cat_result)) {
                            $category_ids[] = $cat_result[0]['id_categoria'];
                        }
                    }
                    
                    $category_ids = array_unique($category_ids);
                    
                    // Buscar productos relacionados con datos completos para el componente
                    $related_products = [];
                    if(!empty($category_ids)) {
                        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
                        $exclude_placeholders = implode(',', array_fill(0, count($product_ids_in_cart), '?'));
                        
                        $params = array_merge($category_ids, $product_ids_in_cart);
                        
                        $related_query = "
                            SELECT DISTINCT
                                p.id_producto,
                                p.nombre_producto,
                                p.precio_producto,
                                p.descuento_porcentaje_producto,
                                p.stock_actual_producto,
                                p.url_imagen_producto,
                                m.nombre_marca,
                                c.nombre_categoria,
                                COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
                                COALESCE(COUNT(DISTINCT r.id_resena), 0) as total_resenas
                            FROM producto p
                            LEFT JOIN marca m ON p.id_marca = m.id_marca
                            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                            LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
                            WHERE p.id_categoria IN ($placeholders)
                            AND p.id_producto NOT IN ($exclude_placeholders)
                            AND p.status_producto = 1
                            AND p.stock_actual_producto > 0
                            GROUP BY p.id_producto
                            ORDER BY RAND()
                            LIMIT 9
                        ";
                        
                        $related_products = executeQuery($related_query, $params);
                    }
                    
                    // Si no hay productos relacionados, mostrar productos aleatorios de la tienda
                    if(empty($related_products) && !empty($product_ids_in_cart)) {
                        $exclude_placeholders = implode(',', array_fill(0, count($product_ids_in_cart), '?'));
                        
                        $random_query = "
                            SELECT DISTINCT
                                p.id_producto,
                                p.nombre_producto,
                                p.precio_producto,
                                p.descuento_porcentaje_producto,
                                p.stock_actual_producto,
                                p.url_imagen_producto,
                                m.nombre_marca,
                                c.nombre_categoria,
                                COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
                                COALESCE(COUNT(DISTINCT r.id_resena), 0) as total_resenas
                            FROM producto p
                            LEFT JOIN marca m ON p.id_marca = m.id_marca
                            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                            LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
                            WHERE p.id_producto NOT IN ($exclude_placeholders)
                            AND p.status_producto = 1
                            AND p.stock_actual_producto > 0
                            GROUP BY p.id_producto
                            ORDER BY RAND()
                            LIMIT 12
                        ";
                        
                        $related_products = executeQuery($random_query, $product_ids_in_cart);
                    }
                    
                    if(!empty($related_products)):
                    ?>
                    
                    <div style="margin-top: 30px;">
                        <div class="section-title">
                            <h4>Productos que tal vez quieras agregar</h4>
                        </div>
                        
                        <div class="products-grid-modern">
                            <div class="row">
                                <?php 
                                // Obtener favoritos del usuario si está logueado
                                $favoritos = [];
                                $productos_en_carrito = [];
                                
                                if($usuario_logueado) {
                                    $fav_result = executeQuery("SELECT id_producto FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
                                    if($fav_result) {
                                        $favoritos = array_column($fav_result, 'id_producto');
                                    }
                                    
                                    $cart_result = executeQuery("SELECT id_producto FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
                                    if($cart_result) {
                                        $productos_en_carrito = array_column($cart_result, 'id_producto');
                                    }
                                }
                                
                                foreach($related_products as $product): 
                                    $is_favorite = in_array($product['id_producto'], $favoritos);
                                    $in_cart = in_array($product['id_producto'], $productos_en_carrito);
                                    renderProductCard($product, $is_favorite, true, $in_cart);
                                endforeach; 
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php endif; ?>
                </div>

                <!-- Resumen del Carrito (Derecha) -->
                <div class="col-lg-3 col-md-4 col-12">
                    <div class="cart-summary-sidebar">
                        
                        <!-- Código de descuento -->
                        <div class="discount-section">
                            <h6><i class="fa fa-tag"></i> Código de descuento</h6>
                            <form id="coupon-form" class="discount-form">
                                <div class="input-group">
                                    <input type="text" id="coupon-code" placeholder="Ingresa tu código" class="discount-input">
                                    <button type="submit" class="btn-apply-discount">Aplicar</button>
                                </div>
                            </form>
                        </div>

                    <!-- Mensaje de cupón fuera del sidebar -->
                    <div id="coupon-message" style="display: none;"></div>

                        <!-- Resumen del carrito -->
                        <div class="cart-totals-section">
                            <h5><i class="fa fa-shopping-cart"></i> Resumen del carrito</h5>
                            <div class="totals-list">
                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <span class="amount" id="cart-subtotal">$<?php echo number_format($subtotal_sin_descuento, 2); ?></span>
                                </div>
                                <?php if($descuento_total > 0): ?>
                                <div class="total-row discount-row active" id="discount-row">
                                    <span style="color: rgba(0, 0, 0, 0.7);"><i class="fa fa-ticket"></i> Descuento</span>
                                    <span class="amount" style="color: rgba(0, 0, 0, 0.7);" id="cart-discount">-$<?php echo number_format($descuento_total, 2); ?></span>
                                </div>
                                <?php else: ?>
                                <div class="total-row discount-row" id="discount-row">
                                    <span style="color: rgba(0, 0, 0, 0.7);"><i class="fa fa-ticket"></i> Descuento</span>
                                    <span class="amount" style="color: rgba(0, 0, 0, 0.7);" id="cart-discount">-$0.00</span>
                                </div>
                                <?php endif; ?>
                                <div class="total-row total-final">
                                    <span>Total</span>
                                    <span class="amount" id="cart-total">$<?php echo number_format($total_carrito, 2); ?></span>
                                </div>
                            </div>
                            
                            <?php if($tiene_direccion_predeterminada): ?>
                            <a href="checkout.php" class="btn-proceed-checkout">
                                <i class="fa fa-lock"></i> Proceder al Pago
                            </a>
                            <?php else: ?>
                            <a href="profile.php?seccion=direcciones" class="btn-proceed-checkout" id="btn-add-address">
                                <i class="fa fa-arrow-right"></i> Continuar
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Información de Seguridad y Confianza -->
                        <div class="trust-section">
                            <div class="trust-badge">
                                <i class="fa fa-shield"></i>
                                <div class="trust-text">
                                    <strong>Pago 100% Seguro</strong>
                                    <p>Protección SSL y encriptación de datos</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-truck"></i>
                                <div class="trust-text">
                                    <strong>Envío Garantizado</strong>
                                    <p>Seguimiento en tiempo real de tu pedido</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-undo"></i>
                                <div class="trust-text">
                                    <strong>Devolución Gratis</strong>
                                    <p>30 días para cambios y devoluciones</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-check-circle"></i>
                                <div class="trust-text">
                                    <strong>Compra Verificada</strong>
                                    <p>Productos 100% originales y garantizados</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-headphones"></i>
                                <div class="trust-text">
                                    <strong>Soporte 24/7</strong>
                                    <p>Atención al cliente siempre disponible</p>
                                </div>
                            </div>

            <div class="secure-payments">
                                <p style="font-size: 11px; color: rgba(0, 0, 0, 0.6); text-align: center; margin: 15px 0 8px;">
                                    <i class="fa fa-lock"></i> Métodos de pago seguros
                                </p>
                                <div class="payment-icons" style="display: flex; justify-content: center; gap: 12px; align-items: center; flex-wrap: wrap;">
                                    <i class="fa fa-cc-visa" style="font-size: 32px; color: #1a1f71;" title="Visa"></i>
                                    <i class="fa fa-cc-mastercard" style="font-size: 32px; color: #eb001b;" title="Mastercard"></i>
                                    <i class="fa fa-cc-amex" style="font-size: 32px; color: #006fcf;" title="American Express"></i>
                                    <i class="fa fa-credit-card" style="font-size: 28px; color: #333;" title="Otras tarjetas"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cart-empty" style="text-align:center; padding: 48px 0 56px 0;">
                        <i class="fa fa-shopping-cart" style="font-size: 54px; color: #bbb; margin-bottom: 18px;"></i>
                        <h3 style="font-weight: 700; margin-bottom: 10px;">¡Tu carrito está vacío!</h3>
                        <p style="color: #666; font-size: 17px; margin-bottom: 24px;">No tienes productos en tu carrito.<br>Descubre las mejores ofertas y encuentra algo para ti.</p>
                        <a href="shop.php" class="btn-continue-shopping" style="display:inline-block; background: #3a6cf6; color: #fff; font-weight: 600; padding: 12px 32px; border-radius: 30px; font-size: 16px; box-shadow: 0 2px 8px rgba(58,108,246,0.08); transition: background 0.2s;">Ir a la tienda</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- Shop Cart Section End -->

    <!-- Footer Sticky Móvil (Solo visible en móvil) -->
    <?php if(!empty($cart_items)): ?>
    <div class="mobile-cart-footer">
        <div class="mobile-cart-footer__content">
            <div class="mobile-cart-footer__total">
                <div class="mobile-cart-footer__label">Total a pagar</div>
                <div class="mobile-cart-footer__amount" id="mobile-footer-total">
                    $<?php echo number_format($total_carrito, 2); ?>
                </div>
                <?php if($descuento_total > 0): ?>
                <div class="mobile-cart-footer__savings">
                    Ahorras $<?php echo number_format($descuento_total, 2); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="mobile-cart-footer__action">
                <?php if($tiene_direccion_predeterminada): ?>
                <a href="checkout.php" class="mobile-checkout-btn">
                    <span>Proceder al Pago</span>
                    <i class="fa fa-arrow-right"></i>
                </a>
                <?php else: ?>
                <a href="profile.php?seccion=direcciones" class="mobile-checkout-btn" id="mobile-btn-add-address">
                    <span>Continuar</span>
                    <i class="fa fa-arrow-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Js Plugins -->
    <script>
        // BASE URL para peticiones AJAX - Compatible con ngrok y cualquier dominio
        (function() {
            var baseUrlFromPHP = '<?php echo defined("BASE_URL") ? BASE_URL : ""; ?>';
            
            // Si no hay BASE_URL definida en PHP, calcularla desde JavaScript
            if (!baseUrlFromPHP || baseUrlFromPHP === '') {
                var path = window.location.pathname;
                var pathParts = path.split('/').filter(function(p) { return p !== ''; });
                
                // Buscar 'fashion-master' en el path
                var basePath = '';
                if (pathParts.includes('fashion-master')) {
                    var index = pathParts.indexOf('fashion-master');
                    basePath = '/' + pathParts.slice(0, index + 1).join('/');
                }
                
                baseUrlFromPHP = window.location.origin + basePath;
            }
            
            // CRÍTICO: Si la página está en HTTPS, forzar BASE_URL a HTTPS
            if (window.location.protocol === 'https:' && baseUrlFromPHP.startsWith('http://')) {
                baseUrlFromPHP = baseUrlFromPHP.replace('http://', 'https://');
            }
            
            window.BASE_URL = baseUrlFromPHP;
        })();
    </script>

    <!-- Masonry Layout para productos relacionados -->
    <script src="public/assets/js/shop/masonry-layout.js?v=1.1"></script>
                
    <script>
    // Variable global para almacenar descuento de cupón aplicado
    let appliedDiscount = 0;
    let discountPercentage = 0;
    let couponCode = '';

    // Función para actualizar los totales en el sidebar
    function updateCartTotals() {
        let subtotalSinDescuento = 0;
        let descuentoProductos = 0;
        
        // Determinar qué vista usar (desktop o móvil)
        const isMobile = window.innerWidth <= 576;
        const itemsSelector = isMobile ? '.cart-mobile-item[data-cart-id]' : 'tr[data-cart-id]';

        let productosSeleccionados = 0;
        
        // Calcular subtotal y descuentos solo de productos SELECCIONADOS
        $(itemsSelector).each(function() {
            const item = $(this);
            
            // Verificar si el checkbox está marcado
            const checkbox = isMobile ? 
                item.find('.item-checkbox-mobile') : 
                item.find('.item-checkbox');
            
            // ✅ SOLO CONTAR SI ESTÁ SELECCIONADO
            if (!checkbox.is(':checked')) {
                return; // Skip este item
            }
            
            productosSeleccionados++;
            
            if(isMobile) {
                // Vista móvil
                const priceText = item.find('.cart-mobile-item__price').text().trim().split('$')[1];
                const precioFinal = parseFloat(priceText.replace(/,/g, ''));
                
                const priceOriginalText = item.find('.cart-mobile-item__price-original').text().trim().replace('$', '').replace(/,/g, '');
                const precioOriginal = priceOriginalText ? parseFloat(priceOriginalText) : precioFinal;
                
                // 🔧 CORREGIDO: Obtener cantidad del dropdown botón o del atributo data-quantity
                const cantidadFromBtn = item.find('.qty-value').text().trim();
                const cantidad = cantidadFromBtn ? parseInt(cantidadFromBtn) : parseInt(item.data('quantity'));
                
                
                subtotalSinDescuento += precioOriginal * cantidad;
                if(precioOriginal > precioFinal) {
                    descuentoProductos += (precioOriginal - precioFinal) * cantidad;
                }
            } else {
                // Vista desktop (tabla)
                const priceCell = item.find('.cart__price');
                let precioOriginalText = priceCell.clone().children().remove().end().text().trim();
                precioOriginalText = precioOriginalText.replace('$', '').replace(/,/g, '').trim();
                const precioOriginal = parseFloat(precioOriginalText);
                
                const priceTachado = priceCell.find('small').text().replace('$', '').replace(/,/g, '').trim();
                const precioConDescuento = priceTachado ? parseFloat(precioOriginalText) : precioOriginal;
                
                const cantidad = parseInt(item.find('.qty-select-cart').val());
                
                if(priceTachado) {
                    const precioOriginalReal = parseFloat(priceTachado);
                    subtotalSinDescuento += precioOriginalReal * cantidad;
                    descuentoProductos += (precioOriginalReal - precioOriginal) * cantidad;
                } else {
                    subtotalSinDescuento += precioOriginal * cantidad;
                }
            }
        });
        
        // Calcular descuento adicional por cupón
        const descuentoCupon = subtotalSinDescuento * (discountPercentage / 100);
        const descuentoTotal = descuentoProductos + descuentoCupon;
        const total = subtotalSinDescuento - descuentoTotal;

        // Actualizar los valores en el DOM
        $('#cart-subtotal').text('$' + subtotalSinDescuento.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        if(descuentoTotal > 0) {
            $('#discount-row').addClass('active').show();
            $('#cart-discount').text('-$' + descuentoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        } else {
            $('#discount-row').removeClass('active').hide();
        }
        
        $('#cart-total').text('$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        // Actualizar también el footer móvil
        $('#mobile-footer-total').text('$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        // Actualizar o mostrar/ocultar el texto de ahorro en el footer móvil
        const savingsElement = $('.mobile-cart-footer__savings');
        if(descuentoTotal > 0) {
            if(savingsElement.length === 0) {
                // Crear el elemento si no existe
                $('.mobile-cart-footer__total').append(
                    '<div class="mobile-cart-footer__savings">Ahorras $' + 
                    descuentoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 
                    '</div>'
                );
            } else {
                // Actualizar el elemento existente
                savingsElement.text('Ahorras $' + descuentoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                savingsElement.show();
            }
        } else {
            savingsElement.hide();
        }
    }

    // ========== DROPDOWN DE CANTIDAD - NUEVO ==========
    
    // Toggle dropdown al hacer click en el botón
    $(document).on('click', '.qty-display-btn', function(e) {
        e.stopPropagation();
        const btn = $(this);
        const wrapper = btn.closest('.qty-select-wrapper');
        const dropdown = wrapper.find('.qty-dropdown');
        
        // Cerrar otros dropdowns abiertos
        $('.qty-dropdown').not(dropdown).removeClass('active');
        $('.qty-display-btn').not(btn).removeClass('active');
        
        // Toggle del dropdown actual
        dropdown.toggleClass('active');
        btn.toggleClass('active');
    });
    
    // Seleccionar cantidad del dropdown
    $(document).on('click', '.qty-option', function(e) {
        e.stopPropagation();
        const option = $(this);
        const newQty = parseInt(option.data('value'));
        const wrapper = option.closest('.qty-select-wrapper');
        const btn = wrapper.find('.qty-display-btn');
        const dropdown = wrapper.find('.qty-dropdown');
        const cartId = btn.data('id');
        const maxStock = btn.data('max');
        const prevQty = parseInt(btn.data('current'));
        
        // Validar stock
        if(newQty > maxStock) {
            alert('Stock máximo alcanzado: ' + maxStock);
            return;
        }
        
        // Actualizar visualmente
        btn.find('.qty-value').text(newQty);
        btn.data('current', newQty);
        
        // Actualizar clases de selección
        wrapper.find('.qty-option').removeClass('selected');
        option.addClass('selected');
        
        // Cerrar dropdown
        dropdown.removeClass('active');
        btn.removeClass('active');
        
        // Actualizar en el servidor
        if(newQty >= 1 && newQty !== prevQty) {
            updateCartQuantityAjax(cartId, newQty, btn);
        }
    });
    
    // Cerrar dropdowns al hacer click fuera
    $(document).on('click', function() {
        $('.qty-dropdown').removeClass('active');
        $('.qty-display-btn').removeClass('active');
    });
    
    // Prevenir que se cierre al hacer click dentro del dropdown
    $(document).on('click', '.qty-dropdown', function(e) {
        e.stopPropagation();
    });
    
    // ========== FIN DROPDOWN DE CANTIDAD ==========

    // Select de cantidad - Evento change (DEPRECADO - mantener por compatibilidad)
    $(document).on('change', '.qty-select-cart', function() {
        const select = $(this);
        const cartId = select.data('id');
        const maxStock = select.data('max');
        const newQty = parseInt(select.val());
        const prevQty = parseInt(select.data('prev-value') || select.val());
        
        // Guardar el valor anterior
        select.data('prev-value', prevQty);
        
        // Validar stock
        if(newQty > maxStock) {
            alert('Stock máximo alcanzado: ' + maxStock);
            select.val(Math.min(prevQty, maxStock));
            return;
        }
        
        if(newQty >= 1) {
            // Deshabilitar el select mientras se actualiza
            select.prop('disabled', true);
            updateCartQuantityAjax(cartId, newQty, select);
        } else {
            select.val(prevQty);
        }
    });
    
    // Hover para botón eliminar
    $(document).on('mouseenter', '.remove-cart-item', function() {
        $(this).css({
            'background': '#dc3545',
            'transform': 'scale(1.1)'
        });
        $(this).find('i').css('color', 'white');
    });
    
    $(document).on('mouseleave', '.remove-cart-item', function() {
        $(this).css({
            'background': '#fee',
            'transform': 'scale(1)'
        });
        $(this).find('i').css('color', '#dc3545');
    });
    
    // Actualizar cantidad del carrito CON AJAX (solo actualiza totales)
    function updateCartQuantityAjax(cartId, quantity, selectElement) {
        $.post('app/actions/update_cart_quantity.php', {
            id_carrito: cartId,
            cantidad: quantity
        }, function(response) {
            // Re-habilitar el select
            if(selectElement) {
                selectElement.prop('disabled', false);
            }
            
            if(response.success) {
                // Actualizar valor previo
                if(selectElement) {
                    selectElement.data('prev-value', quantity);
                }
                
                // Actualizar el precio total de la fila en tabla desktop
                const row = $('tr[data-cart-id="' + cartId + '"]');
                const priceCell = row.find('.cart__price');
                
                // Obtener el precio (primera línea del texto, sin el precio tachado)
                let priceText = priceCell.clone().children().remove().end().text().trim();
                priceText = priceText.replace('$', '').replace(/,/g, '').trim();
                
                const price = parseFloat(priceText);
                
                if(!isNaN(price)) {
                    const newTotal = price * quantity;
                    
                    // Actualizar total de la fila en tabla
                    row.find('.cart__total').text('$' + newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    
                    // Actualizar total en vista móvil
                    const mobileItem = $('.cart-mobile-item[data-cart-id="' + cartId + '"]');
                    mobileItem.find('.cart-mobile-item__total-amount').text('$' + newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    
                    // Actualizar select de cantidad en vista móvil si existe
                    mobileItem.find('.qty-select-cart').val(quantity);
                    
                    // Actualizar los totales del sidebar
                    updateCartTotals();
                } else {
                    location.reload();
                }
            } else {
                alert(response.message);
                // Revertir cantidad si hay error
                if(selectElement) {
                    const prevQty = parseInt(selectElement.data('prev-value') || 1);
                    selectElement.val(prevQty);
                }
            }
        }, 'json').fail(function() {
            // Re-habilitar el select en caso de error
            if(selectElement) {
                selectElement.prop('disabled', false);
                const prevQty = parseInt(selectElement.data('prev-value') || 1);
                selectElement.val(prevQty);
            }
            alert('Error al actualizar el carrito');
        });
    }
    
    // Eliminar item del carrito (usando Fetch API handler moderno)
    $(document).on('click', '.remove-cart-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const cartId = $(this).data('id');
        
        if (!cartId) {
            return;
        }

        // Preferir la función global removeFromCart si está disponible
        if (typeof window.removeFromCart === 'function') {
            window.removeFromCart(cartId).then(success => {
                if (!success) {
                    // Si la eliminación falló, puedes mostrar un mensaje o fallback
                    // Aquí dejamos que removeFromCart muestre notificación cuando falle
                }
            }).catch(err => {
            });
        } else {
            // Fallback al método antiguo por compatibilidad
            $.post('app/actions/remove_from_cart.php', {
                id_carrito: cartId
            }, function(response) {
                if(response.success) {
                    // Mostrar notificación
                    if (typeof window.showNotification === 'function') {
                        window.showNotification('Producto eliminado del carrito', 'success');
                    }
                    
                    // Eliminar fila de la tabla desktop con animación
                    $('tr[data-cart-id="' + cartId + '"]').fadeOut(400, function() {
                        $(this).remove();
                        // Actualizar totales
                        updateCartTotals();
                        // Verificar si quedó vacío
                        if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                            setTimeout(() => location.reload(), 500);
                        }
                    });

                    // Eliminar item de la vista móvil con animación
                    $('.cart-mobile-item[data-cart-id="' + cartId + '"]').fadeOut(400, function() {
                        $(this).remove();
                        updateCartTotals();
                        if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                            setTimeout(() => location.reload(), 500);
                        }
                    });
                    
                    // Actualizar contador
                    if(response.cart_count !== undefined) {
                        $('.cart-count, .header-cart-count').text(response.cart_count);
                        if(response.cart_count === 0) {
                            $('.cart-count, .header-cart-count').hide();
                        }
                    }
                } else {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(response.message || 'Error al eliminar', 'error');
                    }
                }
            }, 'json').fail(function() {
                if (typeof window.showNotification === 'function') {
                    window.showNotification('Error al eliminar el producto', 'error');
                }
            });
        }
    });

    // Escuchar evento personalizado disparado por fetch-api-handler.js cuando se elimina un item
    document.addEventListener('cartItemRemoved', function(e) {
        const cartId = e && e.detail && e.detail.cartId;
        if (!cartId) return;

        // Animación: si la fila aún existe, hacer fadeOut antes de removerla
        const $row = $('tr[data-cart-id="' + cartId + '"]');
        if ($row.length) {
            $row.fadeOut(300, function() {
                $(this).remove();
                updateCartTotals();
                // Solo recargar si el carrito quedó completamente vacío
                if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                    setTimeout(() => location.reload(), 500);
                }
            });
        }

        const $mobile = $('.cart-mobile-item[data-cart-id="' + cartId + '"]');
        if ($mobile.length) {
            $mobile.fadeOut(300, function() {
                $(this).remove();
                updateCartTotals();
                // Solo recargar si el carrito quedó completamente vacío
                if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                    setTimeout(() => location.reload(), 500);
                }
            });
        }
    });

    // Formulario de código de descuento
    $('#coupon-form').on('submit', function(e) {
        e.preventDefault();
        const codigo = $('#coupon-code').val().trim().toUpperCase();
        const messageDiv = $('#coupon-message');
        
        if(!codigo) {
            messageDiv.removeClass('success').addClass('error')
                .html('<i class="fa fa-exclamation-circle"></i> Por favor ingresa un código de descuento')
                .fadeIn();
            
            // Ocultar mensaje después de 3 segundos
            setTimeout(function() {
                messageDiv.fadeOut();
            }, 3000);
            
            return;
        }
        
        // Deshabilitar botón mientras procesa
        const btn = $('.btn-apply-discount');
        const originalText = btn.text();
        btn.prop('disabled', true).text('Verificando...');
        
        // Simular validación de cupón (puedes implementar una llamada AJAX real)
        setTimeout(function() {
            // Cupones de ejemplo (puedes cambiar esto por una llamada AJAX a tu backend)
            const validCoupons = {
                'DESCUENTO10': 10,
                'DESCUENTO20': 20,
                'BIENVENIDO': 15,
                'VERANO25': 25,
                'PROMO30': 30
            };
            
            if(validCoupons[codigo]) {
                discountPercentage = validCoupons[codigo];
                couponCode = codigo;
                
                messageDiv.removeClass('error').addClass('success')
                    .html('<i class="fa fa-check-circle"></i> ¡Cupón aplicado! Descuento del ' + discountPercentage + '%')
                    .fadeIn();
                
                // Ocultar mensaje después de 4 segundos
                setTimeout(function() {
                    messageDiv.fadeOut();
                }, 4000);
                
                // Actualizar totales con el descuento
                updateCartTotals();
                
                // Deshabilitar el campo y botón
                $('#coupon-code').prop('disabled', true);
                btn.text('✓ Aplicado').css({
                    'background': 'rgba(40, 167, 69, 0.2)',
                    'border-color': '#28a745',
                    'color': '#28a745'
                });
                
            } else {
                messageDiv.removeClass('success').addClass('error')
                    .html('<i class="fa fa-times-circle"></i> Código de descuento inválido o expirado')
                    .fadeIn();
                
                // Ocultar mensaje de error después de 4 segundos
                setTimeout(function() {
                    messageDiv.fadeOut();
                }, 4000);
                
                btn.prop('disabled', false).text(originalText);
            }
        }, 800);
    });

    // Inicializar totales al cargar la página
    $(document).ready(function() {
        // Inicializar descuento desde PHP
        const descuentoInicial = <?php echo $descuento_total; ?>;
        
        // Mostrar u ocultar fila de descuento según si hay descuento
        if(descuentoInicial > 0) {
            $('#discount-row').addClass('active').show();
        } else {
            $('#discount-row').removeClass('active').hide();
        }
        
        updateCartTotals();

    });

    // ======== MANEJO DE CHECKBOXES DE SELECCIÓN ========
    
    // Checkbox individual - Desktop
    $(document).on('change', '.item-checkbox', function() {
        updateCartTotals();
        updateSelectAllCheckbox();
    });
    
    // Checkbox individual - Mobile
    $(document).on('change', '.item-checkbox-mobile', function() {
        updateCartTotals();
        updateSelectAllCheckbox();
    });
    
    // Checkbox "Seleccionar todo"
    $(document).on('change', '#select-all-items', function() {
        const isChecked = $(this).is(':checked');
        $('.item-checkbox').prop('checked', isChecked);
        $('.item-checkbox-mobile').prop('checked', isChecked);
        updateCartTotals();
    });
    
    // Función para actualizar el estado del checkbox "Seleccionar todo"
    function updateSelectAllCheckbox() {
        const isMobile = window.innerWidth <= 576;
        const checkboxes = isMobile ? $('.item-checkbox-mobile') : $('.item-checkbox');
        const totalCheckboxes = checkboxes.length;
        const checkedCheckboxes = checkboxes.filter(':checked').length;
        
        const selectAllCheckbox = $('#select-all-items');
        
        if (checkedCheckboxes === 0) {
            selectAllCheckbox.prop('checked', false);
            selectAllCheckbox.prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            selectAllCheckbox.prop('checked', true);
            selectAllCheckbox.prop('indeterminate', false);
        } else {
            selectAllCheckbox.prop('checked', false);
            selectAllCheckbox.prop('indeterminate', true);
        }
    }
    
    // Inicializar al cargar la página
    $(document).ready(function() {
        updateSelectAllCheckbox();
        updateCartTotals();
    });
    
    // ======== AGREGAR AL CARRITO DESDE PRODUCTOS RELACIONADOS ========
    
    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const productId = btn.data('id');
        const isDisabled = btn.data('disabled');
        const isInCart = btn.data('in-cart') === 'true';
        
        // No hacer nada si está deshabilitado (sin stock)
        if (isDisabled) {
            return;
        }
        
        // Si ya está en carrito, removerlo
        if (isInCart) {
            removeProductFromCart(productId, btn);
            return;
        }
        
        // Agregar al carrito
        addToCartRelated(productId, btn);
    });
    
    function addToCartRelated(productId, btn) {
        // Deshabilitar botón temporalmente
        btn.prop('disabled', true).css('opacity', '0.6');
        
        $.ajax({
            url: 'app/actions/add_to_cart.php',
            type: 'POST',
            data: { 
                id_producto: productId,
                cantidad: 1
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Actualizar ícono del botón
                    btn.find('span').removeClass('icon_bag_alt').addClass('icon_check');
                    btn.data('in-cart', 'true');
                    btn.attr('title', 'Quitar del carrito');
                    
                    // Actualizar contador del header
                    if (response.cart_count !== undefined) {
                        $('.cart-count, .header-cart-count').text(response.cart_count);
                        if (response.cart_count > 0) {
                            $('.cart-count, .header-cart-count').show();
                        }
                    }
                    
                    // Mostrar notificación usando el sistema global
                    if (typeof window.showNotification === 'function') {
                        window.showNotification('Producto agregado al carrito', 'success');
                    }
                    
                    // Recargar página después de 1 segundo para actualizar la lista
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(response.message || 'Error al agregar al carrito', 'error');
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al agregar al carrito';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {}
                if (typeof window.showNotification === 'function') {
                    window.showNotification(errorMsg, 'error');
                }
            },
            complete: function() {
                btn.prop('disabled', false).css('opacity', '1');
            }
        });
    }
    
    function removeProductFromCart(productId, btn) {
        // Deshabilitar botón temporalmente
        btn.prop('disabled', true).css('opacity', '0.6');
        
        // Buscar el id_carrito del producto
        $.ajax({
            url: 'app/actions/remove_from_cart.php',
            type: 'POST',
            data: { 
                id_producto: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Actualizar ícono del botón
                    btn.find('span').removeClass('icon_check').addClass('icon_bag_alt');
                    btn.data('in-cart', 'false');
                    btn.attr('title', 'Agregar al carrito');
                    
                    // Actualizar contador del header
                    if (response.cart_count !== undefined) {
                        $('.cart-count, .header-cart-count').text(response.cart_count);
                        if (response.cart_count === 0) {
                            $('.cart-count, .header-cart-count').hide();
                        }
                    }
                    
                    // Mostrar notificación usando el sistema global
                    if (typeof window.showNotification === 'function') {
                        window.showNotification('Producto removido del carrito', 'info');
                    }
                    
                    // Recargar página después de 1 segundo
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(response.message || 'Error al remover del carrito', 'error');
                    }
                }
            },
            error: function() {
                if (typeof window.showNotification === 'function') {
                    window.showNotification('Error al remover del carrito', 'error');
                }
            },
            complete: function() {
                btn.prop('disabled', false).css('opacity', '1');
            }
        });
    }
    
    </script>
</body>
    
</html>
