<?php
/**
 * PÁGINA PRINCIPAL - SLEPPY STORE
 * Tienda de moda online con productos dinámicos
 */

session_start();
require_once 'config/conexion.php';

require_once 'config/config.php'; // <-- Para BASE_URL global

$page_title = "Inicio - SleppyStore";

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

// Obtener contadores
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
        error_log("Error al obtener contadores: " . $e->getMessage());
    }
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY nombre_categoria ASC");
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

// Obtener productos destacados (últimos 8 productos)
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
        WHERE p.status_producto = 1 
          AND p.estado = 'activo' 
          AND p.stock_actual_producto > 0
        GROUP BY p.id_producto
        ORDER BY p.id_producto DESC
        LIMIT 8
    ");
    $productos_destacados = $productos_resultado ? $productos_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener productos: " . $e->getMessage());
}

// Obtener productos en oferta
$productos_oferta = [];
try {
    $ofertas_resultado = executeQuery("
        SELECT p.id_producto, p.nombre_producto, p.precio_producto,
               p.descuento_porcentaje_producto,
               p.url_imagen_producto,
               COALESCE(c.nombre_categoria, 'General') as nombre_categoria
        FROM producto p
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
        WHERE p.status_producto = 1 
        AND p.estado = 'activo'
        AND p.stock_actual_producto > 0
        AND p.en_oferta_producto = 1
        AND p.descuento_porcentaje_producto > 0
        ORDER BY p.descuento_porcentaje_producto DESC
        LIMIT 6
    ");
    $productos_oferta = $ofertas_resultado ? $ofertas_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener ofertas: " . $e->getMessage());
}

// Obtener estadísticas de la tienda
$stats = [
    'total_productos' => 0,
    'total_categorias' => 0,
    'clientes_activos' => 0
];
try {
    $stats_result = executeQuery("
        SELECT 
            (SELECT COUNT(*) FROM producto WHERE status_producto = 1 AND estado = 'activo' AND stock_actual_producto > 0) as total_productos,
            (SELECT COUNT(*) FROM categoria WHERE status_categoria = 1) as total_categorias,
            (SELECT COUNT(*) FROM usuario WHERE status_usuario = 1 AND rol_usuario = 'cliente') as clientes_activos
    ");
    if($stats_result && count($stats_result) > 0) {
        $stats = $stats_result[0];
    }
} catch(Exception $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="SleppyStore - Tu tienda de moda online">
    <meta name="keywords" content="moda, ropa, fashion, tienda online, SleppyStore">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $page_title; ?></title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Solo modals.css y dark-mode reales -->
    <link rel="stylesheet" href="public/assets/css/components/modals.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/dark-mode/dark-mode.css" type="text/css">
    
    <style>
        /* Hero Section Moderno */
        .hero-section {
            background: #1a1a1a;
            padding: 100px 0;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect fill="%23ffffff" fill-opacity="0.05" width="50" height="50"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .hero-stats {
            display: flex;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .btn-hero {
            background: white;
            color: #667eea;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: #667eea;
            text-decoration: none;
        }
        
        /* Categories Section */
        .categories-section {
            padding: 80px 0;
        }
        
        .category-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 300px;
            position: relative;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .category-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
            display: flex;
            align-items: flex-end;
            padding: 30px;
        }
        
        .category-name {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        
        /* Products Grid */
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ca1515;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .section-title-modern {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title-modern h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
            color: #333;
        }
        
        .section-title-modern p {
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'includes/offcanvas-menu.php'; ?>
    <?php include 'includes/header-section.php'; ?>
    

    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <h1 class="hero-title">Tu Estilo,<br>Tu Identidad</h1>
                        <p class="hero-subtitle">Descubre las últimas tendencias en moda y encuentra tu look perfecto en SleppyStore</p>
                        <a href="shop.php" class="btn-hero">Explorar Colección</a>
                        
                        <div class="hero-stats">
                            <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                                <span class="stat-number"><?php echo number_format($stats['total_productos']); ?>+</span>
                                <span class="stat-label">Productos</span>
                            </div>
                            <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                                <span class="stat-number"><?php echo number_format($stats['total_categorias']); ?>+</span>
                                <span class="stat-label">Categorías</span>
                            </div>
                            <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                                <span class="stat-number"><?php echo number_format($stats['clientes_activos']); ?>+</span>
                                <span class="stat-label">Clientes Felices</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600" alt="Hero Fashion" style="max-width: 100%; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <?php if(!empty($categorias) && count($categorias) > 0): ?>
    <section class="categories-section">
        <div class="container">
            <div class="section-title-modern" data-aos="fade-up">
                <h2>Explora por Categorías</h2>
                <p>Encuentra exactamente lo que buscas</p>
            </div>
            
            <div class="row">
                <?php 
                $category_images = [
                    'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400',
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=400',
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400',
                    'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400',
                    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=400',
                    'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=400'
                ];
                $displayed = 0;
                foreach(array_slice($categorias, 0, 6) as $index => $categoria): 
                    $delay = ($index + 1) * 100;
                ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <a href="shop.php?categoria=<?php echo $categoria['id_categoria']; ?>" style="text-decoration: none;">
                        <div class="category-card">
                            <img src="<?php echo $category_images[$index % count($category_images)]; ?>" alt="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>">
                            <div class="category-overlay">
                                <h3 class="category-name"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></h3>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Products Section -->
    <?php if(!empty($productos_destacados)): ?>
    <section class="categories-section" style="background: #f8f9fa;">
        <div class="container">
            <div class="section-title-modern" data-aos="fade-up">
                <h2>Productos Destacados</h2>
                <p>Lo más nuevo de nuestra colección</p>
            </div>
            
            <div class="row">
                <?php foreach($productos_destacados as $index => $producto): 
                    $precio_original = $producto['precio_producto'];
                    $tiene_descuento = $producto['descuento_porcentaje_producto'] > 0;
                    $precio_final = $tiene_descuento ? $precio_original * (1 - $producto['descuento_porcentaje_producto'] / 100) : $precio_original;
                    $delay = ($index + 1) * 100;
                ?>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                    <a href="product-details.php?id=<?php echo $producto['id_producto']; ?>" style="text-decoration: none;">
                        <div class="product-card" style="position: relative;">
                            <?php if($tiene_descuento): ?>
                            <div class="product-badge">-<?php echo round($producto['descuento_porcentaje_producto']); ?>%</div>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($producto['url_imagen_producto']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" 
                                 class="product-image"
                                 crossorigin="anonymous">
                            
                            <div class="product-info">
                                <?php if($producto['total_resenas'] > 0): ?>
                                <div class="product-rating">
                                    <?php 
                                    $rating = round($producto['calificacion_promedio']);
                                    for($i = 0; $i < 5; $i++): 
                                    ?>
                                        <i class="fa fa-star" style="color: <?php echo $i < $rating ? '#ffc107' : '#ddd'; ?>; font-size: 12px;"></i>
                                    <?php endfor; ?>
                                    <span style="font-size: 12px; color: #999;">(<?php echo $producto['total_resenas']; ?>)</span>
                                </div>
                                <?php endif; ?>
                                
                                <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h3>
                                
                                <div class="product-price">
                                    <?php if($tiene_descuento): ?>
                                        <span style="text-decoration: line-through; font-size: 16px; color: #999; margin-right: 10px;">
                                            S/. <?php echo number_format($precio_original, 2); ?>
                                        </span>
                                    <?php endif; ?>
                                    S/. <?php echo number_format($precio_final, 2); ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="shop.php" class="btn-hero">Ver Todos los Productos</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Offers Section -->
    <?php if(!empty($productos_oferta)): ?>
    <section class="categories-section">
        <div class="container">
            <div class="section-title-modern" data-aos="fade-up">
                <h2>🔥 Ofertas Especiales</h2>
                <p>Aprovecha los mejores descuentos</p>
            </div>
            
            <div class="row">
                <?php foreach($productos_oferta as $index => $producto): 
                    $precio_original = $producto['precio_producto'];
                    $precio_final = $precio_original * (1 - $producto['descuento_porcentaje_producto'] / 100);
                    $delay = ($index + 1) * 100;
                ?>
                <div class="col-lg-4 col-md-6" data-aos="flip-left" data-aos-delay="<?php echo $delay; ?>">
                    <a href="product-details.php?id=<?php echo $producto['id_producto']; ?>" style="text-decoration: none;">
                        <div class="product-card" style="position: relative;">
                            <div class="product-badge">-<?php echo round($producto['descuento_porcentaje_producto']); ?>%</div>
                            
                            <img src="<?php echo htmlspecialchars($producto['url_imagen_producto']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" 
                                 class="product-image"
                                 crossorigin="anonymous">
                            
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h3>
                                
                                <div class="product-price">
                                    <span style="text-decoration: line-through; font-size: 16px; color: #999; margin-right: 10px;">
                                        S/. <?php echo number_format($precio_original, 2); ?>
                                    </span>
                                    S/. <?php echo number_format($precio_final, 2); ?>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer style="background: #1a1a1a; color: white; padding: 60px 0 20px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h3 style="margin-bottom: 20px;">SleppyStore</h3>
                    <p style="color: #aaa;">Tu tienda de moda online de confianza. Calidad, estilo y las últimas tendencias al mejor precio.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 style="margin-bottom: 20px;">Enlaces</h5>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;"><a href="shop.php" style="color: #aaa; text-decoration: none;">Tienda</a></li>
                        <li style="margin-bottom: 10px;"><a href="contact.php" style="color: #aaa; text-decoration: none;">Contacto</a></li>
                        <li style="margin-bottom: 10px;"><a href="#" style="color: #aaa; text-decoration: none;">Nosotros</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 style="margin-bottom: 20px;">Categorías</h5>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach(array_slice($categorias, 0, 4) as $cat): ?>
                        <li style="margin-bottom: 10px;">
                            <a href="shop.php?categoria=<?php echo $cat['id_categoria']; ?>" style="color: #aaa; text-decoration: none;">
                                <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 style="margin-bottom: 20px;">Contacto</h5>
                    <p style="color: #aaa;">Email: info@sleppystore.com</p>
                    <p style="color: #aaa;">Teléfono: +51 999 999 999</p>
                    <div style="margin-top: 20px;">
                        <a href="#" style="color: white; font-size: 20px; margin-right: 15px;"><i class="fa fa-facebook"></i></a>
                        <a href="#" style="color: white; font-size: 20px; margin-right: 15px;"><i class="fa fa-instagram"></i></a>
                        <a href="#" style="color: white; font-size: 20px;"><i class="fa fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mt-4 pt-4" style="border-top: 1px solid #333;">
                <div class="col-12 text-center">
                    <p style="color: #aaa; margin: 0;">© <script>document.write(new Date().getFullYear());</script> SleppyStore. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Js Plugins -->
    <script src="public/assets/js/header-globals/jquery-3.3.1.min.js"></script>
    <script src="public/assets/js/header-globals/bootstrap.min.js"></script>
    <script src="public/assets/js/header-globals/real-time-updates.js"></script>
    <script src="public/assets/js/header-globals/swipe-gestures.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
    
    <!-- Modales ya incluidos en header-section.php - NO duplicar -->
        
        <!-- Scripts para modales -->

    <!-- Otros scripts opcionales -->
    <?php if($usuario_logueado): ?>
    <?php endif; ?>
    
    <?php include 'includes/dark-mode-assets.php'; ?>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>
</html>
