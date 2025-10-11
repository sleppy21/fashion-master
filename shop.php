<?php
/**
 * PÁGINA DE TIENDA - SHOP
 * Catálogo completo de productos con filtros
 */

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar archivo de configuración
if (!file_exists('config/conexion.php')) {
    die('Error: Archivo de configuración no encontrado.');
}

require_once 'config/conexion.php';

$page_title = "Tienda";

// Verificar usuario logueado
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
    } catch(Exception $e) {
        error_log("Error al obtener favoritos: " . $e->getMessage());
    }
}

// FILTROS DE BÚSQUEDA (soportar URLs cortas y largas)
$filtro_categoria = isset($_GET['c']) ? intval($_GET['c']) : (isset($_GET['categoria']) ? intval($_GET['categoria']) : null);
$filtro_genero = isset($_GET['g']) ? $_GET['g'] : (isset($_GET['genero']) ? $_GET['genero'] : null);
$filtro_marca = isset($_GET['m']) ? intval($_GET['m']) : (isset($_GET['marca']) ? intval($_GET['marca']) : null);
$filtro_precio_min = isset($_GET['pmin']) ? floatval($_GET['pmin']) : (isset($_GET['precio_min']) ? floatval($_GET['precio_min']) : 0);
$filtro_precio_max = isset($_GET['pmax']) ? floatval($_GET['pmax']) : (isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 10000);
$filtro_buscar = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['buscar']) ? trim($_GET['buscar']) : '');

// Construir query con filtros
$query = "
    SELECT p.id_producto, p.nombre_producto, p.precio_producto,
           p.codigo, p.descripcion_producto,
           COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
           p.genero_producto, p.en_oferta_producto, p.stock_actual_producto,
           p.url_imagen_producto,
           COALESCE(m.nombre_marca, 'Sin marca') as nombre_marca, 
           COALESCE(c.nombre_categoria, 'General') as nombre_categoria,
           c.id_categoria, m.id_marca,
           COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
           COUNT(r.id_resena) as total_resenas
    FROM producto p
    LEFT JOIN marca m ON p.id_marca = m.id_marca
    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
    LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
    WHERE p.status_producto = 1 AND p.estado = 'activo'
";

$params = [];

// Aplicar filtros
if($filtro_categoria) {
    $query .= " AND p.id_categoria = ?";
    $params[] = $filtro_categoria;
}

if($filtro_genero && $filtro_genero !== 'all') {
    $query .= " AND p.genero_producto = ?";
    $params[] = $filtro_genero;
}

if($filtro_marca) {
    $query .= " AND p.id_marca = ?";
    $params[] = $filtro_marca;
}

if($filtro_precio_min > 0 || $filtro_precio_max < 10000) {
    $query .= " AND p.precio_producto BETWEEN ? AND ?";
    $params[] = $filtro_precio_min;
    $params[] = $filtro_precio_max;
}

if($filtro_buscar) {
    $query .= " AND (p.nombre_producto LIKE ? OR p.descripcion_producto LIKE ? OR m.nombre_marca LIKE ?)";
    $buscar_param = '%' . $filtro_buscar . '%';
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $params[] = $buscar_param;
}

$query .= " GROUP BY p.id_producto, p.nombre_producto, p.precio_producto, p.codigo, 
            p.descripcion_producto, p.descuento_porcentaje_producto, p.genero_producto, 
            p.en_oferta_producto, p.stock_actual_producto, p.url_imagen_producto,
            m.nombre_marca, c.nombre_categoria, c.id_categoria, m.id_marca";
$query .= " ORDER BY p.id_producto DESC";

// Ejecutar consulta
$productos = [];
try {
    $productos_resultado = executeQuery($query, $params);
    $productos = $productos_resultado ? $productos_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener productos: " . $e->getMessage());
    $productos = [];
}

// Obtener todas las categorías para el filtro
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY nombre_categoria ASC");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
}

