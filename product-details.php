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
if($usuario_logueado) {
    try {
        $cart_items = executeQuery("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $cart_count = ($cart_items && count($cart_items) > 0) ? ($cart_items[0]['total'] ?? 0) : 0;
        
        $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $favorites_count = ($favorites && count($favorites) > 0) ? ($favorites[0]['total'] ?? 0) : 0;
    } catch(Exception $e) {
        error_log("Error al obtener carrito/favoritos: " . $e->getMessage());
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
    
    <!-- User Account Modal CSS -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    
    <!-- Favorites Modal CSS -->
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Header Responsive Global CSS -->
    <link rel="stylesheet" href="public/assets/css/header-responsive.css?v=2.0" type="text/css">
    
    <!-- Header Override - Máxima prioridad -->
    <link rel="stylesheet" href="public/assets/css/header-override.css?v=2.0" type="text/css">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- Product Details CSS -->
    <link rel="stylesheet" href="public/assets/css/product-details.css" type="text/css">
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
                        <a href="./shop.php?categoria=<?php echo $producto['id_categoria']; ?>"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></a>
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
                        
                        <div class="product__details__button">
                            <div class="quantity">
                                <span>Cantidad:</span>
                                <div class="pro-qty">
                                    <span class="qtybtn qty-btn-detail qty-minus">-</span>
                                    <input type="text" value="0" id="product-quantity">
                                    <span class="qtybtn qty-btn-detail qty-plus" data-max="<?php echo $producto['stock_actual_producto']; ?>">+</span>
                                </div>
                            </div>
                            <?php if($usuario_logueado): ?>
                            <a href="#" class="cart-btn add-to-cart" data-id="<?php echo $producto['id_producto']; ?>" <?php echo $producto['stock_actual_producto'] <= 0 ? 'style="opacity:0.5;" data-disabled="true"' : ''; ?>>
                                <span class="icon_bag_alt"></span> Agregar al carrito
                            </a>
                            <ul>
                                <li>
                                    <a href="#" class="add-to-favorites <?php echo $es_favorito ? 'active' : ''; ?>" 
                                       data-id="<?php echo $producto['id_producto']; ?>"
                                       title="<?php echo $es_favorito ? 'Quitar de favoritos' : 'Agregar a favoritos'; ?>">
                                        <span class="icon_heart<?php echo $es_favorito ? '' : '_alt'; ?>"></span>
                                    </a>
                                </li>
                            </ul>
                            <?php else: ?>
                            <a href="login.php" class="cart-btn">
                                <span class="icon_bag_alt"></span> Iniciar sesión para comprar
                            </a>
                            <?php endif; ?>
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
            <div class="row productos-grid-related" id="productosGridRelated">
                <?php
                // Obtener productos relacionados de la misma categoría
                $query_related = "SELECT p.id_producto, p.nombre_producto, p.precio_producto,
                                        p.url_imagen_producto, p.stock_actual_producto,
                                        COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
                                        p.en_oferta_producto,
                                        COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
                                        COUNT(r.id_resena) as total_resenas
                                 FROM producto p
                                 LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
                                 WHERE p.id_categoria = ? 
                                 AND p.id_producto != ? 
                                 AND p.status_producto = 1
                                 GROUP BY p.id_producto
                                 ORDER BY RAND()
                                 LIMIT 6";
                $productos_relacionados = executeQuery($query_related, [$producto['id_categoria'], $producto_id]);
                
                if(!empty($productos_relacionados)):
                    foreach($productos_relacionados as $prod):
                        $precio_original_rel = $prod['precio_producto'];
                        $tiene_descuento_rel = $prod['descuento_porcentaje_producto'] > 0;
                        $precio_final_rel = $precio_original_rel;
                        if($tiene_descuento_rel) {
                            $precio_final_rel = $precio_original_rel - ($precio_original_rel * $prod['descuento_porcentaje_producto'] / 100);
                        }
                        $sin_stock_rel = $prod['stock_actual_producto'] <= 0;
                        $es_favorito_rel = in_array($prod['id_producto'], $favoritos_ids ?? []);
                ?>
                <div class="grid-item col-lg-3 col-md-4 col-6">
                    <div class="product__item <?php echo $tiene_descuento_rel ? 'sale' : ''; ?>">
                        <div class="product__item__pic set-bg product-image-clickable" 
                             data-setbg="<?php echo htmlspecialchars($prod['url_imagen_producto']); ?>"
                             data-id="<?php echo $prod['id_producto']; ?>"
                             data-product-url="product-details.php?id=<?php echo $prod['id_producto']; ?>"
                             style="background-image: url('<?php echo htmlspecialchars($prod['url_imagen_producto']); ?>'); background-size: cover; background-position: center; cursor: pointer;">
                            <?php if($sin_stock_rel): ?>
                                <div class="label stockout">Sin stock</div>
                            <?php elseif($tiene_descuento_rel): ?>
                                <div class="label sale">-<?php echo round($prod['descuento_porcentaje_producto']); ?>%</div>
                            <?php endif; ?>
                            <ul class="product__hover">
                                <li><a href="product-details.php?id=<?php echo $prod['id_producto']; ?>" class="view-details-btn"><span class="icon_search"></span></a></li>
                                <li>
                                    <a href="#" class="add-to-favorites <?php echo $es_favorito_rel ? 'active' : ''; ?>" 
                                       data-id="<?php echo $prod['id_producto']; ?>">
                                        <span class="icon_heart<?php echo $es_favorito_rel ? '' : '_alt'; ?>"></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="add-to-cart" 
                                       data-id="<?php echo $prod['id_producto']; ?>"
                                       <?php echo $sin_stock_rel ? 'style="opacity:0.5;" data-disabled="true"' : ''; ?>>
                                        <span class="icon_bag_alt"></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="product__item__text">
                            <h6><span style="cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $prod['id_producto']; ?>'">
                                <?php echo htmlspecialchars($prod['nombre_producto']); ?>
                            </span></h6>
                            <div class="rating">
                                <?php 
                                $rating = round($prod['calificacion_promedio']);
                                for($i = 1; $i <= 5; $i++): 
                                ?>
                                <i class="fa fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                                <?php if($prod['total_resenas'] > 0): ?>
                                    <span style="font-size: 11px; color: #999; margin-left: 5px;">(<?php echo $prod['total_resenas']; ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="product__price">
                                $<?php echo number_format($precio_final_rel, 2); ?>
                                <?php if($tiene_descuento_rel): ?>
                                <span>$<?php echo number_format($precio_original_rel, 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
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
    <!-- Product Details Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-7">
                    <div class="footer__about">
                        <div class="footer__logo">
                            <a href="./index.php"><img src="public/assets/img/logo.png" alt="SleppyStore"></a>
                        </div>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt
                        cilisis.</p>
                        <div class="footer__payment">
                            <a href="#"><img src="public/assets/img/payment/payment-1.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-2.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-3.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-4.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-5.png" alt=""></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-5">
                    <div class="footer__widget">
                        <h6>Quick links</h6>
                        <ul>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Blogs</a></li>
                            <li><a href="#">Contact</a></li>
                            <li><a href="#">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <div class="footer__widget">
                        <h6>Account</h6>
                        <ul>
                            <li><a href="#">My Account</a></li>
                            <li><a href="#">Orders Tracking</a></li>
                            <li><a href="#">Checkout</a></li>
                            <li><a href="#">Wishlist</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-8 col-sm-8">
                    <div class="footer__newslatter">
                        <h6>NEWSLETTER</h6>
                        <form action="#">
                            <input type="text" placeholder="Email">
                            <button type="submit" class="site-btn">Subscribe</button>
                        </form>
                        <div class="footer__social">
                            <a href="#"><i class="fa fa-facebook"></i></a>
                            <a href="#"><i class="fa fa-twitter"></i></a>
                            <a href="#"><i class="fa fa-youtube-play"></i></a>
                            <a href="#"><i class="fa fa-instagram"></i></a>
                            <a href="#"><i class="fa fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    <div class="footer__copyright__text">
                        <p>Copyright &copy; <script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a></p>
                    </div>
                    <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->

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
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery.magnific-popup.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/mixitup.min.js"></script>
    <script src="public/assets/js/jquery.countdown.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/owl.carousel.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.min.js"></script>
    <script src="public/assets/js/main.js"></script>
    
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
    });
    </script>

    <!-- Global Offcanvas Menu JavaScript -->
    <script src="public/assets/js/offcanvas-menu.js"></script>

    <?php if($usuario_logueado): ?>
    <!-- Modales -->
    <?php include 'includes/user-account-modal.php'; ?>
    <?php include 'includes/favorites-modal.php'; ?>
    <?php endif; ?>

</body>

</html>

