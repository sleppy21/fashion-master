<?php
/**
 * P�GINA DE DETALLES DEL PRODUCTO
 * Muestra informaci�n completa de un producto espec�fico
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once 'config/conexion.php';

// Obtener ID del producto
$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($producto_id === 0) {
    header('Location: shop.php');
    exit;
}

// Obtener informaci�n del producto
$query = "SELECT p.id_producto, p.nombre_producto, p.precio_producto,
           p.codigo, p.descripcion_producto,
           COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
           p.genero_producto, p.en_oferta_producto, p.stock_actual_producto,
           p.url_imagen_producto,
           COALESCE(m.nombre_marca, 'Sin marca') as nombre_marca, 
           COALESCE(c.nombre_categoria, 'General') as nombre_categoria,
           c.id_categoria, m.id_marca
          FROM producto p
          LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
          LEFT JOIN marca m ON p.id_marca = m.id_marca
          WHERE p.id_producto = ? AND p.status_producto = 1";

$producto = executeQuery($query, [$producto_id]);

if (empty($producto)) {
    header('Location: shop.php');
    exit;
}

$producto = $producto[0];

// Verificar si el usuario está logueado y obtener datos completos
$usuario_logueado = null;
$nombre_usuario = null;
if(isset($_SESSION['user_id'])) {
    try {
        $user_query = "SELECT id_usuario, nombre_usuario, email_usuario, rol_usuario, 
                             fecha_registro, ultimo_acceso, telefono_usuario
                      FROM usuario 
                      WHERE id_usuario = ?";
        $user_result = executeQuery($user_query, [$_SESSION['user_id']]);
        if(!empty($user_result)) {
            $usuario_logueado = $user_result[0];
            $nombre_usuario = $usuario_logueado['nombre_usuario'];
        }
    } catch(Exception $e) {
        error_log("Error al obtener datos del usuario: " . $e->getMessage());
    }
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY nombre_categoria ASC");
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
}

// Obtener marcas para el menú
$marcas = [];
try {
    $marcas = executeQuery("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
} catch(Exception $e) {
    error_log("Error al obtener marcas: " . $e->getMessage());
}

// Obtener cantidad de items en carrito y favoritos
$cart_count = 0;
$favorites_count = 0;
$notifications_count = 0;
if($usuario_logueado) {
    try {
        $cart_items = executeQuery("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $cart_count = ($cart_items && count($cart_items) > 0) ? ($cart_items[0]['total'] ?? 0) : 0;
        
        $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $favorites_count = ($favorites && count($favorites) > 0) ? ($favorites[0]['total'] ?? 0) : 0;
        
        $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
        $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
    } catch(Exception $e) {
        error_log("Error al obtener carrito/favoritos/notificaciones: " . $e->getMessage());
    }
}

// Obtener favoritos del usuario
$favoritos_usuario = [];
$favoritos_ids = [];
if($usuario_logueado) {
    try {
        $favoritos_resultado = executeQuery("
            SELECT p.id_producto, p.nombre_producto, p.precio_producto, p.url_imagen_producto,
                   COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto
            FROM favorito f
            INNER JOIN producto p ON f.id_producto = p.id_producto
            WHERE f.id_usuario = ? AND p.status_producto = 1
            ORDER BY f.fecha_agregado_favorito DESC
        ", [$usuario_logueado['id_usuario']]);
        $favoritos_usuario = $favoritos_resultado ? $favoritos_resultado : [];
        
        foreach($favoritos_usuario as $fav) {
            $favoritos_ids[] = $fav['id_producto'];
        }
    } catch(Exception $e) {
        error_log("Error al obtener favoritos: " . $e->getMessage());
    }
}

// Si el usuario est� logueado, verificar si el producto est� en favoritos
$es_favorito = in_array($producto_id, $favoritos_ids);

// Verificar si el producto ya está en el carrito
$producto_en_carrito = false;
if($usuario_logueado) {
    try {
        $carrito_check = executeQuery("SELECT id_carrito FROM carrito WHERE id_usuario = ? AND id_producto = ?", 
            [$usuario_logueado['id_usuario'], $producto_id]);
        $producto_en_carrito = !empty($carrito_check);
    } catch(Exception $e) {
        error_log("Error al verificar carrito: " . $e->getMessage());
    }
}

$page_title = $producto['nombre_producto'];
?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?php echo htmlspecialchars($producto['descripcion_producto'] ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($producto['nombre_producto'] ?? ''); ?>, moda, ropa">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($producto['nombre_producto'] ?? 'Producto'); ?> - SleppyStore</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    
    <!-- Header Standard - COMPACTO v5.0 -->
    <link rel="stylesheet" href="public/assets/css/header-standard.css?v=5.0">
    
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- User Account Modal CSS -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    
    <!-- Favorites Modal CSS -->
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- Breadcrumb Moderno - Diseño consistente -->
    <link rel="stylesheet" href="public/assets/css/breadcrumb-modern.css?v=1.0" type="text/css">
    
    <!-- Product Details CSS -->
    <link rel="stylesheet" href="public/assets/css/product-details.css" type="text/css">
    
    <!-- Product Details Enhanced - Modo Claro Mejorado -->
    <link rel="stylesheet" href="public/assets/css/product-details-enhanced.css?v=<?php echo time(); ?>" type="text/css">
    
    <!-- Shop Modern Styles -->
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=3.0">
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    
    <!-- Dark Mode CSS - Force reload with timestamp -->
    <link rel="stylesheet" href="public/assets/css/dark-mode.css?v=<?php echo time(); ?>" type="text/css">
    
    <!-- Header Fix - DEBE IR AL FINAL -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-header-fix.css?v=<?= time() ?>">
</head>

<body>
    <?php include 'includes/offcanvas-menu.php'; ?>

    <?php 
    // Incluir header reutilizable
    include 'includes/header-section.php'; 
    ?>

    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="./index.php"><i class="fa fa-home"></i> Inicio</a>
                        <a href="./shop.php">Tienda</a>
                        <?php if(!empty($producto['nombre_categoria'])): ?>
                        <a href="./shop.php?c=<?php echo $producto['id_categoria']; ?>"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></a>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Product Details Section Begin -->
    <section class="product-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="product__details__pic">
                        <div class="product__details__slider__content">
                            <div class="product__details__pic__slider owl-carousel">
                                <img class="product__big__img" src="<?php echo htmlspecialchars($producto['url_imagen_producto']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="product__details__text">
                        <h3><?php echo htmlspecialchars($producto['nombre_producto']); ?> 
                            <?php if(!empty($producto['nombre_marca'])): ?>
                            <span>Marca: <?php echo htmlspecialchars($producto['nombre_marca']); ?></span>
                            <?php endif; ?>
                        </h3>
                        <div class="product__details__price">
                            <?php 
                            $precio_original = $producto['precio_producto'];
                            $tiene_descuento = $producto['descuento_porcentaje_producto'] > 0;
                            $precio_final = $precio_original;
                            if($tiene_descuento) {
                                $precio_final = $precio_original - ($precio_original * $producto['descuento_porcentaje_producto'] / 100);
                            }
                            ?>
                            $<?php echo number_format($precio_final, 2); ?>
                            <?php if($tiene_descuento): ?>
                            <span>$<?php echo number_format($precio_original, 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($producto['descripcion_producto'] ?? 'Sin descripción')); ?></p>
                        
                        <div class="product__details__widget">
                            <ul>
                                <li>
                                    <span>Disponibilidad:</span>
                                    <div class="stock__checkbox">
                                        <label for="stockin">
                                            <?php echo $producto['stock_actual_producto'] > 0 ? 'En Stock (' . $producto['stock_actual_producto'] . ' unidades)' : 'Agotado'; ?>
                                            <input type="checkbox" id="stockin" <?php echo $producto['stock_actual_producto'] > 0 ? 'checked' : ''; ?> disabled>
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </li>
                                <?php if(!empty($producto['nombre_categoria'])): ?>
                                <li>
                                    <span>Categoría:</span>
                                    <p><?php echo htmlspecialchars($producto['nombre_categoria']); ?></p>
                                </li>
                                <?php endif; ?>
                                <?php if(!empty($producto['genero_producto'])): ?>
                                <li>
                                    <span>Género:</span>
                                    <p><?php echo ucfirst(htmlspecialchars($producto['genero_producto'])); ?></p>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <!-- Sección de Acciones Mejorada -->
                        <div class="product__details__action">
                            <!-- Selector de Cantidad Mejorado -->
                            <div class="quantity-selector-modern">
                                <label class="quantity-label">Cantidad:</label>
                                <div class="quantity-input-group">
                                    <button type="button" class="qty-btn qty-minus" id="qty-minus">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="qty-input" 
                                           id="product-quantity" 
                                           value="1" 
                                           min="1" 
                                           max="<?php echo $producto['stock_actual_producto']; ?>"
                                           readonly>
                                    <button type="button" class="qty-btn qty-plus" id="qty-plus" data-max="<?php echo $producto['stock_actual_producto']; ?>">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <span class="stock-info">
                                    <?php if($producto['stock_actual_producto'] > 0): ?>
                                        <i class="fa fa-check-circle"></i> 
                                        <span class="stock-available"><?php echo $producto['stock_actual_producto']; ?> disponibles</span>
                                    <?php else: ?>
                                        <i class="fa fa-times-circle"></i> 
                                        <span class="stock-out">Sin stock</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <!-- Botones de Acción Mejorados -->
                            <div class="action-buttons-modern">
                                <?php if($usuario_logueado): ?>
                                    <?php if($producto['stock_actual_producto'] > 0): ?>
                                        <?php if($producto_en_carrito): ?>
                                            <!-- Producto ya en el carrito -->
                                            <button class="btn-add-cart-modern go-to-cart" 
                                                    data-id="<?php echo $producto['id_producto']; ?>">
                                                <i class="fa fa-shopping-cart"></i>
                                                <span>Ir al Carrito</span>
                                            </button>
                                        <?php else: ?>
                                            <!-- Producto no está en el carrito -->
                                            <button class="btn-add-cart-modern add-to-cart" 
                                                    data-id="<?php echo $producto['id_producto']; ?>">
                                                <i class="fa fa-shopping-cart"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn-add-cart-modern" disabled>
                                            <i class="fa fa-ban"></i>
                                            <span>Sin Stock</span>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn-favorite-modern add-to-favorites <?php echo $es_favorito ? 'active' : ''; ?>" 
                                            data-id="<?php echo $producto['id_producto']; ?>"
                                            title="<?php echo $es_favorito ? 'Quitar de favoritos' : 'Agregar a favoritos'; ?>">
                                        <i class="fa fa-heart<?php echo $es_favorito ? '' : '-o'; ?>"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn-add-cart-modern">
                                        <i class="fa fa-sign-in"></i>
                                        <span>Iniciar Sesión para Comprar</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Descripción</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Especificaciones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Reseñas</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <h6>Descripción del Producto</h6>
                                <div style="background: #f8f8f8; padding: 25px; border-radius: 10px; margin-bottom: 20px;">
                                    <p style="margin: 0; font-size: 15px; line-height: 1.8;">
                                        <?php echo nl2br(htmlspecialchars($producto['descripcion_producto'] ?? 'Este es un producto de alta calidad diseñado para satisfacer tus necesidades. Fabricado con los mejores materiales y siguiendo estrictos estándares de calidad.')); ?>
                                    </p>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-4 mb-3">
                                        <div style="text-align: center; padding: 20px; background: white; border: 1px solid #f0f0f0; border-radius: 10px;">
                                            <i class="fa fa-shield" style="font-size: 32px; color: #000; margin-bottom: 10px;"></i>
                                            <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">Garantía de Calidad</h6>
                                            <p style="font-size: 13px; color: #666; margin: 0;">Productos verificados</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div style="text-align: center; padding: 20px; background: white; border: 1px solid #f0f0f0; border-radius: 10px;">
                                            <i class="fa fa-truck" style="font-size: 32px; color: #000; margin-bottom: 10px;"></i>
                                            <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">Envío Rápido</h6>
                                            <p style="font-size: 13px; color: #666; margin: 0;">Entrega en 2-5 días</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div style="text-align: center; padding: 20px; background: white; border: 1px solid #f0f0f0; border-radius: 10px;">
                                            <i class="fa fa-refresh" style="font-size: 32px; color: #000; margin-bottom: 10px;"></i>
                                            <h6 style="font-size: 14px; font-weight: 600; margin-bottom: 5px;">Devoluciones</h6>
                                            <p style="font-size: 13px; color: #666; margin: 0;">30 días de garantía</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-2" role="tabpanel">
                                <h6>Especificaciones Técnicas</h6>
                                <div style="background: #f8f8f8; padding: 25px; border-radius: 10px;">
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        <?php if(!empty($producto['nombre_categoria'])): ?>
                                        <li style="padding: 15px 0; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between;">
                                            <strong style="color: #000; font-weight: 600;">
                                                <i class="fa fa-tag" style="margin-right: 10px; color: #666;"></i>Categoría:
                                            </strong>
                                            <span style="color: #666;"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if(!empty($producto['nombre_marca'])): ?>
                                        <li style="padding: 15px 0; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between;">
                                            <strong style="color: #000; font-weight: 600;">
                                                <i class="fa fa-certificate" style="margin-right: 10px; color: #666;"></i>Marca:
                                            </strong>
                                            <span style="color: #666;"><?php echo htmlspecialchars($producto['nombre_marca']); ?></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if(!empty($producto['genero_producto'])): ?>
                                        <li style="padding: 15px 0; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between;">
                                            <strong style="color: #000; font-weight: 600;">
                                                <i class="fa fa-user" style="margin-right: 10px; color: #666;"></i>Género:
                                            </strong>
                                            <span style="color: #666;"><?php echo ucfirst(htmlspecialchars($producto['genero_producto'])); ?></span>
                                        </li>
                                        <?php endif; ?>
                                        <li style="padding: 15px 0; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between;">
                                            <strong style="color: #000; font-weight: 600;">
                                                <i class="fa fa-cubes" style="margin-right: 10px; color: #666;"></i>Stock:
                                            </strong>
                                            <span style="color: <?php echo $producto['stock_actual_producto'] > 0 ? '#2ecc71' : '#e74c3c'; ?>; font-weight: 600;">
                                                <?php echo $producto['stock_actual_producto'] > 0 ? $producto['stock_actual_producto'] . ' unidades disponibles' : 'Agotado'; ?>
                                            </span>
                                        </li>
                                        <?php if(!empty($producto['codigo'])): ?>
                                        <li style="padding: 15px 0; display: flex; justify-content: space-between;">
                                            <strong style="color: #000; font-weight: 600;">
                                                <i class="fa fa-barcode" style="margin-right: 10px; color: #666;"></i>Código:
                                            </strong>
                                            <span style="color: #666; font-family: monospace;"><?php echo htmlspecialchars($producto['codigo']); ?></span>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <h6>Reseñas</h6>
                                <?php
                                // Obtener reseñas del producto
                                $query_resenas = "SELECT r.*, u.nombre_usuario 
                                                 FROM resena r 
                                                 INNER JOIN usuario u ON r.id_usuario = u.id_usuario 
                                                 WHERE r.id_producto = ? AND r.aprobada = 1 
                                                 ORDER BY r.fecha_creacion DESC";
                                $resenas = executeQuery($query_resenas, [$producto_id]);
                                $total_resenas = count($resenas);
                                $resenas_mostrar = array_slice($resenas, 0, 3); // Solo primeras 3
                                
                                if(!empty($resenas_mostrar)):
                                    foreach($resenas_mostrar as $resena):
                                ?>
                                <div class="review-item mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <strong><?php echo htmlspecialchars($resena['nombre_usuario']); ?></strong>
                                        <span class="ml-3">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa fa-star<?php echo $i <= $resena['calificacion'] ? '' : '-o'; ?>" style="color: #ffc107;"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <small class="text-muted ml-3"><?php echo date('d/m/Y', strtotime($resena['fecha_creacion'])); ?></small>
                                    </div>
                                    <h6><?php echo htmlspecialchars($resena['titulo']); ?></h6>
                                    <p><?php echo nl2br(htmlspecialchars($resena['comentario'])); ?></p>
                                    <?php if($resena['verificada']): ?>
                                    <small class="text-success"><i class="fa fa-check-circle"></i> Compra verificada</small>
                                    <?php endif; ?>
                                    <hr>
                                </div>
                                <?php 
                                    endforeach;
                                    
                                    // Mostrar botón "Ver más" si hay más de 3 reseñas
                                    if($total_resenas > 3):
                                ?>
                                <div class="text-center mt-4">
                                    <a href="reviews.php?producto=<?php echo $producto_id; ?>" class="btn btn-outline-dark">
                                        Ver todas las reseñas (<?php echo $total_resenas; ?>)
                                    </a>
                                </div>
                                <?php 
                                    endif;
                                else:
                                ?>
                                <p>Aún no hay reseñas para este producto.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Products Section -->
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="related__title">
                        <h5>PRODUCTOS RELACIONADOS</h5>
                    </div>
                </div>
            </div>
            
            <!-- Grid de productos relacionados con Masonry -->
            <div class="row productos-grid-related shop-modern" id="productosGridRelated">
                <?php
                // Obtener productos relacionados de la misma categoría
                $query_related = "SELECT p.id_producto, p.nombre_producto, p.precio_producto,
                                        p.url_imagen_producto, p.stock_actual_producto,
                                        COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
                                        p.en_oferta_producto,
                                        COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
                                        COUNT(r.id_resena) as total_resenas,
                                        COALESCE(m.nombre_marca, 'Sin marca') as nombre_marca,
                                        COALESCE(c.nombre_categoria, 'General') as nombre_categoria
                                 FROM producto p
                                 LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
                                 LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                                 LEFT JOIN marca m ON p.id_marca = m.id_marca
                                 WHERE p.id_categoria = ? 
                                 AND p.id_producto != ? 
                                 AND p.status_producto = 1
                                 GROUP BY p.id_producto
                                 ORDER BY RAND()
                                 LIMIT 6";
                $productos_relacionados = executeQuery($query_related, [$producto['id_categoria'], $producto_id]);
                
                // Cargar componente de tarjetas modernas
                require_once 'app/views/components/product-card.php';
                
                if(!empty($productos_relacionados)):
                    foreach($productos_relacionados as $prod):
                        $es_favorito_rel = in_array($prod['id_producto'], $favoritos_ids ?? []);
                        
                        // Verificar si está en el carrito
                        $in_cart = false;
                        if ($usuario_logueado) {
                            $cart_check = executeQuery(
                                "SELECT id_producto FROM carrito WHERE id_usuario = ? AND id_producto = ?",
                                [$usuario_logueado['id_usuario'], $prod['id_producto']]
                            );
                            $in_cart = !empty($cart_check);
                        }
                        
                        // Renderizar tarjeta moderna (incluye su propio wrapper col)
                        renderProductCard($prod, $es_favorito_rel, $usuario_logueado !== null, $in_cart);
                    endforeach;
                else:
                ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No hay productos relacionados disponibles.</p>
                </div>
                <?php
                endif;
                ?>
            </div>
        </div>
    </section>
    <!-- Product Details Section End --
    <!-- Search Begin -->
    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch">+</div>
            <form class="search-model-form">
                <input type="text" id="search-input" placeholder="Search here.....">
            </form>
        </div>
    </div>
    <!-- Search End -->

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
            
            window.BASE_URL = baseUrlFromPHP;
            console.log('🌐 BASE_URL configurado:', window.BASE_URL);
        })();
    </script>
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    
    <!-- Fetch API Handler Moderno - Reemplaza AJAX/jQuery -->
    <script src="public/assets/js/fetch-api-handler.js"></script>
    
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery.magnific-popup.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/mixitup.min.js"></script>
    <script src="public/assets/js/jquery.countdown.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/owl.carousel.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.min.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- Header Handler - Actualización en tiempo real de contadores -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Sistema Global de Contadores -->
    <script src="public/assets/js/global-counters.js"></script>
    
    <!-- Real-time Updates System - DEBE IR ANTES que cart-favorites-handler -->
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    
    <!-- Cart & Favorites Handler -->
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    
    <!-- User Account Modal -->
    <script src="public/assets/js/user-account-modal.js"></script>
    
    <!-- Product Details Handler -->
    <script src="public/assets/js/product-details-handler.js"></script>

    <!-- Masonry.js para grid de productos -->
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

    <style>
    /* ============================================
       FONDO DEL BODY
       ============================================ */
    body {
        background-color: #f8f5f2 !important;
    }
    
    /* Dark mode */
    body.dark-mode {
        background-color: #1a1a1a !important;
    }
    
    /* Breadcrumb con el mismo color de fondo */
    .breadcrumb-option {
        background-color: #f8f5f2 !important;
        padding: 15px 0 10px 0;
        margin-top: 1px;
        margin-bottom: 0;
    }
    
    body.dark-mode .breadcrumb-option {
        background-color: #1a1a1a !important;
    }
    
    /* Reducir espaciado en la sección de detalles */
    .product-details {
        padding-top: 20px !important;
        padding-bottom: 40px !important;
    }
    
    .product-details .spad {
        padding-top: 20px !important;
    }
    
    /* ============================================
       ESTILOS ESPECÍFICOS DE PRODUCT-DETAILS
       ============================================ */
    
    /* ============================================
       PRODUCT DETAILS RESPONSIVE
       ============================================ */

    /* ============================================
       PRODUCT DETAILS RESPONSIVE
       ============================================ */
    @media (max-width: 991px) {
        /* Centrar imagen del producto */
        .product__details__pic {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }

        .product__details__pic__slider img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        /* Dar más espacio al texto del producto */
        .product__details__text {
            padding: 0 20px !important;
        }
    }

    @media (max-width: 768px) {
        .product__details__text {
            padding: 0 15px !important;
        }

        .product__details__text h3 {
            font-size: 22px;
            line-height: 1.3;
        }

        .product__details__price {
            font-size: 26px;
        }
    }

    @media (max-width: 576px) {
        .product__details__text {
            padding: 0 10px !important;
        }

        .product__details__text h3 {
            font-size: 20px;
        }

        .product__details__price {
            font-size: 24px;
        }
    }

    /* ============================================
       QUANTITY INPUT - DISEÑO MODERNO RESPONSIVO
       ============================================ */
    
    /* Contenedor de cantidad */
    .product__details__button .quantity {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .product__details__button .quantity span {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        min-width: 80px;
    }

    /* Pro-qty moderno para PC */
    .pro-qty {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e0e0e0;
        border-radius: 50px;
        padding: 8px 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .pro-qty:hover {
        border-color: #ca1515;
        box-shadow: 0 6px 16px rgba(202, 21, 21, 0.15);
        transform: translateY(-2px);
    }

    .pro-qty .qtybtn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
        color: white;
        font-size: 20px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        user-select: none;
        box-shadow: 0 3px 8px rgba(202, 21, 21, 0.3);
    }

    .pro-qty .qtybtn:hover {
        background: linear-gradient(135deg, #e01717 0%, #ca1515 100%);
        transform: scale(1.1);
        box-shadow: 0 5px 12px rgba(202, 21, 21, 0.4);
    }

    .pro-qty .qtybtn:active {
        transform: scale(0.95);
    }

    .pro-qty input {
        width: 80px;
        height: 40px;
        border: none;
        background: transparent;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin: 0 10px;
        outline: none;
    }

    .pro-qty input::-webkit-outer-spin-button,
    .pro-qty input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .pro-qty input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    /* Tablets */
    @media (max-width: 991px) {
        .product__details__button .quantity {
            gap: 12px;
        }

        .product__details__button .quantity span {
            font-size: 15px;
            min-width: 70px;
        }

        .pro-qty {
            padding: 6px 12px;
        }

        .pro-qty .qtybtn {
            width: 38px;
            height: 38px;
            font-size: 18px;
        }

        .pro-qty input {
            width: 70px;
            height: 38px;
            font-size: 17px;
            margin: 0 8px;
        }
    }

    /* Móviles */
    @media (max-width: 768px) {
        .product__details__button .quantity {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 18px;
        }

        .product__details__button .quantity span {
            font-size: 14px;
            min-width: auto;
        }

        .pro-qty {
            padding: 5px 10px;
            border-radius: 40px;
            width: 100%;
            max-width: 200px;
            justify-content: space-between;
        }

        .pro-qty .qtybtn {
            width: 36px;
            height: 36px;
            font-size: 17px;
        }

        .pro-qty input {
            width: 60px;
            height: 36px;
            font-size: 16px;
            margin: 0 5px;
        }
    }

    /* Móviles pequeños */
    @media (max-width: 576px) {
        .product__details__button .quantity {
            gap: 8px;
            margin-bottom: 15px;
        }

        .product__details__button .quantity span {
            font-size: 13px;
        }

        .pro-qty {
            padding: 4px 8px;
            max-width: 180px;
        }

        .pro-qty .qtybtn {
            width: 34px;
            height: 34px;
            font-size: 16px;
        }

        .pro-qty input {
            width: 50px;
            height: 34px;
            font-size: 15px;
            margin: 0 4px;
        }
    }

    /* Móviles extra pequeños */
    @media (max-width: 400px) {
        .pro-qty {
            max-width: 160px;
            padding: 3px 6px;
        }

        .pro-qty .qtybtn {
            width: 32px;
            height: 32px;
            font-size: 15px;
        }

        .pro-qty input {
            width: 45px;
            height: 32px;
            font-size: 14px;
            margin: 0 3px;
        }
    }

    /* ============================================
       FIN QUANTITY INPUT
       ============================================ */

    /* Grid de productos relacionados con Masonry */
    .productos-grid-related {
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }

    .productos-grid-related .grid-item {
        padding-left: 15px;
        padding-right: 15px;
        margin-bottom: 30px;
    }

    /* Grid 2 columnas en móvil */
    @media (max-width: 991px) {
        .productos-grid-related .grid-item {
            width: 50% !important;
            flex: 0 0 50%;
            max-width: 50%;
        }

        /* Sistema de interacción móvil para productos */
        .product-details.spad {
            padding: 30px 0;
        }

        .product__details__text h3 {
            font-size: 22px;
            line-height: 1.3;
        }

        .product__details__text h3 span {
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }

        .product__details__price {
            font-size: 24px;
        }

        /* Título de relacionados */
        .related__title {
            margin-bottom: 20px;
        }

        .related__title h5 {
            font-size: 18px;
        }
    }

    @media (max-width: 576px) {
        .productos-grid-related {
            margin-left: -8px;
            margin-right: -8px;
        }

        .productos-grid-related .grid-item {
            padding-left: 8px;
            padding-right: 8px;
            margin-bottom: 15px;
        }

        .product__item {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .product__item__pic {
            min-height: 180px;
        }

        .product__item__text {
            padding: 12px;
        }

        .product__item__text h6 {
            font-size: 13px;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .product__price {
            font-size: 14px;
        }

        .product__item__pic__hover li a {
            width: 35px;
            height: 35px;
            font-size: 14px;
            line-height: 35px;
        }

        .offcanvas-menu-wrapper {
            width: 280px;
            left: -280px;
        }

        .offcanvas__nav {
            padding: 70px 20px 25px 20px;
        }
    }

    @media (max-width: 400px) {
        .product__item__pic {
            min-height: 160px;
        }

        .product__item__text h6 {
            font-size: 12px;
        }

        .product__price {
            font-size: 13px;
        }
    }

    /* Sistema de interacción móvil para productos */
    @media (max-width: 991px) {
        .product__item__pic .product__hover {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .product__item__pic.show-mobile-actions .product__hover {
            opacity: 1;
            visibility: visible;
        }

        .product__item__pic.show-mobile-actions::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
            pointer-events: none;
        }

        .product__item__pic.show-mobile-actions .product__hover {
            z-index: 2;
        }

        .product__item__pic.show-mobile-actions .product__hover li a {
            width: 42px;
            height: 42px;
            font-size: 16px;
            line-height: 42px;
            animation: fadeInUp 0.3s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product__item__pic:active {
            transform: scale(0.98);
        }
    }
    </style>

    <script>
    $(document).ready(function() {
        // ============================================
        // MASONRY LAYOUT PARA PRODUCTOS RELACIONADOS
        // ============================================
        let masonryInstance = null;

        function initMasonry() {
            const grid = document.querySelector('#productosGridRelated');
            if (!grid) return;

            // Destruir instancia anterior si existe
            if (masonryInstance) {
                masonryInstance.destroy();
                masonryInstance = null;
            }

            // Solo en móvil
            if (window.innerWidth <= 991) {
                imagesLoaded(grid, function() {
                    masonryInstance = new Masonry(grid, {
                        itemSelector: '.grid-item',
                        columnWidth: '.grid-item',
                        percentPosition: true,
                        gutter: 0,
                        transitionDuration: '0.3s'
                    });
                });
            }
        }

        // Inicializar al cargar
        initMasonry();

        // Reinicializar al redimensionar
        let resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initMasonry, 250);
        });

        // ============================================
        // FUNCIÓN DE NOTIFICACIÓN (FALLBACK)
        // ============================================
        // Esperar un poco para que cart-favorites-handler.js se cargue completamente
        setTimeout(function() {
            if (typeof window.showNotification !== 'function') {
                console.warn('⚠️ showNotification no disponible después de esperar, creando fallback');
                window.showNotification = function(message, type) {
                    console.log('[NOTIFICACIÓN ' + type + ']:', message);
                    alert(message);
                };
            } else {
                console.log('✅ showNotification disponible globalmente');
            }
        }, 100);

        // ============================================
        // BOTONES DE CANTIDAD MODERNOS
        // ============================================
        const $qtyInput = $('#product-quantity');
        const $qtyPlus = $('#qty-plus');
        const $qtyMinus = $('#qty-minus');
        const maxStock = parseInt($qtyPlus.data('max')) || 999;

        // Botón +
        $qtyPlus.on('click', function() {
            let currentVal = parseInt($qtyInput.val()) || 1;
            if (currentVal < maxStock) {
                $qtyInput.val(currentVal + 1);
                $qtyMinus.prop('disabled', false);
            }
            if (currentVal + 1 >= maxStock) {
                $(this).prop('disabled', true);
            }
        });

        // Botón -
        $qtyMinus.on('click', function() {
            let currentVal = parseInt($qtyInput.val()) || 1;
            if (currentVal > 1) {
                $qtyInput.val(currentVal - 1);
                $qtyPlus.prop('disabled', false);
            }
            if (currentVal - 1 <= 1) {
                $(this).prop('disabled', true);
            }
        });

        // Validar input manual
        $qtyInput.on('change', function() {
            let val = parseInt($(this).val()) || 1;
            if (val < 1) val = 1;
            if (val > maxStock) val = maxStock;
            $(this).val(val);
            
            // Actualizar estados de botones
            $qtyMinus.prop('disabled', val <= 1);
            $qtyPlus.prop('disabled', val >= maxStock);
        });

        // Estado inicial
        $qtyMinus.prop('disabled', true);
        if (maxStock <= 1) {
            $qtyPlus.prop('disabled', true);
        }

        // ============================================
        // FUNCIÓN PARA AGREGAR AL CARRITO CON ACTUALIZACIÓN EN TIEMPO REAL
        // ============================================
        // Verificar si el producto ya está en el carrito (desde PHP)
        let productoEnCarrito = <?php echo $producto_en_carrito ? 'true' : 'false'; ?>;

        // Evento para "Ir al Carrito" (cuando ya está en el carrito)
        $(document).on('click', '.go-to-cart', function(e) {
            e.preventDefault();
            window.location.href = 'cart.php';
        });

        // ===== BOTONES DE PRODUCTOS RELACIONADOS =====
        // DESHABILITADO - Ahora usa real-time-updates.js
        // Los productos relacionados usan las tarjetas modernas con real-time-updates.js
        console.log('✅ Botones de productos relacionados manejados por real-time-updates.js');
        
        /* CÓDIGO ANTIGUO DESHABILITADO
        // Evento para "Agregar al Carrito" (cuando no está en el carrito)
        $(document).on('click', '.add-to-cart', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const productoId = $btn.data('id');
            const cantidad = parseInt($qtyInput.val()) || 1;

            if (!productoId) {
                console.error('ID de producto no encontrado');
                if (window.showNotification) {
                    window.showNotification('Error: ID de producto no válido', 'error');
                }
                return;
            }

            // Verificar si está deshabilitado
            if ($btn.prop('disabled')) {
                if (window.showNotification) {
                    window.showNotification('Producto sin stock', 'warning');
                }
                return;
            }

            // Mostrar loading
            const originalHTML = $btn.html();
            $btn.html('<i class="fa fa-spinner fa-spin"></i> <span>Agregando...</span>');
            $btn.prop('disabled', true);

            console.log('Agregando al carrito:', {productoId, cantidad});

            // Hacer petición AJAX
            const baseUrl = window.BASE_URL || '';
            fetch(baseUrl + '/app/actions/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_producto=' + productoId + '&cantidad=' + cantidad
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Carrito response:', data);
                
                if (data.success) {
                    // Mostrar notificación de éxito
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Producto agregado al carrito', 'success');
                    }
                    
                    // Actualizar contador en tiempo real
                    actualizarContadoresTiempoReal('carrito');
                    
                    // Actualizar modal de favoritos para reflejar el estado del carrito
                    actualizarModalFavoritos();
                    
                    // Cambiar el botón a "Ir al Carrito" inmediatamente
                    productoEnCarrito = true;
                    $btn.html('<i class="fa fa-shopping-cart"></i> <span>Ir al Carrito</span>');
                    $btn.removeClass('add-to-cart').addClass('go-to-cart');
                    $btn.prop('disabled', false);
                    
                } else {
                    // Error del servidor
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Error al agregar al carrito', 'error');
                    }
                    console.error('Error en carrito:', data.message);
                    $btn.html(originalHTML);
                    $btn.prop('disabled', false);
                }
            })
            .catch(error => {
                console.error('Error catch carrito:', error);
                if (window.showNotification) {
                    window.showNotification('Error de conexión al procesar el carrito', 'error');
                }
                $btn.html(originalHTML);
                $btn.prop('disabled', false);
            });
        });
        FIN CÓDIGO ANTIGUO DESHABILITADO */

        // ============================================
        // FUNCIÓN PARA FAVORITOS CON ACTUALIZACIÓN EN TIEMPO REAL
        // ============================================
        // DESHABILITADO - Ahora usa real-time-updates.js
        console.log('✅ Favoritos manejados por real-time-updates.js');
        
        /* CÓDIGO ANTIGUO DESHABILITADO
        $(document).on('click', '.add-to-favorites', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const productoId = $btn.data('id');
            
            if (!productoId) {
                console.error('ID de producto no encontrado');
                if (window.showNotification) {
                    window.showNotification('Error: ID de producto no válido', 'error');
                }
                return;
            }

            // Mostrar loading
            const $icon = $btn.find('i, span');
            const iconOriginal = $icon.attr('class');
            
            // Si es un icono FA (i), mostrar spinner
            if ($icon.is('i')) {
                $icon.attr('class', 'fa fa-spinner fa-spin');
            } else {
                // Si es span (icon_heart_alt), agregar clase de loading visual
                $btn.css('opacity', '0.6');
            }
            
            $btn.css('pointer-events', 'none');

            console.log('Agregando/quitando favorito:', productoId);

            // Hacer petición AJAX
            const baseUrl = window.BASE_URL || '';
            fetch(baseUrl + '/app/actions/add_to_favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_producto=' + productoId
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Favoritos response:', data);
                
                if (data.success) {
                    // Determinar si se agregó o quitó
                    const esAgregar = data.action === 'added';
                    
                    // Actualizar estado visual del botón clickeado
                    if (esAgregar) {
                        $btn.addClass('active');
                        // Manejar iconos FA (i)
                        if ($icon.is('i')) {
                            $icon.attr('class', 'fa fa-heart');
                        }
                        // Manejar iconos theme (span)
                        else if ($icon.is('span')) {
                            $icon.attr('class', 'icon_heart');
                        }
                        $btn.attr('title', 'Quitar de favoritos');
                    } else {
                        $btn.removeClass('active');
                        // Manejar iconos FA (i)
                        if ($icon.is('i')) {
                            $icon.attr('class', 'fa fa-heart-o');
                        }
                        // Manejar iconos theme (span)
                        else if ($icon.is('span')) {
                            $icon.attr('class', 'icon_heart_alt');
                        }
                        $btn.attr('title', 'Agregar a favoritos');
                    }
                    
                    // Actualizar TODOS los iconos de este producto (productos relacionados y principal)
                    actualizarIconosFavoritos(productoId, esAgregar);
                    
                    // Mostrar notificación
                    if (window.showNotification) {
                        window.showNotification(data.message, 'success');
                    }
                    
                    // Actualizar contador de favoritos en tiempo real
                    actualizarContadoresTiempoReal('favoritos');
                    
                    // Actualizar modal de favoritos
                    actualizarModalFavoritos();
                    
                } else {
                    // Restaurar ícono original en caso de error
                    if ($icon && $icon.length > 0) {
                        $icon.attr('class', iconOriginal);
                    }
                    
                    if (window.showNotification) {
                        window.showNotification(data.message || 'Error al actualizar favoritos', 'error');
                    }
                    console.error('Error en favoritos:', data.message);
                }
            })
            .catch(error => {
                // Restaurar ícono original en caso de error
                if ($icon && $icon.length > 0) {
                    $icon.attr('class', iconOriginal);
                }
                $btn.css('opacity', '');
                
                console.error('Error catch favoritos:', error);
                if (window.showNotification) {
                    window.showNotification('Error de conexión al procesar favoritos', 'error');
                }
            })
            .finally(() => {
                $btn.css('pointer-events', '');
                $btn.css('opacity', '');
            });
        });
        FIN CÓDIGO ANTIGUO DESHABILITADO */

        // ============================================
        // FUNCIÓN PARA ACTUALIZAR CONTADORES EN TIEMPO REAL
        // ============================================
        function actualizarContadoresTiempoReal(tipo) {
            console.log('Actualizando contadores:', tipo);
            const baseUrl = window.BASE_URL || '';
            
            if (tipo === 'carrito' || tipo === 'ambos') {
                fetch(baseUrl + '/app/actions/get_cart_count.php')
                    .then(res => res.json())
                    .then(data => {
                        console.log('Contador carrito actualizado:', data);
                        if (data.success) {
                            const count = parseInt(data.count) || 0;
                            
                            // Buscar el link del carrito
                            const $cartLink = $('a[href*="cart.php"]').first();
                            let $tip = $cartLink.find('.tip');
                            
                            if (count > 0) {
                                if ($tip.length === 0) {
                                    // Crear el elemento .tip si no existe
                                    $tip = $('<div class="tip"></div>');
                                    $cartLink.append($tip);
                                }
                                $tip.text(count).show();
                            } else {
                                // Ocultar si es 0
                                $tip.hide();
                            }
                            
                            console.log('Contador de carrito actualizado a:', count);
                        }
                    })
                    .catch(err => console.error('Error al actualizar contador carrito:', err));
            }
            
            if (tipo === 'favoritos' || tipo === 'ambos') {
                const baseUrl = window.BASE_URL || '';
                fetch(baseUrl + '/app/actions/get_favorites_count.php')
                    .then(res => res.json())
                    .then(data => {
                        console.log('Contador favoritos actualizado:', data);
                        if (data.success) {
                            const count = parseInt(data.count) || 0;
                            
                            // Buscar el link de favoritos
                            const $favLink = $('#favorites-link');
                            let $tip = $favLink.find('.tip');
                            
                            if (count > 0) {
                                if ($tip.length === 0) {
                                    // Crear el elemento .tip si no existe
                                    $tip = $('<div class="tip"></div>');
                                    $favLink.append($tip);
                                }
                                $tip.text(count).show();
                            } else {
                                // Ocultar si es 0
                                $tip.hide();
                            }
                            
                            console.log('Contador de favoritos actualizado a:', count);
                        }
                    })
                    .catch(err => console.error('Error al actualizar contador favoritos:', err));
            }
        }

        // ============================================
        // FUNCIÓN PARA ACTUALIZAR MODAL DE FAVORITOS
        // ============================================
        function actualizarModalFavoritos() {
            const $modalBody = $('.favorites-modal-body');
            if ($modalBody.length === 0) {
                console.log('Modal de favoritos no encontrado en el DOM');
                return;
            }

            console.log('Actualizando modal de favoritos...');
            
            const baseUrl = window.BASE_URL || '';
            fetch(baseUrl + '/app/actions/get_favorites.php')
                .then(res => res.json())
                .then(data => {
                    console.log('Modal favoritos response:', data);
                    if (data.success) {
                        $modalBody.html(data.html);
                        console.log('✅ Modal de favoritos actualizado');
                    }
                })
                .catch(err => console.error('Error al actualizar modal favoritos:', err));
        }

        // ============================================
        // ACTUALIZAR ICONOS DE FAVORITOS EN PRODUCTOS RELACIONADOS
        // ============================================
        function actualizarIconosFavoritos(productoId, esAgregar) {
            console.log('Actualizando iconos para producto:', productoId, 'Agregar:', esAgregar);
            
            // Buscar todos los botones de favoritos para este producto
            $(`.add-to-favorites[data-id="${productoId}"]`).each(function() {
                const $btn = $(this);
                const $iconFA = $btn.find('i');
                const $iconTheme = $btn.find('span');
                
                if (esAgregar) {
                    // Agregar a favoritos
                    $btn.addClass('active');
                    
                    // Actualizar icono Font Awesome
                    if ($iconFA.length > 0) {
                        $iconFA.removeClass('fa-heart-o').addClass('fa-heart');
                    }
                    
                    // Actualizar icono del theme
                    if ($iconTheme.length > 0) {
                        $iconTheme.removeClass('icon_heart_alt').addClass('icon_heart');
                    }
                    
                    $btn.attr('title', 'Quitar de favoritos');
                } else {
                    // Quitar de favoritos
                    $btn.removeClass('active');
                    
                    // Actualizar icono Font Awesome
                    if ($iconFA.length > 0) {
                        $iconFA.removeClass('fa-heart').addClass('fa-heart-o');
                    }
                    
                    // Actualizar icono del theme
                    if ($iconTheme.length > 0) {
                        $iconTheme.removeClass('icon_heart').addClass('icon_heart_alt');
                    }
                    
                    $btn.attr('title', 'Agregar a favoritos');
                }
            });
            
            console.log('✅ Iconos actualizados para producto:', productoId);
        }

        // ============================================
        // ACTUALIZAR BOTÓN DE CARRITO EN LA PÁGINA
        // ============================================
        function actualizarBotonCarritoPagina(productoId, enCarrito) {
            console.log('Actualizando botón de carrito en página:', productoId, 'En carrito:', enCarrito);
            
            // Buscar el botón de carrito (puede tener clase add-to-cart o go-to-cart)
            let $btn = $('.add-to-cart');
            if ($btn.length === 0) {
                $btn = $('.go-to-cart');
            }
            
            if ($btn.length === 0) {
                console.log('⚠️ Botón de carrito no encontrado en la página');
                return;
            }
            
            const currentProductId = $btn.data('id');
            
            // Solo actualizar si es el producto actual
            if (currentProductId == productoId) {
                if (enCarrito) {
                    // Cambiar a "Ir al Carrito"
                    productoEnCarrito = true;
                    $btn.html('<i class="fa fa-shopping-cart"></i> <span>Ir al Carrito</span>');
                    $btn.removeClass('add-to-cart').addClass('go-to-cart');
                } else {
                    // Cambiar a "Agregar al Carrito"
                    productoEnCarrito = false;
                    $btn.html('<i class="fa fa-shopping-cart"></i> <span>Agregar al Carrito</span>');
                    $btn.removeClass('go-to-cart').addClass('add-to-cart');
                }
                console.log('✅ Botón de carrito actualizado en la página');
            }
        }

        // ============================================
        // SISTEMA DE INTERACCIÓN SEPARADO PC Y MÓVIL
        // ============================================
        const esDispositivoMovil = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        if (esDispositivoMovil) {
            // MÓVIL: 1er tap = mostrar botones, 2do tap = ir a detalles
            $(document).on('click', '.product__item__pic', function(e) {
                const $pic = $(this);
                const $target = $(e.target);
                
                if ($target.closest('.product__hover').length) {
                    return;
                }
                
                if ($pic.hasClass('show-mobile-actions')) {
                    const productUrl = $pic.data('product-url');
                    if (productUrl && !$target.closest('.product__hover').length) {
                        window.location.href = productUrl;
                    }
                } else {
                    $('.product__item__pic').removeClass('show-mobile-actions');
                    $pic.addClass('show-mobile-actions');
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.product__item__pic').length) {
                    $('.product__item__pic').removeClass('show-mobile-actions');
                }
            });
        } else {
            // PC: Click directo va a detalles
            $(document).on('click', '.product__item__pic', function(e) {
                const $target = $(e.target);
                
                if ($target.closest('.product__hover').length) {
                    return;
                }
                
                const productUrl = $(this).data('product-url');
                if (productUrl) {
                    window.location.href = productUrl;
                }
            });
        }

        // ============================================
        // EXPONER FUNCIONES GLOBALMENTE para cart-favorites-handler.js
        // ============================================
        window.actualizarIconosFavoritos = actualizarIconosFavoritos;
        window.actualizarBotonCarritoPagina = actualizarBotonCarritoPagina;
        
        console.log('✅ Funciones de sincronización exportadas globalmente');
    });
    </script>

    <!-- Global Offcanvas Menu JavaScript -->
    <script src="public/assets/js/offcanvas-menu.js"></script>
    
    <!-- Dark Mode JavaScript -->
    <script src="public/assets/js/dark-mode.js"></script>

    <script>
    // Asegurar que las tarjetas de productos relacionados funcionen
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ Product-details: Tarjetas modernas cargadas');
        
        // Desactivar AOS animations en productos relacionados para mejor performance
        const relatedProducts = document.querySelectorAll('.productos-grid-related .product-card-modern');
        relatedProducts.forEach(card => {
            card.removeAttribute('data-aos');
        });
        
        // DEBUG: Verificar que los botones existen
        const cartButtons = document.querySelectorAll('.productos-grid-related .add-to-cart');
        const favButtons = document.querySelectorAll('.productos-grid-related .add-to-favorites');
        console.log('🛒 Botones de carrito encontrados:', cartButtons.length);
        console.log('❤️ Botones de favoritos encontrados:', favButtons.length);
        
        // DEBUG: Listener global para verificar clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.productos-grid-related')) {
                console.log('🖱️ Click en productos relacionados:', e.target);
            }
        }, true); // Usar capture phase
    });
    </script>

    <?php if($usuario_logueado): ?>
    <!-- Modales -->
    <?php include 'includes/user-account-modal.php'; ?>
    <?php include 'includes/favorites-modal.php'; ?>
    <?php include 'includes/notifications-modal.php'; ?>
    <?php endif; ?>

    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>

</html>

