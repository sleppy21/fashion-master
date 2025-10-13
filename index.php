<?php
/**
 * PÁGINA PRINCIPAL - INDEX
 * Inicio de SleppyStore con productos destacados dinámicos
 */

// Habilitar reporte de errores para debugging (remover en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si es una llamada a la API del bot
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Las APIs del bot se manejan desde fashion_store_complete.py
// if($path === '/health' || $path === '/api/chat' || $path === '/api/suggestions') {
//     include 'bot_api.php';
//     exit;
// }

session_start();

// Verificar que el archivo de conexión existe
if (!file_exists('config/conexion.php')) {
    die('Error: Archivo de configuración no encontrado.');
}

require_once 'config/conexion.php';

$page_title = "Inicio";

// Verificar si hay usuario logueado
$usuario_logueado = null;
if(isset($_SESSION['user_id'])) {
    try {
        $usuario_resultado = executeQuery("SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1", [$_SESSION['user_id']]);
        $usuario_logueado = ($usuario_resultado && count($usuario_resultado) > 0) ? $usuario_resultado[0] : null;
    } catch(Exception $e) {
        error_log("Error al verificar usuario: " . $e->getMessage());
        $usuario_logueado = null;
    }
}

// Obtener cantidad de items en carrito
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
        $cart_count = 0;
        $favorites_count = 0;
    }
}

