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
$filtro_ordenar = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

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

// Agregar ordenamiento según el filtro seleccionado
switch($filtro_ordenar) {
    case 'price_asc':
        $query .= " ORDER BY (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY p.nombre_producto ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.nombre_producto DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.id_producto DESC";
        break;
    case 'rating':
        $query .= " ORDER BY calificacion_promedio DESC, total_resenas DESC";
        break;
    default:
        $query .= " ORDER BY p.id_producto DESC";
        break;
}

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
    $marcas_resultado = executeQuery("SELECT id_marca, nombre_marca, url_imagen_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
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
    
    <!-- noUiSlider - Reemplaza jQuery UI Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css">
    
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- Modals CSS -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Header Responsive Global CSS -->
    <link rel="stylesheet" href="public/assets/css/header-responsive.css?v=3.0" type="text/css">
    
    <!-- Header Override - Máxima prioridad -->
    <link rel="stylesheet" href="public/assets/css/header-override.css?v=2.0" type="text/css">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- ===================================================================
         FUNCIONES DE FILTROS - CARGADAS EN HEAD PARA DISPONIBILIDAD GLOBAL
         =================================================================== -->
    <script>
    // Variables globales para manejar filtros
    var filtrosActuales = {
        categoria: <?php echo $filtro_categoria ?: 'null'; ?>,
        genero: <?php echo $filtro_genero ? "'" . $filtro_genero . "'" : 'null'; ?>,
        marca: <?php echo $filtro_marca ?: 'null'; ?>,
        precio_min: <?php echo $filtro_precio_min; ?>,
        precio_max: <?php echo $filtro_precio_max; ?>,
        buscar: '<?php echo addslashes($filtro_buscar); ?>'
    };

    // Función unificada para aplicar filtros - DISPONIBLE GLOBALMENTE
    function aplicarFiltro(tipo, valor) {
        // Actualizar el filtro específico
        if(valor === null || valor === '' || valor === 'all') {
            filtrosActuales[tipo] = null;
        } else {
            filtrosActuales[tipo] = valor;
        }
        
        // Actualizar estado visual de los botones (solo si el DOM está listo)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                actualizarBotonesActivos(tipo, valor);
                aplicarFiltrosAjax();
            });
        } else {
            actualizarBotonesActivos(tipo, valor);
            aplicarFiltrosAjax();
        }
    }

    // Función para actualizar el estado visual de los botones
    function actualizarBotonesActivos(tipo, valor) {
        const botones = document.querySelectorAll('[data-filter-type="' + tipo + '"]');
        
        botones.forEach(function(boton) {
            const btnValue = boton.getAttribute('data-filter-value');
            boton.classList.remove('active');
            
            if ((valor === null || valor === '' || valor === 'all') && btnValue === '') {
                boton.classList.add('active');
            } else if (btnValue == valor) {
                boton.classList.add('active');
            }
        });
    }

    // Función para aplicar filtros vía Fetch API
    function aplicarFiltrosAjax() {
        let params = new URLSearchParams();
        
        if(filtrosActuales.categoria) params.set('c', filtrosActuales.categoria);
        if(filtrosActuales.genero) params.set('g', filtrosActuales.genero);
        if(filtrosActuales.marca) params.set('m', filtrosActuales.marca);
        if(filtrosActuales.precio_min > 0 || filtrosActuales.precio_max < 500) {
            params.set('pmin', filtrosActuales.precio_min);
            params.set('pmax', filtrosActuales.precio_max);
        }
        if(filtrosActuales.buscar) params.set('q', filtrosActuales.buscar);
        
        // Agregar ordenamiento
        const sortValue = document.getElementById('sortSelect')?.value;
        if(sortValue && sortValue !== 'default') {
            params.set('sort', sortValue);
        }
        
        const newUrl = params.toString() ? '?' + params.toString() : window.location.pathname;
        history.pushState({}, '', newUrl);
        
        const productsContainer = document.querySelector('.shop__product__option__right').closest('.row').nextElementSibling;
        if (productsContainer) {
            productsContainer.innerHTML = '<div class="col-12 text-center" style="padding: 60px 20px;"><i class="fa fa-spinner fa-spin" style="font-size: 48px; color: #ca1515;"></i><p style="margin-top: 20px; color: #666;">Cargando productos...</p></div>';
        }
        
        fetch('app/actions/get_products_filtered.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success && productsContainer) {
                    productsContainer.innerHTML = data.html;
                    document.dispatchEvent(new CustomEvent('productosActualizados'));
                }
            })
            .catch(error => console.error('Error en filtros:', error));
    }
    </script>
    
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

    <?php include 'includes/breadcrumb.php'; ?>

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
                <!-- SIDEBAR CON FILTROS MEJORADO -->
                <div class="col-lg-3 col-md-3">
                    <div class="shop__sidebar">
                        <!-- Header de filtros -->
                        <div class="filters-header">
                            <h3><i class="fa fa-sliders"></i> Filtros</h3>
                            <button class="btn-clear-filters-compact" onclick="limpiarFiltros()" title="Limpiar filtros">
                                <i class="fa fa-redo"></i>
                            </button>
                        </div>
                        
                        <!-- Filtro por Categorías -->
                        <div class="filter-section">
                            <div class="filter-section-header" data-toggle="collapse" data-target="#categoriesCollapse">
                                <h4><i class="fa fa-th-large"></i> Categorías</h4>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                            <div class="filter-section-body collapse show" id="categoriesCollapse">
                                <div class="filter-buttons-grid">
                                    <button class="filter-chip <?php echo !$filtro_categoria ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('categoria', null)"
                                            data-filter-type="categoria"
                                            data-filter-value="">
                                        <i class="fa fa-th"></i>
                                        <span>Todas</span>
                                    </button>
                                    <?php foreach($categorias as $categoria): 
                                        $is_active = $filtro_categoria == $categoria['id_categoria'];
                                    ?>
                                    <button class="filter-chip <?php echo $is_active ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('categoria', <?php echo $categoria['id_categoria']; ?>)"
                                            data-filter-type="categoria"
                                            data-filter-value="<?php echo $categoria['id_categoria']; ?>">
                                        <span><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Filtro por Género -->
                        <div class="filter-section">
                            <div class="filter-section-header" data-toggle="collapse" data-target="#genderCollapse">
                                <h4><i class="fa fa-venus-mars"></i> Género</h4>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                            <div class="filter-section-body collapse show" id="genderCollapse">
                                <div class="filter-buttons-grid gender-grid">
                                    <button class="filter-chip <?php echo !$filtro_genero || $filtro_genero == 'all' ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('genero', null)"
                                            data-filter-type="genero"
                                            data-filter-value="">
                                        <i class="fa fa-users"></i>
                                        <span>Todos</span>
                                    </button>
                                    <button class="filter-chip <?php echo $filtro_genero == 'F' ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('genero', 'F')"
                                            data-filter-type="genero"
                                            data-filter-value="F">
                                        <i class="fa fa-female"></i>
                                        <span>Mujer</span>
                                    </button>
                                    <button class="filter-chip <?php echo $filtro_genero == 'M' ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('genero', 'M')"
                                            data-filter-type="genero"
                                            data-filter-value="M">
                                        <i class="fa fa-male"></i>
                                        <span>Hombre</span>
                                    </button>
                                    <button class="filter-chip <?php echo $filtro_genero == 'Kids' ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('genero', 'Kids')"
                                            data-filter-type="genero"
                                            data-filter-value="Kids">
                                        <i class="fa fa-child"></i>
                                        <span>Kids</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filtro por Marca -->
                        <div class="filter-section">
                            <div class="filter-section-header" data-toggle="collapse" data-target="#brandsCollapse">
                                <h4><i class="fa fa-bookmark"></i> Marcas</h4>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                            <div class="filter-section-body collapse show" id="brandsCollapse">
                                <div class="filter-list">
                                    <button class="filter-list-item <?php echo !$filtro_marca ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('marca', null)"
                                            data-filter-type="marca"
                                            data-filter-value="">
                                        <i class="fa fa-check-circle check-icon"></i>
                                        <span class="filter-list-text">Todas las marcas</span>
                                    </button>
                                    <?php foreach($marcas as $marca): ?>
                                    <button class="filter-list-item <?php echo $filtro_marca == $marca['id_marca'] ? 'active' : ''; ?>" 
                                            onclick="aplicarFiltro('marca', <?php echo $marca['id_marca']; ?>)"
                                            data-filter-type="marca"
                                            data-filter-value="<?php echo $marca['id_marca']; ?>">
                                        <i class="fa fa-check-circle check-icon"></i>
                                        <?php if(!empty($marca['url_imagen_marca']) && $marca['url_imagen_marca'] != '/fashion-master/public/assets/img/default-product.jpg'): ?>
                                            <img src="<?php echo htmlspecialchars($marca['url_imagen_marca']); ?>" 
                                                 alt="<?php echo htmlspecialchars($marca['nombre_marca']); ?>" 
                                                 class="marca-icon-list">
                                        <?php endif; ?>
                                        <span class="filter-list-text"><?php echo htmlspecialchars($marca['nombre_marca']); ?></span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Filtro por Precio -->
                        <div class="filter-section">
                            <div class="filter-section-header" data-toggle="collapse" data-target="#priceCollapse">
                                <h4><i class="fa fa-dollar-sign"></i> Precio</h4>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                            <div class="filter-section-body collapse show" id="priceCollapse">
                                <div class="price-range-container">
                                    <div class="price-slider" id="price-slider"></div>
                                    <div class="price-inputs">
                                        <div class="price-input-group">
                                            <label>Mínimo</label>
                                            <input type="text" id="minamount" value="$<?php echo $filtro_precio_min; ?>" readonly>
                                        </div>
                                        <span class="price-separator">-</span>
                                        <div class="price-input-group">
                                            <label>Máximo</label>
                                            <input type="text" id="maxamount" value="$<?php echo $filtro_precio_max; ?>" readonly>
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
                    <div class="row mb-4 align-items-end">
                        <div class="col-lg-4 col-md-12 mb-3 mb-lg-0">
                            <div class="shop__product__option__left">
                                <p><strong><?php echo count($productos); ?></strong> productos encontrados</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                            <div class="shop__product__option__sort">
                                <label for="sortSelect" style="font-size: 13px; color: #666; margin-bottom: 5px; display: block;">
                                    <i class="fa fa-sort"></i> Ordenar por:
                                </label>
                                <select id="sortSelect" class="form-control" style="
                                    border: 1px solid #ddd;
                                    border-radius: 6px;
                                    padding: 8px 12px;
                                    font-size: 14px;
                                    color: #333;
                                    background: white;
                                    cursor: pointer;
                                    transition: all 0.3s ease;
                                ">
                                    <option value="default" <?php echo $filtro_ordenar === 'default' ? 'selected' : ''; ?>>Predeterminado</option>
                                    <option value="price_asc" <?php echo $filtro_ordenar === 'price_asc' ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                                    <option value="price_desc" <?php echo $filtro_ordenar === 'price_desc' ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                                    <option value="name_asc" <?php echo $filtro_ordenar === 'name_asc' ? 'selected' : ''; ?>>Nombre: A - Z</option>
                                    <option value="name_desc" <?php echo $filtro_ordenar === 'name_desc' ? 'selected' : ''; ?>>Nombre: Z - A</option>
                                    <option value="newest" <?php echo $filtro_ordenar === 'newest' ? 'selected' : ''; ?>>Más Recientes</option>
                                    <option value="rating" <?php echo $filtro_ordenar === 'rating' ? 'selected' : ''; ?>>Mejor Calificados</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                            <div class="shop__product__option__right">
                                <label style="font-size: 13px; color: #666; margin-bottom: 5px; display: block;">
                                    <i class="fa fa-search"></i> Buscar:
                                </label>
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

    <!-- ============================================
         JAVASCRIPT LIBRARIES - ORDEN CRÍTICO
         ============================================ -->
    
    <!-- 1. jQuery - Requerido por Bootstrap, Owl Carousel, y main.js -->
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    
    <!-- 2. Plugins que dependen de jQuery -->
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/owl.carousel.min.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- 3. Bibliotecas Vanilla JS (NO dependen de jQuery) -->
    <script src="public/assets/js/mixitup.min.js"></script>
    
    <!-- 4. Masonry Layout para efecto cascada en productos -->
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
    
    <!-- 5. noUiSlider - Reemplaza jQuery UI Slider para filtro de precios -->
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
    
    <!-- 6. Fetch API Handler Moderno - Reemplaza AJAX de filtros -->
    <script src="public/assets/js/fetch-api-handler.js"></script>

    <?php if($usuario_logueado): ?>
    <!-- Scripts para carrito y favoritos -->
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/user-account-modal.js"></script>
    <?php endif; ?>
    
    <!-- Scroll Position Memory -->
    <script src="public/assets/js/scroll-position-memory.js"></script>

    <!-- ===================================================================
         INICIALIZACIÓN DE COMPONENTES - EJECUTAR DESPUÉS DE LIBRERÍAS
         =================================================================== -->
    <script>
    // Inicialización cuando el DOM está listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎯 Inicializando componentes de shop.php');
        
        // ==================== BÚSQUEDA EN TIEMPO REAL ====================
        var searchTimeout;
        const searchInput = document.getElementById('search-input-shop');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchValue = this.value;
                
                searchTimeout = setTimeout(function() {
                    filtrosActuales.buscar = searchValue;
                    aplicarFiltrosAjax();
                }, 800);
            });
            console.log('✅ Búsqueda en tiempo real inicializada');
        }

        // ==================== ORDENAMIENTO ====================
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                aplicarFiltrosAjax();
            });
            console.log('✅ Selector de ordenamiento inicializado');
        }

        // ==================== SLIDER DE PRECIO ====================
        const minPrice = <?php echo $filtro_precio_min; ?>;
        const maxPrice = <?php echo $filtro_precio_max; ?>;
        
        const priceSlider = document.getElementById('price-slider');
        if (priceSlider && typeof noUiSlider !== 'undefined') {
            noUiSlider.create(priceSlider, {
                start: [minPrice, maxPrice],
                connect: true,
                range: { 'min': 0, 'max': 500 },
                step: 1,
                format: {
                    to: function(value) { return Math.round(value); },
                    from: function(value) { return Number(value); }
                },
                // Mejorar interacción táctil
                behaviour: 'tap-drag',
                tooltips: false,
                cssPrefix: 'noUi-',
                cssClasses: {
                    target: 'target',
                    base: 'base',
                    origin: 'origin',
                    handle: 'handle',
                    handleLower: 'handle-lower',
                    handleUpper: 'handle-upper',
                    touchArea: 'touch-area',
                    horizontal: 'horizontal',
                    vertical: 'vertical',
                    background: 'background',
                    connect: 'connect',
                    connects: 'connects',
                    ltr: 'ltr',
                    rtl: 'rtl',
                    draggable: 'draggable',
                    drag: 'state-drag',
                    tap: 'state-tap',
                    active: 'active',
                    tooltip: 'tooltip',
                    pips: 'pips',
                    pipsHorizontal: 'pips-horizontal',
                    pipsVertical: 'pips-vertical',
                    marker: 'marker',
                    markerHorizontal: 'marker-horizontal',
                    markerVertical: 'marker-vertical',
                    markerNormal: 'marker-normal',
                    markerLarge: 'marker-large',
                    markerSub: 'marker-sub',
                    value: 'value',
                    valueHorizontal: 'value-horizontal',
                    valueVertical: 'value-vertical',
                    valueNormal: 'value-normal',
                    valueLarge: 'value-large',
                    valueSub: 'value-sub'
                }
            });

            const minAmountInput = document.getElementById('minamount');
            const maxAmountInput = document.getElementById('maxamount');

            // Actualizar inputs mientras se mueve el slider (sin filtrar)
            priceSlider.noUiSlider.on('update', function(values, handle) {
                const value = values[handle];
                if (handle === 0) {
                    minAmountInput.value = '$' + value;
                } else {
                    maxAmountInput.value = '$' + value;
                }
            });

            // Filtrar SOLO al soltar el slider
            priceSlider.noUiSlider.on('set', function(values) {
                filtrosActuales.precio_min = parseInt(values[0]);
                filtrosActuales.precio_max = parseInt(values[1]);
                aplicarFiltrosAjax();
            });
            
            // Permitir escribir en los inputs y actualizar el slider
            minAmountInput.addEventListener('change', function() {
                const value = parseInt(this.value.replace('$', '')) || 0;
                const clampedValue = Math.max(0, Math.min(value, filtrosActuales.precio_max - 1));
                priceSlider.noUiSlider.set([clampedValue, null]);
                filtrosActuales.precio_min = clampedValue;
                aplicarFiltrosAjax();
            });
            
            maxAmountInput.addEventListener('change', function() {
                const value = parseInt(this.value.replace('$', '')) || 500;
                const clampedValue = Math.min(500, Math.max(value, filtrosActuales.precio_min + 1));
                priceSlider.noUiSlider.set([null, clampedValue]);
                filtrosActuales.precio_max = clampedValue;
                aplicarFiltrosAjax();
            });
            
            // Permitir edición manual de los inputs (remover readonly si existe)
            minAmountInput.removeAttribute('readonly');
            maxAmountInput.removeAttribute('readonly');
            
            console.log('✅ Slider de precio inicializado (noUiSlider) - Editable y filtra al soltar');
        }

        actualizarContadorFiltros();
    });

    // Función auxiliar para contar filtros activos
    function actualizarContadorFiltros() {
        let activeCount = 0;
        if (filtrosActuales.categoria) activeCount++;
        if (filtrosActuales.genero && filtrosActuales.genero !== 'all') activeCount++;
        if (filtrosActuales.marca) activeCount++;
        if (filtrosActuales.precio_min > 0 || filtrosActuales.precio_max < 500) activeCount++;
        if (filtrosActuales.buscar) activeCount++;
        
        const countBadge = document.getElementById('filterCount');
        if (countBadge) {
            countBadge.textContent = activeCount > 0 ? activeCount : '';
            if (activeCount > 0) {
                countBadge.classList.add('active');
            } else {
                countBadge.classList.remove('active');
            }
        }
    }

    // Función para limpiar todos los filtros
    function limpiarFiltros() {
        filtrosActuales = {
            categoria: null,
            genero: null,
            marca: null,
            precio_min: 0,
            precio_max: 500,
            buscar: ''
        };
        
        const searchInput = document.getElementById('search-input-shop');
        if (searchInput) searchInput.value = '';
        
        // Actualizar visualmente cada tipo de filtro usando la función existente
        actualizarBotonesActivos('categoria', null);
        actualizarBotonesActivos('genero', null);
        actualizarBotonesActivos('marca', null);
        
        // Resetear slider de precio
        const priceSlider = document.getElementById('price-slider');
        if (priceSlider && priceSlider.noUiSlider) {
            priceSlider.noUiSlider.set([0, 500]);
        }
        const minAmount = document.getElementById('minamount');
        const maxAmount = document.getElementById('maxamount');
        if (minAmount) minAmount.value = '$0';
        if (maxAmount) maxAmount.value = '$500';
        
        // Resetear ordenamiento
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) sortSelect.value = 'newest';
        
        history.pushState({}, '', window.location.pathname);
        aplicarFiltrosAjax();
        actualizarContadorFiltros();
    }
    </script>

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

    <style>
    /* Estilos para botones de filtro */
    .filter-buttons-group {
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
                    
                    // Trigger evento para reinicializar Masonry - VANILLA JS
                    document.dispatchEvent(new CustomEvent('productosActualizados'));
                    
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
        const searchInput = document.getElementById('search-input-shop');
        if (searchInput) searchInput.value = '';
        
        // Actualizar visualmente cada tipo de filtro usando la función existente
        actualizarBotonesActivos('categoria', null);
        actualizarBotonesActivos('genero', null);
        actualizarBotonesActivos('marca', null);
        
        // Resetear slider de precio
        try {
            const priceSlider = document.getElementById('price-slider');
            if (priceSlider && priceSlider.noUiSlider) {
                priceSlider.noUiSlider.set([0, 500]);
            }
            const minAmount = document.getElementById('minamount');
            const maxAmount = document.getElementById('maxamount');
            if (minAmount) minAmount.value = '$0';
            if (maxAmount) maxAmount.value = '$500';
        } catch(e) {
            console.log('Error al resetear slider:', e);
        }
        
        // Resetear ordenamiento a "Más Recientes"
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.value = 'newest';
        }
        
        // Actualizar URL
        history.pushState({}, '', window.location.pathname);
        
        // Recargar productos sin filtros
        aplicarFiltrosAjax();
        
        // Actualizar contador de filtros
        actualizarContadorFiltros();
    }

    // Búsqueda en tiempo real (con debounce) - VANILLA JS
    let searchTimeout;
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda con debounce - VANILLA JS
        const searchInput = document.getElementById('search-input-shop');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchValue = this.value;
                
                searchTimeout = setTimeout(function() {
                    filtrosActuales.buscar = searchValue;
                    aplicarFiltrosAjax();
                }, 800);
            });
        }

        // Inicializar slider de precio - NOUI SLIDER (sin jQuery)
        const minPrice = <?php echo $filtro_precio_min; ?>;
        const maxPrice = <?php echo $filtro_precio_max; ?>;
        
        const priceSlider = document.getElementById('price-slider');
        if (priceSlider && typeof noUiSlider !== 'undefined') {
            noUiSlider.create(priceSlider, {
                start: [minPrice, maxPrice],
                connect: true,
                range: {
                    'min': 0,
                    'max': 500
                },
                step: 1,
                format: {
                    to: function(value) {
                        return Math.round(value);
                    },
                    from: function(value) {
                        return Number(value);
                    }
                }
            });

            const minAmountInput = document.getElementById('minamount');
            const maxAmountInput = document.getElementById('maxamount');

            // Actualizar inputs cuando el slider cambia
            priceSlider.noUiSlider.on('update', function(values, handle) {
                if (minAmountInput) minAmountInput.value = '$' + values[0];
                if (maxAmountInput) maxAmountInput.value = '$' + values[1];
            });

            // Aplicar filtro cuando se suelta el slider
            priceSlider.noUiSlider.on('change', function(values, handle) {
                filtrosActuales.precio_min = parseInt(values[0]);
                filtrosActuales.precio_max = parseInt(values[1]);
                aplicarFiltrosAjax();
            });
        }
        
        // Actualizar contador de filtros al cargar
        updateFilterCount();
    });

    // FUNCIONES PARA FILTROS EN MÓVIL - VANILLA JS
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
        document.body.style.overflow = '';
    }

    // Cerrar filtros al hacer clic fuera
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.col-lg-3.col-md-3.show-filters');
        if (sidebar) {
            const sidebarInner = sidebar.querySelector('.shop__sidebar');
            const rect = sidebarInner.getBoundingClientRect();
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
    </script>

    <script>
    // Collapsible filters functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar collapsibles de filtros
        const filterHeaders = document.querySelectorAll('.filter-section-header');
        
        filterHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const target = document.querySelector(targetId);
                
                if (target) {
                    if (target.classList.contains('show')) {
                        target.classList.remove('show');
                        this.setAttribute('aria-expanded', 'false');
                    } else {
                        target.classList.add('show');
                        this.setAttribute('aria-expanded', 'true');
                    }
                }
            });
        });
    });
    </script>

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
        width: 30px;
        height: 30px;
        object-fit: contain;
        border-radius: 4px;
        padding: 2px;
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

    /* Selector de ordenamiento */
    .shop__product__option__sort label {
        font-weight: 500;
        margin-bottom: 8px;
    }

    .shop__product__option__sort label i {
        color: #ca1515;
        margin-right: 4px;
    }

    .shop__product__option__sort select {
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .shop__product__option__sort select:hover {
        border-color: #ca1515 !important;
        box-shadow: 0 0 0 3px rgba(202, 21, 21, 0.1);
    }

    .shop__product__option__sort select:focus {
        outline: none;
        border-color: #ca1515 !important;
        box-shadow: 0 0 0 3px rgba(202, 21, 21, 0.15);
    }

    /* Alineación de barra de herramientas */
    .shop__product__option__left p {
        margin: 0;
        padding-top: 8px;
    }

    .shop__product__option__right label {
        font-weight: 500;
    }

    .shop__product__option__right label i {
        color: #ca1515;
        margin-right: 4px;
    }

    /* Imágenes de marcas en filtros */
    .marca-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
        margin-right: 8px;
        vertical-align: middle;
        border-radius: 4px;
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
    }

    /* ============================================
       NUEVO DISEÑO DE FILTROS - MODERNO Y COMPACTO
       ============================================ */

    .shop__sidebar {
        background: #ffffff;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    /* Header de filtros */
    .filters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border-bottom: 3px solid #ca1515;
    }

    .filters-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filters-header h3 i {
        font-size: 16px;
    }

    .btn-clear-filters-compact {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .btn-clear-filters-compact:hover {
        background: rgba(255,255,255,0.25);
        transform: rotate(180deg);
    }

    /* Sección de filtro */
    .filter-section {
        border-bottom: 1px solid #f0f0f0;
    }

    .filter-section:last-child {
        border-bottom: none;
    }

    .filter-section-header {
        padding: 16px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        transition: all 0.3s ease;
        user-select: none;
    }

    .filter-section-header:hover {
        background: #f8f9fa;
    }

    .filter-section-header h4 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-section-header h4 i {
        font-size: 13px;
        color: #ca1515;
    }

    .toggle-icon {
        font-size: 12px;
        color: #999;
        transition: transform 0.3s ease;
    }

    .filter-section-header[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }

    .filter-section-body {
        padding: 12px 20px 20px;
        background: #fafafa;
    }

    /* Filtros tipo chip (botones pequeños) */
    .filter-buttons-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .gender-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .filter-chip {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 12px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .filter-chip i {
        font-size: 14px;
        flex-shrink: 0;
    }

    .filter-chip span {
        flex: 1;
        text-align: center;
    }

    .filter-chip:hover {
        border-color: #ca1515;
        background: #fff5f5;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(202, 21, 21, 0.15);
    }

    .filter-chip.active {
        background: linear-gradient(135deg, #ca1515 0%, #a01212 100%);
        border-color: #ca1515;
        color: white;
        box-shadow: 0 4px 12px rgba(202, 21, 21, 0.3);
    }

    .filter-chip.active:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(202, 21, 21, 0.4);
    }

    /* Filtros tipo lista (marcas) */
    .filter-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-height: 300px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .filter-list::-webkit-scrollbar {
        width: 6px;
    }

    .filter-list::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 10px;
    }

    .filter-list::-webkit-scrollbar-thumb {
        background: #ca1515;
        border-radius: 10px;
    }

    .filter-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: white;
        border: 1px solid #e8e8e8;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: left;
    }

    .filter-list-item:hover {
        border-color: #ca1515;
        background: #fff5f5;
        transform: translateX(4px);
    }

    .filter-list-item.active {
        border-color: #ca1515;
        background: #fff5f5;
    }

    .check-icon {
        font-size: 16px;
        color: transparent;
        transition: all 0.3s ease;
    }

    .filter-list-item.active .check-icon {
        color: #ca1515;
    }

    .marca-icon-list {
        width: 22px;
        height: 22px;
        object-fit: contain;
        border-radius: 4px;
    }

    .filter-list-text {
        flex: 1;
        font-size: 13px;
        font-weight: 500;
        color: #555;
    }

    .filter-list-item.active .filter-list-text {
        color: #ca1515;
        font-weight: 600;
    }

    /* Contenedor de precio */
    .price-range-container {
        background: white;
        padding: 20px 15px;
        border-radius: 8px;
    }

    .price-slider {
        margin-bottom: 20px;
    }

    .price-inputs {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .price-input-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .price-input-group label {
        font-size: 11px;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .price-input-group input {
        width: 100%;
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        text-align: center;
        background: #f8f9fa;
        cursor: default;
    }

    .price-separator {
        font-size: 18px;
        font-weight: 600;
        color: #ccc;
        margin-top: 20px;
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
    
    /* noUiSlider - Mejorar área táctil en móvil */
    .noUi-target {
        background: #e8e8e8;
        border-radius: 8px;
        border: none;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        height: 8px;
    }
    
    .noUi-connect {
        background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
    }
    
    .noUi-handle {
        width: 28px !important;
        height: 28px !important;
        border-radius: 50% !important;
        background: white !important;
        border: 4px solid #ca1515 !important;
        box-shadow: 0 3px 12px rgba(202, 21, 21, 0.4), 0 0 0 8px rgba(202, 21, 21, 0.1) !important;
        cursor: grab !important;
        top: -10px !important;
        outline: none !important;
        transition: all 0.3s ease !important;
    }
    
    .noUi-handle:active {
        cursor: grabbing !important;
        transform: scale(1.2) !important;
        box-shadow: 0 4px 16px rgba(202, 21, 21, 0.6), 0 0 0 12px rgba(202, 21, 21, 0.15) !important;
    }
    
    .noUi-handle:before,
    .noUi-handle:after {
        display: none !important;
    }
    
    /* Área táctil expandida para móvil */
    @media (max-width: 991px) {
        .noUi-handle {
            width: 36px !important;
            height: 36px !important;
            top: -14px !important;
            border-width: 5px !important;
            box-shadow: 0 4px 16px rgba(202, 21, 21, 0.5), 0 0 0 10px rgba(202, 21, 21, 0.12) !important;
        }
        
        .noUi-handle:active {
            transform: scale(1.25) !important;
            box-shadow: 0 6px 20px rgba(202, 21, 21, 0.7), 0 0 0 15px rgba(202, 21, 21, 0.18) !important;
        }
        
        .noUi-target {
            height: 10px;
            margin: 20px 0;
        }
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

        /* Optimizar filtros para móvil - Reducir espaciado */
        .filters-header {
            padding: 14px 16px;
        }

        .filters-header h3 {
            font-size: 16px;
        }

        .filter-section-header {
            padding: 12px 16px;
        }

        .filter-section-header h4 {
            font-size: 13px;
        }

        .filter-section-body {
            padding: 10px 16px 16px;
        }

        .filter-chip {
            font-size: 12px;
            padding: 8px 10px;
        }

        .filter-chip i {
            font-size: 12px;
        }

        .filter-list-item {
            padding: 8px 10px;
            font-size: 12px;
        }

        .marca-icon-list {
            width: 18px;
            height: 18px;
        }

        .price-range-container {
            padding: 12px 10px;
        }

        .price-input-group input {
            padding: 8px;
            font-size: 13px;
        }

        /* Espaciado del shop section - Reducido */
        .shop.spad {
            padding: 0 0 20px 0 !important;
        }
        
        /* Reducir padding del breadcrumb en móvil */
        .breadcrumb-option {
            padding: 8px 0 !important;
            margin-bottom: 10px !important;
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
            padding: 0 0 20px 0;
        }
        
        .breadcrumb-option {
            padding: 10px 0 !important;
            margin-bottom: 10px !important;
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

        .shop__product__option__left p {
            padding-top: 0;
            font-size: 14px;
        }

        /* Selector de ordenamiento en móvil */
        .shop__product__option__sort {
            margin-bottom: 0;
        }

        .shop__product__option__sort label,
        .shop__product__option__right label {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .shop__product__option__sort select {
            width: 100%;
            font-size: 13px !important;
            padding: 10px 12px !important;
        }

        /* Imágenes de marcas más pequeñas en móvil */
        .marca-icon {
            width: 20px;
            height: 20px;
            margin-right: 6px;
        }

        /* Búsqueda en móvil */
        .search-box-wrapper {
            margin-bottom: 0;
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

    /* Mejorar accesibilidad táctil en elementos de filtros */
    @media (hover: none) and (pointer: coarse) {
        .filter-btn,
        .btn-clear-filters,
        .product__item__pic__hover li a {
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
        max-height: 90vh;
        background: #ffffff;
        border-radius: 24px 24px 0 0;
        z-index: 1001;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.15);
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
        padding: 24px 28px;
        border-bottom: 1px solid #e8e8e8;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 24px 24px 0 0;
        position: relative;
    }
    
    /* Barra decorativa superior */
    .filters-modal-header::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 4px;
        background: #d0d0d0;
        border-radius: 2px;
    }

    .filters-modal-header h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #111;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }

    .filters-modal-header i {
        color: #ca1515;
        font-size: 22px;
    }

    .filters-modal-close {
        background: rgba(0,0,0,0.05);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .filters-modal-close:hover {
        background: rgba(202, 21, 21, 0.1);
        transform: rotate(90deg) scale(1.1);
    }

    .filters-modal-close:active {
        transform: rotate(90deg) scale(0.95);
    }

    .filters-modal-close i {
        font-size: 20px;
        color: #666;
    }

    .filters-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px 28px;
        max-height: calc(90vh - 160px);
        background: #fafafa;
    }
    
    /* Reducir padding en móvil */
    @media (max-width: 576px) {
        .filters-modal-body {
            padding: 16px 14px;
        }
    }
    
    /* Scroll suave en modal */
    .filters-modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .filters-modal-body::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .filters-modal-body::-webkit-scrollbar-thumb {
        background: #d0d0d0;
        border-radius: 3px;
    }
    
    .filters-modal-body::-webkit-scrollbar-thumb:hover {
        background: #b0b0b0;
    }

    .filters-modal-body .shop__sidebar {
        margin-bottom: 0;
        padding: 0;
        box-shadow: none;
        background: transparent;
    }

    .filters-modal-body .section-title h4 {
        font-size: 15px;
        color: #111;
        font-weight: 700;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Mejorar filtros dentro del modal */
    .filters-modal-body .filter-btn {
        margin-bottom: 10px;
        font-size: 14px;
        padding: 14px 18px;
        background: white;
        border: 2px solid #e8e8e8;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }
    
    .filters-modal-body .filter-btn:hover {
        border-color: #ca1515;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(202, 21, 21, 0.15);
    }
    
    .filters-modal-body .filter-btn.active {
        background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
        border-color: #ca1515;
        color: white;
        box-shadow: 0 4px 16px rgba(202, 21, 21, 0.3);
    }

    .filters-modal-footer {
        display: flex;
        gap: 14px;
        padding: 20px 28px;
        border-top: 1px solid #e8e8e8;
        background: white;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
    }
    
    /* Reducir padding del footer en móvil */
    @media (max-width: 576px) {
        .filters-modal-footer {
            padding: 14px 16px;
            gap: 10px;
        }
    }

    .btn-clear-filters-modal,
    .btn-apply-filters-modal {
        flex: 1;
        padding: 16px 24px;
        border: none;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        letter-spacing: 0.3px;
    }
    
    /* Reducir tamaño de botones en móvil */
    @media (max-width: 576px) {
        .btn-clear-filters-modal,
        .btn-apply-filters-modal {
            padding: 12px 16px;
            font-size: 14px;
            border-radius: 10px;
            gap: 6px;
        }
    }

    .btn-clear-filters-modal {
        background: white;
        color: #666;
        border: 2px solid #e8e8e8;
    }

    .btn-clear-filters-modal:hover {
        background: #f8f8f8;
        border-color: #ca1515;
        color: #ca1515;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .btn-clear-filters-modal:active {
        transform: translateY(0);
    }

    .btn-apply-filters-modal {
        background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
        color: white;
        box-shadow: 0 4px 20px rgba(202, 21, 21, 0.4);
    }

    .btn-apply-filters-modal:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(202, 21, 21, 0.5);
    }
    
    .btn-apply-filters-modal:active {
        transform: translateY(-1px);
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
        /* Ocultar contador de productos y buscador en móvil */
        .shop__product__option__left {
            display: none !important;
        }
        
        .shop__product__option__right {
            display: none !important;
        }

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
    // MODAL DE FILTROS PARA MÓVIL - VANILLA JS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const btnMobileFilters = document.getElementById('btnMobileFilters');
        const filtersModal = document.getElementById('filtersModal');
        const filtersOverlay = document.getElementById('filtersModalOverlay');
        const closeModal = document.getElementById('closeFiltersModal');
        const applyFilters = document.getElementById('applyFiltersModal');
        const filtersContent = document.getElementById('filtersModalContent');
        const sidebarOriginal = document.querySelector('.shop__sidebar');

        // Abrir modal
        if (btnMobileFilters) {
            btnMobileFilters.addEventListener('click', function() {
                // Clonar el sidebar original al modal
                if (sidebarOriginal) {
                    const sidebarClone = sidebarOriginal.cloneNode(true);
                    filtersContent.innerHTML = '';
                    filtersContent.appendChild(sidebarClone);
                }
                
                // Mostrar modal
                filtersOverlay.classList.add('active');
                filtersModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Cerrar modal
        function cerrarModal() {
            filtersOverlay.classList.remove('active');
            filtersModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (closeModal) {
            closeModal.addEventListener('click', cerrarModal);
        }
        
        if (filtersOverlay) {
            filtersOverlay.addEventListener('click', cerrarModal);
        }

        // Aplicar filtros y cerrar
        if (applyFilters) {
            applyFilters.addEventListener('click', function() {
                cerrarModal();
                // Los filtros se aplican automáticamente por los botones clonados
            });
        }

        // Prevenir que el modal se cierre al hacer clic dentro de él
        if (filtersModal) {
            filtersModal.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });

    // ============================================
    // SISTEMA DE INTERACCIÓN SEPARADO PC Y MÓVIL - VANILLA JS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const esDispositivoMovil = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        if (esDispositivoMovil) {
            // ============================================
            // COMPORTAMIENTO PARA MÓVIL/TABLET
            // ============================================
            document.addEventListener('click', function(e) {
                const pic = e.target.closest('.product__item__pic');
                
                if (pic) {
                    const target = e.target;
                    const hoverEl = target.closest('.product__hover');
                    
                    // Si se hace clic en un botón del product__hover, permitir acción
                    if (hoverEl) {
                        return; // Dejar que el enlace funcione normalmente
                    }
                    
                    // Si ya tiene botones visibles
                    if (pic.classList.contains('show-mobile-actions')) {
                        // Si hace clic en la imagen de nuevo, ir a detalles
                        const productUrl = pic.dataset.productUrl;
                        if (productUrl && !hoverEl) {
                            window.location.href = productUrl;
                        }
                    } else {
                        // Ocultar botones de otros productos
                        document.querySelectorAll('.product__item__pic').forEach(p => {
                            p.classList.remove('show-mobile-actions');
                        });
                        // Mostrar botones de este producto
                        pic.classList.add('show-mobile-actions');
                        e.preventDefault();
                        e.stopPropagation();
                    }
                } else {
                    // Cerrar botones al tocar fuera del producto
                    document.querySelectorAll('.product__item__pic').forEach(p => {
                        p.classList.remove('show-mobile-actions');
                    });
                }
            });
        } else {
            // ============================================
            // COMPORTAMIENTO PARA PC/DESKTOP
            // ============================================
            // En PC, click directo va a detalles (hover muestra botones automáticamente con CSS)
            document.addEventListener('click', function(e) {
                const pic = e.target.closest('.product__item__pic');
                
                if (pic) {
                    const target = e.target;
                    const hoverEl = target.closest('.product__hover');
                    
                    // Si se hace clic en un botón del product__hover, permitir acción
                    if (hoverEl) {
                        return; // Dejar que el enlace funcione normalmente
                    }
                    
                    // Si se hace clic en la imagen, ir a detalles
                    const productUrl = pic.dataset.productUrl;
                    if (productUrl) {
                        window.location.href = productUrl;
                    }
                }
            });
        }
    });

    // ============================================
    // MASONRY LAYOUT PARA PRODUCTOS - VANILLA JS
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

    // Inicializar masonry al cargar la página - VANILLA JS
    document.addEventListener('DOMContentLoaded', function() {
        initMasonry();
    });

    // Reinicializar en resize con debounce - VANILLA JS
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            initMasonry();
        }, 250);
    });

    // Reinicializar después de aplicar filtros - VANILLA JS
    document.addEventListener('productosActualizados', function() {
        setTimeout(function() {
            initMasonry();
        }, 100);
    });
    </script>

    <!-- Global Offcanvas Menu JavaScript -->
    <script src="public/assets/js/offcanvas-menu.js"></script>
</body>
</html>