// Obtener todas las marcas para el filtro
$marcas = [];
try {
    $marcas_resultado = executeQuery("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
    $marcas = $marcas_resultado ? $marcas_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener marcas: " . $e->getMessage());
}

// Obtener IDs de productos en favoritos
$favoritos_ids = [];
if($usuario_logueado) {
    foreach($favoritos_usuario as $fav) {
        $favoritos_ids[] = $fav['id_producto'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="SleppyStore - Tienda de moda online">
    <meta name="keywords" content="tienda, moda, ropa, zapatos, accesorios">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tienda - SleppyStore</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    
    <!-- Modals CSS -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Header Responsive Global CSS -->
    <link rel="stylesheet" href="public/assets/css/header-responsive.css?v=2.0" type="text/css">
    
    <!-- Header Override - Máxima prioridad -->
    <link rel="stylesheet" href="public/assets/css/header-override.css?v=2.0" type="text/css">
    
    <style>
        /* ============================================
           ESTILOS ESPECÍFICOS DE SHOP
           ============================================ */
    </style>
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
                        <span>Tienda</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Botón de filtros (solo visible en móvil) -->
    <button class="btn-mobile-filters" id="btnMobileFilters">
        <i class="fa fa-filter"></i>
        <span class="filter-count" id="filterCount">0</span>
    </button>

    <!-- Modal de Filtros para Móvil -->
    <div class="filters-modal-overlay" id="filtersModalOverlay"></div>
    <div class="filters-modal" id="filtersModal">
        <div class="filters-modal-header">
            <h4><i class="fa fa-filter"></i> Filtros</h4>
            <button class="filters-modal-close" id="closeFiltersModal">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="filters-modal-body" id="filtersModalContent">
            <!-- Los filtros se clonarán aquí dinámicamente -->
        </div>
        <div class="filters-modal-footer">
            <button class="btn-clear-filters-modal" onclick="limpiarFiltros()">
                <i class="fa fa-refresh"></i> Limpiar
            </button>
            <button class="btn-apply-filters-modal" id="applyFiltersModal">
                <i class="fa fa-check"></i> Aplicar
            </button>
        </div>
    </div>

    <!-- Shop Section Begin -->
    <section class="shop spad">
        <div class="container">
            <div class="row">
                <!-- SIDEBAR CON FILTROS -->
                <div class="col-lg-3 col-md-3">
                    <div class="shop__sidebar">
                        <!-- Botón limpiar filtros ARRIBA -->
                        <div class="mb-3">
                            <button class="btn-clear-filters w-100" onclick="limpiarFiltros()">
                                <i class="fa fa-refresh"></i> 
                                <span>Restablecer filtros</span>
                            </button>
                        </div>
                        
                        <!-- Filtro por Categorías -->
                        <div class="sidebar__categories mb-4">
                            <div class="section-title">
                                <h4>Categorías</h4>
                            </div>
                            <div class="filter-buttons-group" style="display: flex; flex-direction: column; gap: 8px;">
                                <button class="filter-btn <?php echo !$filtro_categoria ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('categoria', null)"
                                        data-filter-type="categoria"
                                        data-filter-value="">
                                    <i class="fa fa-th"></i> Todas
                                </button>
                                <?php foreach($categorias as $categoria): 
                                    $is_active = $filtro_categoria == $categoria['id_categoria'];
                                ?>
                                <button class="filter-btn <?php echo $is_active ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('categoria', <?php echo $categoria['id_categoria']; ?>)"
                                        data-filter-type="categoria"
                                        data-filter-value="<?php echo $categoria['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Filtro por Género -->
                        <div class="sidebar__filter mb-4">
                            <div class="section-title">
                                <h4>Género</h4>
                            </div>
                            <div class="filter-buttons-group">
                                <button class="filter-btn <?php echo !$filtro_genero || $filtro_genero == 'all' ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('genero', null)"
                                        data-filter-type="genero"
                                        data-filter-value="">
                                    <i class="fa fa-users"></i> Todos
                                </button>
                                <button class="filter-btn <?php echo $filtro_genero == 'F' ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('genero', 'F')"
                                        data-filter-type="genero"
                                        data-filter-value="F">
                                    <i class="fa fa-female"></i> Mujer
                                </button>
                                <button class="filter-btn <?php echo $filtro_genero == 'M' ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('genero', 'M')"
                                        data-filter-type="genero"
                                        data-filter-value="M">
                                    <i class="fa fa-male"></i> Hombre
                                </button>
                                <button class="filter-btn <?php echo $filtro_genero == 'Kids' ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('genero', 'Kids')"
                                        data-filter-type="genero"
                                        data-filter-value="Kids">
                                    <i class="fa fa-child"></i> Kids
                                </button>
                            </div>
                        </div>

                        <!-- Filtro por Marca -->
                        <div class="sidebar__categories mb-4">
                            <div class="section-title">
                                <h4>Marcas</h4>
                            </div>
                            <div class="filter-buttons-group">
                                <button class="filter-btn <?php echo !$filtro_marca ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('marca', null)"
                                        data-filter-type="marca"
                                        data-filter-value="">
                                    <i class="fa fa-star"></i> Todas
                                </button>
                                <?php foreach($marcas as $marca): ?>
                                <button class="filter-btn <?php echo $filtro_marca == $marca['id_marca'] ? 'active' : ''; ?>" 
                                        onclick="aplicarFiltro('marca', <?php echo $marca['id_marca']; ?>)"
                                        data-filter-type="marca"
                                        data-filter-value="<?php echo $marca['id_marca']; ?>">
                                    <?php if(!empty($marca['url_imagen_marca']) && $marca['url_imagen_marca'] != '/fashion-master/public/assets/img/default-product.jpg'): ?>
                                        <img src="<?php echo htmlspecialchars($marca['url_imagen_marca']); ?>" 
                                             alt="<?php echo htmlspecialchars($marca['nombre_marca']); ?>" 
                                             class="marca-icon">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($marca['nombre_marca']); ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Filtro por Precio -->
                        <div class="sidebar__filter mb-4">
                            <div class="section-title">
                                <h4>Rango de Precio</h4>
                            </div>
                            <div class="filter-range-wrap">
                                <div class="price-range ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content"
                                data-min="0" data-max="500" id="price-slider"></div>
                                <div class="range-slider mt-3">
                                    <div class="price-input">
                                        <p style="margin-bottom: 8px; font-weight: 600;">Precio seleccionado:</p>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <input type="text" id="minamount" value="$<?php echo $filtro_precio_min; ?>" readonly 
                                                   style="width: 80px; text-align: center; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                            <span>-</span>
                                            <input type="text" id="maxamount" value="$<?php echo $filtro_precio_max; ?>" readonly 
                                                   style="width: 80px; text-align: center; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUCTOS -->
                <div class="col-lg-9 col-md-9">
                    <!-- Barra de herramientas -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="shop__product__option__left">
                                <p><strong><?php echo count($productos); ?></strong> productos encontrados</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="shop__product__option__right">
                                <div class="search-box-wrapper">
                                    <input type="text" 
                                           id="search-input-shop" 
                                           name="buscar" 
                                           placeholder="Buscar productos..." 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($filtro_buscar); ?>">
                                    <i class="fa fa-search search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid de Productos con Masonry Layout -->
                    <div class="row productos-grid" id="productosGrid">
                        <?php if(!empty($productos)): ?>
                            <?php foreach($productos as $producto): 
                                // Calcular precio con descuento
                                $precio_original = $producto['precio_producto'];
                                $tiene_descuento = $producto['descuento_porcentaje_producto'] > 0;
                                $precio_final = $precio_original;
                                if($tiene_descuento) {
                                    $precio_final = $precio_original - ($precio_original * $producto['descuento_porcentaje_producto'] / 100);
                                }
                                
                                // Imagen del producto - Usar versión _shop si existe
                                $imagen_url = !empty($producto['url_imagen_producto']) ? $producto['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                                
                                // Intentar usar versión _shop (con fondo difuminado)
                                if (!empty($producto['imagen_producto']) && strpos($producto['imagen_producto'], 'product_') === 0) {
                                    $shop_version = str_replace('.', '_shop.', $producto['imagen_producto']);
                                    $shop_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $shop_version;
                                    if (file_exists($shop_path)) {
                                        $imagen_url = '/fashion-master/public/assets/img/products/' . $shop_version;
                                    }
                                }
                                
                                // Stock
                                $sin_stock = $producto['stock_actual_producto'] <= 0;
                                
                                // Verificar si está en favoritos
                                $es_favorito = in_array($producto['id_producto'], $favoritos_ids);
                            ?>
                        <div class="grid-item col-lg-4 col-md-6 col-6">
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
                                    <?php endif; ?>
                                    <ul class="product__hover">
                                        <li><a href="product-details.php?id=<?php echo $producto['id_producto']; ?>" class="view-details-btn"><span class="icon_search"></span></a></li>
                                        <li>
                                            <a href="#" class="add-to-favorites <?php echo $es_favorito ? 'active' : ''; ?>" 
                                               data-id="<?php echo $producto['id_producto']; ?>">
                                               <span class="icon_heart<?php echo $es_favorito ? '' : '_alt'; ?>"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="add-to-cart" data-id="<?php echo $producto['id_producto']; ?>" 
                                               <?php echo $sin_stock ? 'style="opacity:0.5;" data-disabled="true"' : ''; ?>>
                                               <span class="icon_bag_alt"></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product__item__text">
                                    <h6><span style="cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>'">
                                        <?php echo htmlspecialchars($producto['nombre_producto']); ?>
                                    </span></h6>
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
                                    <i class="fa fa-info-circle"></i> No se encontraron productos con los filtros seleccionados.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Paginación (por implementar si se necesita) -->
                    <!-- <div class="col-lg-12 text-center">
                        <div class="pagination__option">
                            <a href="#">1</a>
                            <a href="#">2</a>
                            <a href="#">3</a>
                            <a href="#"><i class="fa fa-angle-right"></i></a>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </section>
    <!-- Shop Section End -->

    <!-- Instagram Begin -->
    <div class="instagram">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-1.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppystore</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-2.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppystore</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-3.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppystore</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-4.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppystore</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-5.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppystore</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-6.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppystore</a>
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
                        <p>Tu tienda de moda online de confianza. Encuentra los mejores productos al mejor precio.</p>
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
                        <h6>Enlaces rápidos</h6>
                        <ul>
                            <li><a href="#">Sobre nosotros</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Contacto</a></li>
                            <li><a href="#">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <div class="footer__widget">
                        <h6>Mi cuenta</h6>
                        <ul>
                            <li><a href="#">Mi cuenta</a></li>
                            <li><a href="#">Mis pedidos</a></li>
                            <li><a href="cart.php">Carrito</a></li>
                            <li><a href="#">Favoritos</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-8 col-sm-8">
                    <div class="footer__newslatter">
                        <h6>NEWSLETTER</h6>
                        <form action="#">
                            <input type="text" placeholder="Email">
                            <button type="submit" class="site-btn">Suscribirse</button>
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
                    <div class="footer__copyright__text">
                        <p>Copyright &copy; <script>document.write(new Date().getFullYear());</script> SleppyStore. Todos los derechos reservados.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->

    <!-- Search Begin -->
    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch">+</div>
            <form class="search-model-form" method="GET" action="shop.php">
                <input type="text" id="search-input" name="buscar" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($filtro_buscar); ?>">
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
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery.magnific-popup.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/mixitup.min.js"></script>
    <script src="public/assets/js/jquery.countdown.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/owl.carousel.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.min.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- Masonry Layout para efecto cascada en productos -->
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

    <?php if($usuario_logueado): ?>
    <!-- Scripts para carrito y favoritos -->
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/user-account-modal.js"></script>
    <?php endif; ?>
    
    <!-- Product Navigation ya no es necesario - usando onclick directo -->
    <!-- <script src="public/assets/js/product-navigation.js"></script> -->
    
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

    <script>
    // Variables globales para manejar filtros
    let filtrosActuales = {
        categoria: <?php echo $filtro_categoria ?: 'null'; ?>,
        genero: <?php echo $filtro_genero ? "'" . $filtro_genero . "'" : 'null'; ?>,
        marca: <?php echo $filtro_marca ?: 'null'; ?>,
        precio_min: <?php echo $filtro_precio_min; ?>,
        precio_max: <?php echo $filtro_precio_max; ?>,
        buscar: '<?php echo addslashes($filtro_buscar); ?>'
    };

    // Función unificada para aplicar filtros
    function aplicarFiltro(tipo, valor) {
        // Actualizar el filtro específico
        if(valor === null || valor === '' || valor === 'all') {
            filtrosActuales[tipo] = null;
        } else {
            filtrosActuales[tipo] = valor;
        }
        
        // Actualizar estado visual de los botones
        actualizarBotonesActivos(tipo, valor);
        
        // Aplicar filtros vía AJAX (sin refresh)
        aplicarFiltrosAjax();
    }

    // Función para actualizar el estado visual de los botones
    function actualizarBotonesActivos(tipo, valor) {
        // Encontrar todos los botones de este tipo de filtro
        const botones = document.querySelectorAll('[data-filter-type="' + tipo + '"]');
        
        botones.forEach(function(boton) {
            const btnValue = boton.getAttribute('data-filter-value');
            
            // Remover clase active de todos
            boton.classList.remove('active');
            
            // Agregar active al botón seleccionado
            if ((valor === null || valor === '' || valor === 'all') && btnValue === '') {
                boton.classList.add('active');
            } else if (btnValue == valor) {
                boton.classList.add('active');
            }
        });
    }

    // Función para aplicar filtros vía AJAX
    function aplicarFiltrosAjax() {
        let params = new URLSearchParams();
        
        // Agregar cada filtro si tiene valor
        if(filtrosActuales.categoria) params.set('c', filtrosActuales.categoria);
        if(filtrosActuales.genero) params.set('g', filtrosActuales.genero);
        if(filtrosActuales.marca) params.set('m', filtrosActuales.marca);
        if(filtrosActuales.precio_min > 0 || filtrosActuales.precio_max < 500) {
            params.set('pmin', filtrosActuales.precio_min);
            params.set('pmax', filtrosActuales.precio_max);
        }
        if(filtrosActuales.buscar) params.set('q', filtrosActuales.buscar);
        
        // Actualizar URL sin recargar
        const newUrl = params.toString() ? '?' + params.toString() : window.location.pathname;
        history.pushState({}, '', newUrl);
        
        // Mostrar indicador de carga
        const productsContainer = document.querySelector('.shop__product__option__right').closest('.row').nextElementSibling;
        if (productsContainer) {
            productsContainer.innerHTML = '<div class="col-12 text-center" style="padding: 60px 20px;"><i class="fa fa-spinner fa-spin" style="font-size: 48px; color: #ca1515;"></i><p style="margin-top: 20px; color: #666;">Cargando productos...</p></div>';
        }
        
        // Hacer petición AJAX
        fetch('app/actions/get_products_filtered.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    productsContainer.innerHTML = data.html;
                    
                    // Re-inicializar botones de carrito y favoritos
                    if (typeof initCartButtons === 'function') initCartButtons();
                    if (typeof initFavoriteButtons === 'function') initFavoriteButtons();
                    
                    // Trigger evento para reinicializar Masonry
                    $(document).trigger('productosActualizados');
                    
                    console.log('✅ Products filtered:', data.count);
                } else {
                    console.error('Error:', data.message);
                    productsContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar productos</div></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productsContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error de conexión</div></div>';
            });
    }

    // Función para limpiar todos los filtros
    function limpiarFiltros() {
        // Resetear todos los filtros
        filtrosActuales = {
            categoria: null,
            genero: null,
            marca: null,
            precio_min: 0,
            precio_max: 500,
            buscar: ''
        };
        
        // Limpiar input de búsqueda
        document.getElementById('search-input-shop').value = '';
        
        // Resetear todos los botones a su estado inicial
        document.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Activar solo los botones "Todas/Todos"
        document.querySelectorAll('[data-filter-value=""]').forEach(function(btn) {
            btn.classList.add('active');
        });
        
        // Resetear slider de precio (con verificación robusta de inicialización)
        try {
            const priceSlider = $('#price-slider');
            if (priceSlider.length > 0) {
                // Verificar si el slider está inicializado
                if (typeof priceSlider.slider === 'function') {
                    try {
                        // Verificar si ya tiene el widget inicializado
                        if (priceSlider.data('ui-slider')) {
                            priceSlider.slider('values', [0, 500]);
                            $('#minamount').val('$0');
                            $('#maxamount').val('$500');
                        } else {
                            // Si no está inicializado, solo resetear los valores visuales
                            $('#minamount').val('$0');
                            $('#maxamount').val('$500');
                        }
                    } catch(e) {
                        console.log('Error al resetear slider:', e);
                        // Solo resetear los valores visuales
                        $('#minamount').val('$0');
                        $('#maxamount').val('$500');
                    }
                }
            }
        } catch(e) {
            console.log('Error en limpiarFiltros slider:', e);
        }
        
        // Actualizar URL
        history.pushState({}, '', window.location.pathname);
        
        // Recargar productos sin filtros
        aplicarFiltrosAjax();
    }

    // Búsqueda en tiempo real (con debounce)
    let searchTimeout;
    $(document).ready(function() {
        $('#search-input-shop').on('input', function() {
            clearTimeout(searchTimeout);
            const searchValue = $(this).val();
            
            searchTimeout = setTimeout(function() {
                filtrosActuales.buscar = searchValue;
                aplicarFiltrosAjax(); // Usar AJAX en lugar de redirect
            }, 800); // Espera 800ms después de que el usuario deje de escribir
        });

        // Inicializar slider de precio con valores del servidor
        const minPrice = <?php echo $filtro_precio_min; ?>;
        const maxPrice = <?php echo $filtro_precio_max; ?>;
        
        $('#price-slider').slider({
            range: true,
            min: 0,
            max: 500,
            values: [minPrice, maxPrice],
            slide: function(event, ui) {
                $('#minamount').val('$' + ui.values[0]);
                $('#maxamount').val('$' + ui.values[1]);
            },
            stop: function(event, ui) {
                // Aplicar filtro vía AJAX cuando se suelta el slider
                filtrosActuales.precio_min = ui.values[0];
                filtrosActuales.precio_max = ui.values[1];
                aplicarFiltrosAjax(); // Usar AJAX en lugar de redirect
            }
        });
        
        // Establecer valores iniciales
        $('#minamount').val('$' + $('#price-slider').slider('values', 0));
        $('#maxamount').val('$' + $('#price-slider').slider('values', 1));
    });

    // ============================================
    // FUNCIONES PARA FILTROS EN MÓVIL
    // ============================================

    function toggleMobileFilters() {
        const sidebar = document.querySelector('.col-lg-3.col-md-3');
        const overlay = document.getElementById('filtersOverlay');
        
        if (sidebar.classList.contains('show-filters')) {
            closeMobileFilters();
        } else {
            sidebar.classList.add('show-filters');
            if (!overlay) {
                const newOverlay = document.createElement('div');
                newOverlay.id = 'filtersOverlay';
                newOverlay.className = 'filters-overlay active';
                newOverlay.onclick = closeMobileFilters;
                document.body.appendChild(newOverlay);
            } else {
                overlay.classList.add('active');
            }
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMobileFilters() {
        const sidebar = document.querySelector('.col-lg-3.col-md-3');
        const overlay = document.getElementById('filtersOverlay');
        
        sidebar.classList.remove('show-filters');
        if (overlay) {
            overlay.classList.remove('active');
        }
        // Restaurar scroll del body
        document.body.style.overflow = '';
    }

    // Cerrar filtros al hacer clic en el pseudo-elemento ::before
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.col-lg-3.col-md-3.show-filters');
        if (sidebar) {
            const sidebarInner = sidebar.querySelector('.shop__sidebar');
            const rect = sidebarInner.getBoundingClientRect();
            
            // Si el clic es en el área del ::before (arriba del contenido)
            if (e.clientY < rect.top) {
                closeMobileFilters();
            }
        }
    });

    // Actualizar contador de filtros activos
    function updateFilterCount() {
        let activeCount = 0;
        
        if (filtrosActuales.categoria) activeCount++;
        if (filtrosActuales.genero && filtrosActuales.genero !== 'all') activeCount++;
        if (filtrosActuales.marca) activeCount++;
        if (filtrosActuales.precio_min > 0 || filtrosActuales.precio_max < 500) activeCount++;
        if (filtrosActuales.buscar) activeCount++;
        
        const countBadge = document.getElementById('filterCount');
        if (countBadge) {
            if (activeCount > 0) {
                countBadge.textContent = activeCount;
                countBadge.classList.add('active');
            } else {
                countBadge.classList.remove('active');
            }
        }
    }

    // Llamar a updateFilterCount cuando se apliquen filtros
    const originalAplicarFiltro = window.aplicarFiltro;
    if (originalAplicarFiltro) {
        window.aplicarFiltro = function(tipo, valor) {
            originalAplicarFiltro(tipo, valor);
            updateFilterCount();
        };
    }

    // Actualizar contador al cargar la página
    $(document).ready(function() {
        updateFilterCount();
    });
    </script>

    <style>
    /* Estilos para botones de filtro */
    .filter-buttons-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* Botón limpiar filtros - diseño moderno */
    .btn-clear-filters {
        width: 100%;
        padding: 12px 20px;
        border: 2px dashed #e0e0e0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        color: #666;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .btn-clear-filters:hover {
        transform: translateY(-2px);
    }

    .btn-clear-filters i {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .btn-clear-filters:hover i {
        transform: rotate(180deg);
    }

    .filter-btn {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        background: white;
        color: #333;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-btn:hover {
        background: #f8f9fa;
        border-color: #2c3e50;
        color: #2c3e50;
        transform: translateX(3px);
    }

    .filter-btn.active {
        background: #2c3e50;
        color: white;
        border-color: #2c3e50;
        font-weight: 600;
    }

    .filter-btn.active:hover {
        background: #1a252f;
        transform: translateX(0);
    }

    .filter-btn i {
        font-size: 14px;
        width: 16px;
    }

    /* Imágenes de marca en los botones */
    .filter-btn .marca-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
        border-radius: 4px;
        background: white;
        padding: 2px;
        border: 1px solid #e0e0e0;
    }
    
    .filter-btn.active .marca-icon {
        border-color: white;
        background: rgba(255,255,255,0.2);
    }

    /* Nombre del producto clickeable */
    .product__item__text h6 span {
        transition: color 0.3s ease;
    }

    .product__item__text h6 span:hover {
        color: #ca1515;
    }

    /* Estilos para la búsqueda */
    .search-box-wrapper {
        position: relative;
        width: 100%;
    }

    .search-box-wrapper input {
        width: 100%;
        padding: 10px 40px 10px 15px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-box-wrapper input:focus {
        outline: none;
        border-color: #2c3e50;
        box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    }

    .search-box-wrapper .search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        pointer-events: none;
    }

    /* Mejorar sección de precio */
    .sidebar__filter .section-title h4 {
        margin-bottom: 15px;
        color: #2c3e50;
        font-weight: 600;
    }

    /* Espaciado entre secciones */
    .shop__sidebar > div {
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .shop__sidebar > div:last-child {
        border-bottom: none;
    }

    /* Mejorar contador de productos */
    .shop__product__option__left p {
        font-size: 15px;
        color: #666;
        margin: 0;
    }

    .shop__product__option__left strong {
        color: #2c3e50;
        font-size: 18px;
    }

    /* Animación de carga */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .product__item {
        animation: fadeIn 0.3s ease-in-out;
    }

    /* Slider de precio personalizado */
    .ui-slider-horizontal {
        height: 6px;
        background: #e0e0e0;
        border-radius: 3px;
        border: none;
    }

    .ui-slider-horizontal .ui-slider-handle {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #2c3e50;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        cursor: pointer;
        top: -6px;
    }

    .ui-slider-horizontal .ui-slider-handle:hover {
        background: #1a252f;
    }

    .ui-slider-horizontal .ui-slider-range {
        background: #2c3e50;
        border-radius: 3px;
    }

    /* CORREGIR PARPADEO DE IMÁGENES */
    .product__item__pic.set-bg {
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        min-height: 320px;
        transition: none !important; /* Eliminar transición que causa parpadeo */
    }

    /* Cursor pointer para doble click */
    .product__item__pic.set-bg {
        cursor: pointer;
    }

    .product__item__pic.set-bg:active {
        transform: scale(0.98);
    }

    /* Prevenir flash de contenido */
    .product__item {
        visibility: visible;
        opacity: 1;
    }

    /* ============================================
       RESPONSIVO - MÓVIL PRIMERO
       ============================================ */

    /* Botón flotante de filtros para móvil */
    .btn-mobile-filters {
        display: none; /* Oculto por defecto en desktop */
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 998;
        padding: 14px 24px;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-mobile-filters:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    }

    .btn-mobile-filters:active {
        transform: translateY(-1px);
    }

    .btn-mobile-filters i {
        font-size: 16px;
    }

    .btn-mobile-filters .filter-count {
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        margin-left: 4px;
    }

    /* Overlay para cerrar filtros en móvil */
    .filters-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 997;
        backdrop-filter: blur(2px);
    }

    .filters-overlay.active {
        display: block;
    }
    
    /* En móvil, mostrar productos primero y filtros después */
    @media (max-width: 991px) {
        /* Mostrar botón flotante de filtros */
        .btn-mobile-filters {
            display: flex !important;
        }

        /* Convertir el row en flex para poder reordenar */
        .shop .container > .row {
            display: flex;
            flex-direction: column;
        }

        /* Sidebar de filtros - oculto por defecto en móvil */
        .col-lg-3.col-md-3 {
            order: 2;
            margin-top: 30px;
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
            display: none; /* Oculto por defecto */
        }

        .col-lg-3.col-md-3.show-filters {
            display: block !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            margin: 0;
            overflow-y: auto;
            background: white;
            animation: slideInUp 0.3s ease-out;
        }

        @keyframes slideInUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }

        /* Productos van primero (order: 1) */
        .col-lg-9.col-md-9 {
            order: 1;
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }

        /* Estilos del sidebar cuando está visible */
        .col-lg-3.col-md-3.show-filters .shop__sidebar {
            padding: 20px;
            background: white;
            border-radius: 0;
            box-shadow: none;
            height: 100%;
            overflow-y: auto;
        }

        /* Botón cerrar filtros en móvil */
        .col-lg-3.col-md-3.show-filters .shop__sidebar::before {
            content: '✕ Cerrar Filtros';
            display: block;
            font-size: 18px;
            font-weight: 700;
            color: white;
            text-align: center;
            padding: 15px;
            background: #2c3e50;
            margin: -20px -20px 20px -20px;
            cursor: pointer;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .shop__sidebar {
            padding: 20px;
            background: white;
        }

        /* Hacer filtros colapsables en móvil */
        .sidebar__categories,
        .sidebar__filter {
            margin-bottom: 20px;
        }

        .section-title h4 {
            font-size: 16px;
            padding: 10px 0;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 15px;
        }
    }

    /* Tablets */
    @media (max-width: 768px) {
        /* Productos en 2 columnas */
        .product__item__pic.set-bg {
            min-height: 250px;
        }

        .filter-btn {
            font-size: 13px;
            padding: 9px 12px;
        }

        .btn-clear-filters {
            font-size: 12px;
            padding: 10px 15px;
        }
    }

    /* Móviles pequeños */
    @media (max-width: 576px) {
        /* Productos en 1 columna en móviles muy pequeños */
        .col-sm-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .product__item__pic.set-bg {
            min-height: 300px;
        }

        /* Optimizar filtros para móvil */
        .filter-buttons-group {
            gap: 6px;
        }

        .filter-btn {
            font-size: 12px;
            padding: 8px 10px;
        }

        .filter-btn i {
            font-size: 12px;
        }

        .section-title h4 {
            font-size: 14px;
        }

        /* Botón limpiar más compacto */
        .btn-clear-filters {
            font-size: 11px;
            padding: 8px 12px;
        }

        /* Espaciado del shop section */
        .shop.spad {
            padding: 40px 0;
        }

        /* Breadcrumb más pequeño */
        .breadcrumb-option {
            padding: 15px 0;
        }

        .breadcrumb__links {
            font-size: 12px;
        }

        /* Slider de precio más pequeño */
        .price-range span {
            font-size: 12px;
        }

        .search-box-wrapper input {
            font-size: 13px;
            padding: 8px 35px 8px 12px;
        }

        /* Ajustar grid de productos */
        .shop__product__option__right p {
            font-size: 12px;
        }

        .nice-select {
            font-size: 13px;
            padding: 8px 12px;
        }
    }

    /* Landscape en móviles */
    @media (max-width: 767px) and (orientation: landscape) {
        .product__item__pic.set-bg {
            min-height: 200px;
        }

        .shop.spad {
            padding: 30px 0;
        }
    }

    /* Pantallas muy pequeñas */
    @media (max-width: 400px) {
        .product__item__text h6 {
            font-size: 13px;
        }

        .product__price {
            font-size: 14px;
        }

        .rating i {
            font-size: 11px;
        }

        .filter-btn .marca-icon {
            width: 20px;
            height: 20px;
        }
    }

    /* ============================================
       MEJORAS ADICIONALES PARA MÓVIL
       ============================================ */

    /* Sidebar de filtros en móvil */
    @media (max-width: 991px) {
        .shop__sidebar {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .shop__sidebar__accordion {
            margin-bottom: 0;
        }

        .sidebar__sizes label,
        .sidebar__color label {
            min-width: auto;
            margin: 5px;
        }
    }

    @media (max-width: 576px) {
        .shop__sidebar {
            padding: 15px;
            margin-bottom: 20px;
        }

        /* Mejorar tarjetas de producto en móvil */
        .product__item {
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .product__item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .product__item__pic {
            border-radius: 12px 12px 0 0;
        }

        .product__item__text {
            padding: 15px;
        }

        /* Breadcrumb responsivo */
        .breadcrumb-option {
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .breadcrumb__links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            font-size: 12px;
        }

        .breadcrumb__links a,
        .breadcrumb__links span {
            font-size: 12px;
        }

        /* Opciones de productos (ordenar, vista) */
        .shop__product__option {
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .shop__product__option__left,
        .shop__product__option__right {
            width: 100%;
            justify-content: space-between;
        }

        /* Búsqueda en móvil */
        .search-box-wrapper {
            margin-bottom: 15px;
        }

        .search-box-wrapper input {
            width: 100%;
            font-size: 14px;
            padding: 10px 40px 10px 15px;
            border-radius: 25px;
            border: 2px solid #e0e0e0;
        }

        .search-box-wrapper input:focus {
            border-color: #ca1515;
            box-shadow: 0 0 0 3px rgba(202, 21, 21, 0.1);
        }

        /* Botones de agregar al carrito más visibles en móvil */
        .product__item__pic__hover li a {
            width: 40px;
            height: 40px;
            font-size: 16px;
            line-height: 40px;
        }

        /* Paginación más compacta */
        .shop__last__option .pagination a {
            margin: 0 3px;
            padding: 8px 12px;
            font-size: 13px;
        }

        /* Ajustar espaciado general */
        .shop.spad {
            padding: 30px 0;
        }

        .container {
            padding-left: 15px;
            padding-right: 15px;
        }

        /* Mejorar sección de título */
        .section-title h4 {
            font-size: 15px;
            padding: 12px 0;
            margin-bottom: 12px;
            border-bottom: 2px solid #ca1515;
            color: #333;
            font-weight: 700;
        }

        /* Slider de precio más touch-friendly */
        #price-slider {
            margin: 20px 5px;
        }

        .ui-slider-handle {
            width: 20px !important;
            height: 20px !important;
            border-radius: 50% !important;
        }

        /* Labels de filtro más grandes para touch */
        .sidebar__categories ul li a,
        .sidebar__brand ul li a {
            padding: 12px 0;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
    }

    /* Ajustes extra pequeños */
    @media (max-width: 400px) {
        .shop__sidebar {
            padding: 12px;
        }

        .product__item__text {
            padding: 12px;
        }

        .section-title h4 {
            font-size: 14px;
            padding: 10px 0;
        }

        .filter-btn {
            font-size: 11px;
            padding: 7px 8px;
        }

        .btn-clear-filters {
            font-size: 10px;
            padding: 7px 10px;
        }

        .product__item__pic__hover li a {
            width: 35px;
            height: 35px;
            font-size: 14px;
            line-height: 35px;
        }
    }

    /* Mejorar accesibilidad táctil */
    @media (hover: none) and (pointer: coarse) {
        .filter-btn,
        .btn-clear-filters,
        .product__item__pic__hover li a {
            min-height: 44px;
        }

        .header__right__widget li a,
        .header__right__widget li span {
            min-width: 44px;
            min-height: 44px;
        }
    }

    /* ============================================
       BOTÓN DE FILTROS MÓVIL - CIRCULAR
       SIEMPRE VISIBLE EN TABLET/MÓVIL
       ============================================ */
    .btn-mobile-filters {
        display: none; /* Oculto por defecto */
        position: fixed !important;
        bottom: 20px !important;
        left: 20px !important;
        z-index: 9999 !important;
        background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
        color: white;
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 20px;
        box-shadow: 0 4px 20px rgba(202, 21, 21, 0.4);
        cursor: pointer;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-mobile-filters:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 6px 25px rgba(202, 21, 21, 0.5);
    }

    .btn-mobile-filters:active {
        transform: translateY(-1px) scale(0.98);
    }

    .btn-mobile-filters i {
        font-size: 22px;
    }

    .btn-mobile-filters .filter-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #fff;
        color: #ca1515;
        border-radius: 50%;
        min-width: 24px;
        height: 24px;
        font-size: 12px;
        font-weight: 700;
        display: none;
        align-items: center;
        justify-content: center;
        border: 2px solid #ca1515;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .btn-mobile-filters .filter-count.active {
        display: flex;
    }

    /* Mostrar en tablet y móvil */
    @media (max-width: 991px) {
        .btn-mobile-filters {
            display: flex !important;
        }
    }

    @media (max-width: 576px) {
        .btn-mobile-filters {
            bottom: 15px;
            left: 15px;
            width: 56px;
            height: 56px;
        }

        .btn-mobile-filters i {
            font-size: 20px;
        }

        .btn-mobile-filters .filter-count {
            min-width: 22px;
            height: 22px;
            font-size: 11px;
        }
    }

    /* ============================================
       SISTEMA DE INTERACCIÓN MÓVIL MEJORADO
       ============================================ */
    @media (max-width: 991px) {
        /* En móvil, los botones están ocultos por defecto */
        .product__item__pic .product__hover {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        /* Cuando se hace tap en la imagen, mostrar botones */
        .product__item__pic.show-mobile-actions .product__hover {
            opacity: 1;
            visibility: visible;
        }

        /* Agregar indicador visual cuando está activo */
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

        /* Hacer los botones más grandes y accesibles en móvil */
        .product__item__pic.show-mobile-actions .product__hover li a {
            width: 45px;
            height: 45px;
            font-size: 18px;
            line-height: 45px;
            animation: fadeInUp 0.3s ease forwards;
        }

        /* Animación de entrada para los botones */
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

        /* Efecto de rebote al tocar */
        .product__item__pic:active {
            transform: scale(0.98);
        }
    }

    /* ============================================
       MODAL DE FILTROS PARA MÓVIL
       ============================================ */
    .filters-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .filters-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .filters-modal {
        position: fixed;
        bottom: -100%;
        left: 0;
        right: 0;
        max-height: 85vh;
        background: white;
        border-radius: 20px 20px 0 0;
        z-index: 1001;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
    }

    .filters-modal.active {
        bottom: 0;
    }

    .filters-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 25px;
        border-bottom: 2px solid #e0e0e0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 20px 20px 0 0;
    }

    .filters-modal-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filters-modal-header i {
        color: #ca1515;
    }

    .filters-modal-close {
        background: #f0f0f0;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filters-modal-close:hover {
        background: #e0e0e0;
        transform: rotate(90deg);
    }

    .filters-modal-close i {
        font-size: 18px;
        color: #666;
    }

    .filters-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px 25px;
        max-height: calc(85vh - 140px);
    }

    .filters-modal-body .shop__sidebar {
        margin-bottom: 0;
        padding: 0;
        box-shadow: none;
        background: transparent;
    }

    .filters-modal-body .section-title h4 {
        font-size: 16px;
        color: #333;
        font-weight: 700;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e0e0;
    }

    .filters-modal-body .filter-btn {
        margin-bottom: 8px;
        font-size: 14px;
        padding: 12px 16px;
    }

    .filters-modal-footer {
        display: flex;
        gap: 12px;
        padding: 15px 25px;
        border-top: 2px solid #e0e0e0;
        background: #f8f9fa;
    }

    .btn-clear-filters-modal,
    .btn-apply-filters-modal {
        flex: 1;
        padding: 14px 20px;
        border: none;
        border-radius: 50px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-clear-filters-modal {
        background: white;
        color: #666;
        border: 2px solid #e0e0e0;
    }

    .btn-clear-filters-modal:hover {
        background: #f8f9fa;
        border-color: #ca1515;
        color: #ca1515;
    }

    .btn-apply-filters-modal {
        background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(202, 21, 21, 0.3);
    }

    .btn-apply-filters-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(202, 21, 21, 0.4);
    }

    /* Ocultar sidebar original en móvil */
    @media (max-width: 991px) {
        .col-lg-3.col-md-3 {
            display: none !important;
        }
    }

    /* ============================================
       MASONRY LAYOUT PARA PRODUCTOS
       ============================================ */
    .productos-grid {
        /* En desktop, comportamiento normal */
    }

    /* 2 columnas en móvil con masonry */
    @media (max-width: 991px) {
        .productos-grid {
            display: flex;
            flex-wrap: wrap;
            margin-left: -10px;
            margin-right: -10px;
        }

        .productos-grid .grid-item {
            padding-left: 10px;
            padding-right: 10px;
            margin-bottom: 20px;
            width: 50% !important;
            max-width: 50% !important;
            flex: 0 0 50% !important;
        }

        .product__item {
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .product__item:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .product__item__pic {
            flex-shrink: 0;
        }

        .product__item__text {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
    }

    @media (max-width: 576px) {
        .productos-grid .grid-item {
            margin-bottom: 15px;
        }

        .product__item {
            border-radius: 10px;
        }

        .product__item__pic {
            min-height: 200px;
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
            width: 36px;
            height: 36px;
            font-size: 14px;
            line-height: 36px;
        }
    }

    @media (max-width: 400px) {
        .productos-grid {
            margin-left: -5px;
            margin-right: -5px;
        }

        .productos-grid .grid-item {
            padding-left: 5px;
            padding-right: 5px;
            margin-bottom: 10px;
        }

        .product__item__pic {
            min-height: 180px;
        }

        .product__item__text {
            padding: 10px;
        }

        .product__item__text h6 {
            font-size: 12px;
        }

        .product__price {
            font-size: 13px;
        }
    }
    </style>

    <script>
    // ============================================
    // MODAL DE FILTROS PARA MÓVIL
    // ============================================
    $(document).ready(function() {
        const btnMobileFilters = $('#btnMobileFilters');
        const filtersModal = $('#filtersModal');
        const filtersOverlay = $('#filtersModalOverlay');
        const closeModal = $('#closeFiltersModal');
        const applyFilters = $('#applyFiltersModal');
        const filtersContent = $('#filtersModalContent');
        const sidebarOriginal = $('.shop__sidebar').first();

        // Abrir modal
        btnMobileFilters.on('click', function() {
            // Clonar el sidebar original al modal
            const sidebarClone = sidebarOriginal.clone();
            filtersContent.html(sidebarClone);
            
            // Mostrar modal
            filtersOverlay.addClass('active');
            filtersModal.addClass('active');
            $('body').css('overflow', 'hidden');
        });

        // Cerrar modal
        function cerrarModal() {
            filtersOverlay.removeClass('active');
            filtersModal.removeClass('active');
            $('body').css('overflow', '');
        }

        closeModal.on('click', cerrarModal);
        filtersOverlay.on('click', cerrarModal);

        // Aplicar filtros y cerrar
        applyFilters.on('click', function() {
            cerrarModal();
            // Los filtros se aplican automáticamente por los botones clonados
        });

        // Prevenir que el modal se cierre al hacer clic dentro de él
        filtersModal.on('click', function(e) {
            e.stopPropagation();
        });
    });

    // ============================================
    // SISTEMA DE INTERACCIÓN SEPARADO PC Y MÓVIL
    // ============================================
    $(document).ready(function() {
        const esDispositivoMovil = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        if (esDispositivoMovil) {
            // ============================================
            // COMPORTAMIENTO PARA MÓVIL/TABLET
            // ============================================
            $(document).on('click', '.product__item__pic', function(e) {
                const $pic = $(this);
                const $target = $(e.target);
                
                // Si se hace clic en un botón del product__hover, permitir acción
                if ($target.closest('.product__hover').length) {
                    return; // Dejar que el enlace funcione normalmente
                }
                
                // Si ya tiene botones visibles
                if ($pic.hasClass('show-mobile-actions')) {
                    // Si hace clic en la imagen de nuevo, ir a detalles
                    const productUrl = $pic.data('product-url');
                    if (productUrl && !$target.closest('.product__hover').length) {
                        window.location.href = productUrl;
                    }
                } else {
                    // Ocultar botones de otros productos
                    $('.product__item__pic').removeClass('show-mobile-actions');
                    // Mostrar botones de este producto
                    $pic.addClass('show-mobile-actions');
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            // Cerrar botones al tocar fuera del producto
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.product__item__pic').length) {
                    $('.product__item__pic').removeClass('show-mobile-actions');
                }
            });
        } else {
            // ============================================
            // COMPORTAMIENTO PARA PC/DESKTOP
            // ============================================
            // En PC, click directo va a detalles (hover muestra botones automáticamente con CSS)
            $(document).on('click', '.product__item__pic', function(e) {
                const $target = $(e.target);
                
                // Si se hace clic en un botón del product__hover, permitir acción
                if ($target.closest('.product__hover').length) {
                    return; // Dejar que el enlace funcione normalmente
                }
                
                // Si se hace clic en la imagen, ir a detalles
                const productUrl = $(this).data('product-url');
                if (productUrl) {
                    window.location.href = productUrl;
                }
            });
        }
    });

    // ============================================
    // MASONRY LAYOUT PARA PRODUCTOS
    // ============================================
    let masonryInstance = null;

    function initMasonry() {
        const grid = document.querySelector('#productosGrid');
        if (!grid) return;

        // Destruir instancia anterior si existe
        if (masonryInstance) {
            masonryInstance.destroy();
        }

        // Solo aplicar masonry en móvil
        if (window.innerWidth <= 991) {
            // Esperar a que las imágenes se carguen
            imagesLoaded(grid, function() {
                masonryInstance = new Masonry(grid, {
                    itemSelector: '.grid-item',
                    columnWidth: '.grid-item',
                    percentPosition: true,
                    gutter: 0,
                    transitionDuration: '0.3s',
                    fitWidth: false,
                    horizontalOrder: true
                });

                // Forzar layout después de un breve delay
                setTimeout(function() {
                    if (masonryInstance) {
                        masonryInstance.layout();
                    }
                }, 100);
            });
        }
    }

    // Inicializar masonry al cargar la página
    $(document).ready(function() {
        initMasonry();
    });

    // Reinicializar en resize con debounce
    let resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            initMasonry();
        }, 250);
    });

    // Reinicializar después de aplicar filtros AJAX
    $(document).on('productosActualizados', function() {
        setTimeout(function() {
            initMasonry();
        }, 100);
    });
    </script>

    <!-- Global Offcanvas Menu JavaScript -->
    <script src="public/assets/js/offcanvas-menu.js"></script>
</body>
</html>