// Obtener productos destacados y en oferta
$productos_destacados = [];
try {
    $productos_resultado = executeQuery("
        SELECT p.id_producto, p.nombre_producto, p.precio_producto,
               p.codigo, p.descripcion_producto,
               COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
               p.genero_producto, p.en_oferta_producto, p.stock_actual_producto,
               p.url_imagen_producto,
               COALESCE(m.nombre_marca, 'Sin marca') as nombre_marca, 
               COALESCE(c.nombre_categoria, 'General') as nombre_categoria,
               c.id_categoria,
               COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
               COUNT(r.id_resena) as total_resenas
        FROM producto p
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
        LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
        WHERE p.status_producto = 1 AND p.estado = 'activo'
        GROUP BY p.id_producto, p.nombre_producto, p.precio_producto, p.codigo,
                 p.descripcion_producto, p.descuento_porcentaje_producto, p.genero_producto,
                 p.en_oferta_producto, p.stock_actual_producto, p.url_imagen_producto,
                 m.nombre_marca, c.nombre_categoria, c.id_categoria
        ORDER BY p.id_producto DESC
    ");
    $productos_destacados = $productos_resultado ? $productos_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener productos: " . $e->getMessage());
    $productos_destacados = [];
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY id_categoria ASC LIMIT 5");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="SleppyStore - Tu tienda de moda online">
    <meta name="keywords" content="moda, ropa, zapatos, accesorios, tienda online">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $page_title; ?> - SleppyStore</title>

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
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- Breadcrumb Moderno - Diseño consistente -->
    <link rel="stylesheet" href="public/assets/css/breadcrumb-modern.css?v=1.0" type="text/css">
    
    <!-- Modern Design Improvements -->
    <link rel="stylesheet" href="public/assets/css/modern-improvements.css" type="text/css">
    
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- Chat Widget Styles -->
    <link rel="stylesheet" href="public/assets/css/fashion-chat-button.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/fashion-chat-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/fashion-chat-states.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/fashion-chat-states.css?v=1" type="text/css">
    
    <!-- User Account Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    
    <!-- Favorites Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <style>
        /* Header Sticky - Siempre visible */
        .header {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #ffffff;
            box-shadow: 0px 5px 10px rgba(91, 91, 91, 0.1);
            transition: all 0.3s ease;
        }

        .header.scrolled {
            box-shadow: 0px 8px 16px rgba(91, 91, 91, 0.15);
        }

        /* Prevenir parpadeo de imágenes en productos */
        .product__item__pic.set-bg {
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            min-height: 320px;
            transition: none !important;
            cursor: pointer;
        }
        
        .product__item__pic.set-bg:active {
            transform: scale(0.98);
        }

        /* Nombre del producto clickeable */
        .product__item__text h6 span {
            transition: color 0.3s ease;
        }

        .product__item__text h6 span:hover {
            color: #ca1515;
        }
    </style>
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Offcanvas Menu Begin -->
    <div class="offcanvas-menu-overlay"></div>
    <div class="offcanvas-menu-wrapper">
        <div class="offcanvas__close">+</div>
        <ul class="offcanvas__widget">
            <li><span class="icon_search search-switch"></span></li>
            <?php if($usuario_logueado): ?>
            <li><a href="#" id="favorites-link-mobile"><span class="icon_heart_alt"></span>
                <?php if($favorites_count > 0): ?>
                <div class="tip"><?php echo $favorites_count; ?></div>
                <?php endif; ?>
            </a></li>
            <?php else: ?>
            <li><a href="login.php"><span class="icon_heart_alt"></span></a></li>
            <?php endif; ?>
            <li><a href="cart.php"><span class="icon_bag_alt"></span>
                <?php if($cart_count > 0): ?>
                <div class="tip"><?php echo $cart_count; ?></div>
                <?php endif; ?>
            </a></li>
        </ul>
        <div class="offcanvas__logo">
            <a href="./index.php"><img src="public/assets/img/logo.png" alt="SleppyStore"></a>
        </div>
        <div id="mobile-menu-wrap"></div>
        <div class="offcanvas__auth">
            <?php if($usuario_logueado): ?>
                <a href="account.php">Mi Cuenta</a>
                <a href="logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar Sesión</a>
                <a href="register.php">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Offcanvas Menu End -->

    <?php 
    // ===================================================================
    // HEADER RESPONSIVE - VERSIÓN 2.0 - INCLUIDO DESDE header-section.php
    // Si ves código HTML estático aquí, limpia el cache del navegador
    // ===================================================================
    include 'includes/header-section.php'; 
    ?>

    <!-- Product Section Begin -->
    <section class="product spad" id="destacados">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4" data-aos="fade-right">
                    <div class="section-title">
                        <h4>Productos Destacados</h4>
                    </div>
                </div>
            <div class="col-lg-8 col-md-8" data-aos="fade-left">
                <ul class="filter__controls">
                    <li class="active" data-filter="*">All</li>
                    <li data-filter=".women">Women's</li>
                    <li data-filter=".men">Men's</li>
                    <li data-filter=".kid">Kid's</li>
                    <li data-filter=".accessories">Accessories</li>
                    <li data-filter=".cosmetic">Cosmetics</li>
                </ul>
            </div>
        </div>
        <div class="row property__gallery">
            <?php if(!empty($productos_destacados)): ?>
                <?php foreach($productos_destacados as $producto): 
                    // Determinar la clase de género para el filtro
                    $gender_class = '';
                    switch($producto['genero_producto']) {
                        case 'F':
                            $gender_class = 'women';
                            break;
                        case 'M':
                            $gender_class = 'men';
                            break;
                        case 'Kids':
                            $gender_class = 'kid';
                            break;
                        default:
                            $gender_class = 'women men kid accessories cosmetic';
                    }
                    
                    // Calcular precio con descuento
                    $precio_original = $producto['precio_producto'];
                    $tiene_descuento = $producto['descuento_porcentaje_producto'] > 0;
                    $precio_final = $precio_original;
                    if($tiene_descuento) {
                        $precio_final = $precio_original - ($precio_original * $producto['descuento_porcentaje_producto'] / 100);
                    }
                    
                    // Determinar la imagen del producto - Usar versión _shop si existe
                    $imagen_url = !empty($producto['url_imagen_producto']) ? $producto['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                    
                    // Verificar si está sin stock
                    $sin_stock = $producto['stock_actual_producto'] <= 0;
                    
                    // Determinar si es nuevo (últimos 7 productos)
                    static $contador = 0;
                    $contador++;
                    $es_nuevo = $contador <= 3;
                ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mix <?php echo $gender_class; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($contador * 50); ?>">
                <div class="product__item <?php echo $tiene_descuento ? 'sale' : ''; ?>">
                    <div class="product__item__pic set-bg product-image-clickable" 
                         data-setbg="<?php echo htmlspecialchars($imagen_url); ?>"
                         data-id="<?php echo $producto['id_producto']; ?>"
                         data-product-url="product-details.php?id=<?php echo $producto['id_producto']; ?>"
                         style="background-image: url('<?php echo htmlspecialchars($imagen_url); ?>'); background-size: cover; background-position: center; cursor: pointer;">
                        <?php if($sin_stock): ?>
                            <div class="label stockout">Sin stock</div>
                        <?php elseif($tiene_descuento): ?>
                            <div class="label sale">-<?php echo round($producto['descuento_porcentaje_producto']); ?>%</div>
                        <?php elseif($es_nuevo): ?>
                            <div class="label new">Nuevo</div>
                        <?php endif; ?>
                        <ul class="product__hover">
                            <li><a href="product-details.php?id=<?php echo $producto['id_producto']; ?>" class="view-details-btn"><span class="icon_search"></span></a></li>
                            <li><a href="#" class="add-to-favorites" data-id="<?php echo $producto['id_producto']; ?>"><span class="icon_heart_alt"></span></a></li>
                            <li><a href="#" class="add-to-cart" data-id="<?php echo $producto['id_producto']; ?>"><span class="icon_bag_alt"></span></a></li>
                        </ul>
                    </div>
                    <div class="product__item__text">
                        <h6><span style="cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>'"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span></h6>
                        <div class="rating">
                            <?php 
                            $calificacion = round($producto['calificacion_promedio']);
                            $total_resenas = $producto['total_resenas'];
                            for($i = 1; $i <= 5; $i++): 
                                if($i <= $calificacion): ?>
                                    <i class="fa fa-star"></i>
                                <?php else: ?>
                                    <i class="fa fa-star-o"></i>
                                <?php endif;
                            endfor; ?>
                            <?php if($total_resenas > 0): ?>
                                <span style="font-size: 11px; color: #999; margin-left: 5px;">(<?php echo $total_resenas; ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="product__price">
                            $<?php echo number_format($precio_final, 2); ?>
                            <?php if($tiene_descuento): ?>
                                <span>$<?php echo number_format($precio_original, 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> No hay productos disponibles en este momento.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!-- Product Section End -->

<!-- Banner Section Begin -->
<section class="banner set-bg" data-setbg="public/assets/img/banner/banner-1.jpg" data-aos="fade-up">
    <div class="container">
        <div class="row">
            <div class="col-xl-7 col-lg-8 m-auto">
                <div class="banner__slider owl-carousel">
                    <div class="banner__item" data-aos="zoom-in">
                        <div class="banner__text">
                            <span>The Chloe Collection</span>
                            <h1>The Project Jacket</h1>
                            <a href="#">Shop now</a>
                        </div>
                    </div>
                    <div class="banner__item">
                        <div class="banner__text">
                            <span>The Chloe Collection</span>
                            <h1>The Project Jacket</h1>
                            <a href="#">Shop now</a>
                        </div>
                    </div>
                    <div class="banner__item">
                        <div class="banner__text">
                            <span>The Chloe Collection</span>
                            <h1>The Project Jacket</h1>
                            <a href="#">Shop now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Banner Section End -->

<!-- Trend Section Begin -->
<section class="trend spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                <div class="trend__content">
                    <div class="section-title">
                        <h4>Hot Trend</h4>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/ht-1.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Chain bucket bag</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/ht-2.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Pendant earrings</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/ht-3.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Cotton T-Shirt</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                <div class="trend__content">
                    <div class="section-title">
                        <h4>Best seller</h4>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/bs-1.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Cotton T-Shirt</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/bs-2.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Zip-pockets pebbled tote <br />briefcase</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/bs-3.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Round leather bag</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                <div class="trend__content">
                    <div class="section-title">
                        <h4>Feature</h4>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/f-1.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Bow wrap skirt</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/f-2.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Metallic earrings</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                    <div class="trend__item">
                        <div class="trend__item__pic">
                            <img src="public/assets/img/trend/f-3.jpg" alt="">
                        </div>
                        <div class="trend__item__text">
                            <h6>Flap cross-body bag</h6>
                            <div class="rating">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <div class="product__price">$ 59.0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Trend Section End -->

<!-- Discount Section Begin -->
<section class="discount">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 p-0" data-aos="fade-right">
                <div class="discount__pic">
                    <img src="public/assets/img/discount.jpg" alt="">
                </div>
            </div>
            <div class="col-lg-6 p-0" data-aos="fade-left">
                <div class="discount__text">
                    <div class="discount__text__title">
                        <span>Discount</span>
                        <h2>Summer 2019</h2>
                        <h5><span>Sale</span> 50%</h5>
                    </div>
                    <div class="discount__countdown" id="countdown-time">
                        <div class="countdown__item">
                            <span>22</span>
                            <p>Days</p>
                        </div>
                        <div class="countdown__item">
                            <span>18</span>
                            <p>Hour</p>
                        </div>
                        <div class="countdown__item">
                            <span>46</span>
                            <p>Min</p>
                        </div>
                        <div class="countdown__item">
                            <span>05</span>
                            <p>Sec</p>
                        </div>
                    </div>
                    <a href="#">Shop now</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Discount Section End -->

<!-- Services Section Begin -->
<section class="services spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="services__item">
                    <i class="fa fa-car"></i>
                    <h6>Free Shipping</h6>
                    <p>For all oder over $99</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="services__item">
                    <i class="fa fa-money"></i>
                    <h6>Money Back Guarantee</h6>
                    <p>If good have Problems</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="services__item">
                    <i class="fa fa-support"></i>
                    <h6>Online Support 24/7</h6>
                    <p>Dedicated support</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="services__item">
                    <i class="fa fa-headphones"></i>
                    <h6>Payment Secure</h6>
                    <p>100% secure payment</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Services Section End -->

<!-- Instagram Begin -->
<div class="instagram">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-1.jpg">
                    <div class="instagram__text">
                        <i class="fa fa-instagram"></i>
                        <a href="#">@ ashion_shop</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-2.jpg">
                    <div class="instagram__text">
                        <i class="fa fa-instagram"></i>
                        <a href="#">@ ashion_shop</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-3.jpg">
                    <div class="instagram__text">
                        <i class="fa fa-instagram"></i>
                        <a href="#">@ ashion_shop</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-4.jpg">
                    <div class="instagram__text">
                        <i class="fa fa-instagram"></i>
                        <a href="#">@ ashion_shop</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-5.jpg">
                    <div class="instagram__text">
                        <i class="fa fa-instagram"></i>
                        <a href="#">@ ashion_shop</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-6.jpg">
                    <div class="instagram__text">
                        <i class="fa fa-instagram"></i>
                        <a href="#">@ ashion_shop</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Instagram End -->

<!-- Footer Section Begin -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-7">
                <div class="footer__about">
                    <div class="footer__logo">
                        <a href="./index.php"><img src="public/assets/img/logo.png" alt=""></a>
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

<?php 
// Incluir modales si el usuario está logueado
if($usuario_logueado) {
    include 'includes/user-account-modal.php'; 
    include 'includes/favorites-modal.php';
}
?>

<!-- Js Plugins -->
<script src="public/assets/js/jquery-3.3.1.min.js"></script>

<!-- Fetch API Handler Moderno - Reemplaza AJAX/jQuery para llamadas al servidor -->
<script src="public/assets/js/fetch-api-handler.js"></script>

<script src="public/assets/js/error-handler.js"></script>
<script src="public/assets/js/bootstrap.min.js"></script>
<script src="public/assets/js/jquery.magnific-popup.min.js"></script>
<script src="public/assets/js/jquery-ui.min.js"></script>
<script src="public/assets/js/mixitup.min.js"></script>
<script src="public/assets/js/jquery.countdown.min.js"></script>
<script src="public/assets/js/jquery.slicknav.js"></script>
<script src="public/assets/js/owl.carousel.min.js"></script>
<script src="public/assets/js/jquery.nicescroll.min.js"></script>
<script src="public/assets/js/main.js"></script>

<!-- User Account Modal Script -->
<script src="public/assets/js/user-account-modal.js"></script>

<!-- Cart & Favorites Handler -->
<script src="public/assets/js/cart-favorites-handler.js"></script>

<!-- Scroll Position Memory -->
<script src="public/assets/js/scroll-position-memory.js"></script>

<script>
// Header sticky effect
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
</script>

<!-- Fashion Store Chat Widget - Sistema Optimizado -->
<script>
// Cargar el chat widget del bot de moda
(function() {
    // Verificar que el script no esté ya cargado
    if (window.FashionStoreChatWidget) {
        return;
    }
    
    // Crear y cargar el script del chat widget
    const script = document.createElement('script');
    script.src = 'proyecto-bot-main/src/fashion-chat-widget.js';
    script.onload = function() {
        // Inicializar el widget automáticamente
        if (window.FashionStoreChatWidget) {
            try {
                window.fashionChat = new FashionStoreChatWidget();
            } catch (error) {
            }
        }
    };
    script.onerror = function() {
    };
    
    // Agregar el script al head para mejor compatibilidad
    document.head.appendChild(script);
})();
</script>
</body>

</html>
