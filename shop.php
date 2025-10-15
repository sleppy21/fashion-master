<?php
/**
 * SHOP - PÁGINA DE TIENDA MODERNIZADA
 * Catálogo de productos con filtros avanzados
 * @version 2.0 - Octubre 2025
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Cargar dependencias
require_once 'config/conexion.php';
require_once 'config/config.php'; // ← IMPORTANTE: Define BASE_URL y otras constantes
require_once 'app/controllers/ShopController.php';
require_once 'app/views/components/product-card.php';

// Obtener datos del controlador
$data = ShopController::index();
extract($data); // Extrae todas las variables del array

$page_title = "Tienda";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Descubre nuestra colección de productos de moda y accesorios">
    <meta name="keywords" content="tienda, moda, ropa, accesorios, compras online">
    <title><?= $page_title ?> - SleppyStore</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    
    <!-- Modern Libraries -->
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- Shop Modern CSS -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-modern.css?v=2.0">
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=3.0">
    <link rel="stylesheet" href="public/assets/css/shop/shop-filters-modern.css?v=2.0">
    <link rel="stylesheet" href="public/assets/css/shop/shop-sticky-header.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/shop/fix-grid.css?v=<?= time() ?>">
    
    <!-- noUiSlider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css">
    
    <!-- Modals -->
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css">
    <link rel="stylesheet" href="public/assets/css/modals-dark-mode.css">
    
    <!-- Dark Mode -->
    <link rel="stylesheet" href="public/assets/css/dark-mode.css?v=<?= time() ?>">
    
    <!-- HEADER FIX - CARGADO AL FINAL PARA SOBRESCRIBIR CONFLICTOS -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-header-fix.css?v=<?= time() ?>">
    
    <!-- Config Script -->
    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        window.SHOP_FILTERS = <?= json_encode($filters) ?>;
        console.log('🛍️ Shop Modern v2.0 cargado');
        console.log('🌐 BASE_URL:', window.BASE_URL);
        console.log('🔒 Protocol:', window.BASE_URL.split(':')[0]);
        
        // Verificar y corregir protocolo si es necesario
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            console.warn('⚠️ Corrigiendo protocolo de HTTP a HTTPS');
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
            console.log('✅ BASE_URL corregido:', window.BASE_URL);
        }
    </script>
</head>

<body class="shop-page">
    <!-- Offcanvas Menu -->
    <?php include 'includes/offcanvas-menu.php'; ?>
    
    <!-- Header con modales -->
    <?php include 'includes/header-section.php'; ?>
    
    <!-- Breadcrumb -->
    <?php include 'includes/breadcrumb.php'; ?>
    
    <!-- Botón de filtros móvil -->
    <button class="btn-mobile-filters" id="btnMobileFilters" aria-label="Abrir filtros">
        <i class="fa fa-filter"></i>
        <span class="filter-count" id="filterCount">0</span>
    </button>
    
    <!-- Main Shop Section -->
    <section class="shop-modern spad">
        <div class="container-fluid px-lg-5">
            <div class="row">
                
                <!-- ========== SIDEBAR - FILTROS ========== -->
                <aside class="col-lg-3 col-md-4">
                    <div class="shop-sidebar modern-sidebar" data-aos="fade-right">
                        
                        <!-- Header -->
                        <div class="sidebar-header">
                            <h2><i class="fa fa-sliders"></i> Filtros</h2>
                            <button class="btn-clear" onclick="limpiarFiltros()" title="Limpiar filtros">
                                <i class="fa fa-redo"></i>
                            </button>
                        </div>
                        
                        <!-- Filtro: Categorías -->
                        <div class="filter-section">
                            <h3 class="filter-title">
                                <i class="fa fa-th-large"></i> Categorías
                            </h3>
                            <div class="filter-buttons">
                                <button class="filter-chip <?= !$filters['categoria'] ? 'active' : '' ?>" 
                                        data-filter-type="categoria" 
                                        data-filter-value="null">
                                    <i class="fa fa-th"></i>
                                    <span>Todas</span>
                                </button>
                                <?php foreach($categorias as $cat): ?>
                                    <button class="filter-chip <?= $filters['categoria'] == $cat['id_categoria'] ? 'active' : '' ?>" 
                                            data-filter-type="categoria" 
                                            data-filter-value="<?= $cat['id_categoria'] ?>">
                                        <span><?= htmlspecialchars($cat['nombre_categoria']) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Filtro: Género -->
                        <div class="filter-section">
                            <h3 class="filter-title">
                                <i class="fa fa-venus-mars"></i> Género
                            </h3>
                            <div class="filter-buttons">
                                <?php 
                                $generos = [
                                    'all' => ['icon' => 'users', 'label' => 'Todos'],
                                    'hombre' => ['icon' => 'mars', 'label' => 'Hombre'],
                                    'mujer' => ['icon' => 'venus', 'label' => 'Mujer'],
                                    'unisex' => ['icon' => 'genderless', 'label' => 'Unisex']
                                ];
                                
                                foreach($generos as $value => $genero): 
                                    $is_active = ($filters['genero'] == $value) || (!$filters['genero'] && $value == 'all');
                                ?>
                                    <button class="filter-chip <?= $is_active ? 'active' : '' ?>" 
                                            data-filter-type="genero" 
                                            data-filter-value="<?= $value ?>">
                                        <i class="fa fa-<?= $genero['icon'] ?>"></i>
                                        <span><?= $genero['label'] ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Filtro: Marcas -->
                        <div class="filter-section">
                            <h3 class="filter-title">
                                <i class="fa fa-trademark"></i> Marcas
                            </h3>
                            <div class="filter-buttons">
                                <button class="filter-chip <?= !$filters['marca'] ? 'active' : '' ?>" 
                                        data-filter-type="marca" 
                                        data-filter-value="null">
                                    <i class="fa fa-th"></i>
                                    <span>Todas</span>
                                </button>
                                <?php foreach($marcas as $marca): ?>
                                    <button class="filter-chip <?= $filters['marca'] == $marca['id_marca'] ? 'active' : '' ?>" 
                                            data-filter-type="marca" 
                                            data-filter-value="<?= $marca['id_marca'] ?>">
                                        <?php if (!empty($marca['url_imagen_marca'])): ?>
                                            <img src="<?= $marca['url_imagen_marca'] ?>" 
                                                 alt="<?= htmlspecialchars($marca['nombre_marca']) ?>"
                                                 class="brand-logo">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($marca['nombre_marca']) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Filtro: Precio -->
                        <div class="filter-section price-filter-section">
                            <h3 class="filter-title">
                                <i class="fa fa-dollar"></i> Rango de Precio
                                <button class="price-reset-btn" id="resetPriceBtn" title="Restablecer precio">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </h3>
                            <div class="price-filter-modern">
                                <!-- Display de Precio Seleccionado -->
                                <div class="price-display">
                                    <div class="price-badge">
                                        <span class="price-label">Desde</span>
                                        <span class="price-value" id="display-min-price">$0</span>
                                    </div>
                                    <div class="price-divider">
                                        <i class="fa fa-arrows-h"></i>
                                    </div>
                                    <div class="price-badge">
                                        <span class="price-label">Hasta</span>
                                        <span class="price-value" id="display-max-price">$10,000</span>
                                    </div>
                                </div>

                                <!-- Slider de Precio -->
                                <div class="price-slider-container">
                                    <div id="price-slider" class="price-slider"></div>
                                    <div class="price-marks" id="price-marks">
                                        <span class="mark-min" data-value="0">$0</span>
                                        <span class="mark-current-min" data-value="">-</span>
                                        <span class="mark-mid" data-value="5000">$5k</span>
                                        <span class="mark-current-max" data-value="">-</span>
                                        <span class="mark-max" data-value="10000">$10k</span>
                                    </div>
                                </div>

                                <!-- Inputs ocultos para mantener la funcionalidad -->
                                <input type="hidden" 
                                       id="min-price" 
                                       value="<?= $filters['precio_min'] ?>">
                                <input type="hidden" 
                                       id="max-price" 
                                       value="<?= $filters['precio_max'] ?>">
                            </div>
                        </div>
                        
                    </div>
                </aside>
                
                <!-- ========== PRODUCTOS GRID ========== -->
                <main class="col-lg-9 col-md-8">
                    
                    <!-- Barra superior -->
                    <div class="shop-topbar modern-topbar" data-aos="fade-left">
                        <div class="topbar-left">
                            <h1 class="shop-title">
                                <i class="fa fa-store"></i> Catálogo
                            </h1>
                            <span class="results-count" id="results-count">
                                <?= count($productos) ?> producto<?= count($productos) != 1 ? 's' : '' ?> encontrado<?= count($productos) != 1 ? 's' : '' ?>
                            </span>
                        </div>
                        
                        <div class="topbar-right">
                            <!-- Búsqueda -->
                            <div class="search-box-modern">
                                <input type="text" 
                                       id="search-input" 
                                       class="search-input" 
                                       placeholder="Buscar productos..."
                                       value="<?= htmlspecialchars($filters['buscar']) ?>">
                                <i class="fa fa-search"></i>
                            </div>
                            
                            <!-- Ordenar -->
                            <div class="sort-dropdown">
                                <button class="btn-sort" id="btnSort">
                                    <i class="fa fa-sort-amount-down"></i>
                                    <span>Ordenar</span>
                                </button>
                                <div class="sort-menu" id="sortMenu">
                                    <button data-filter-type="ordenar" data-filter-value="newest" 
                                            class="<?= $filters['ordenar'] == 'newest' ? 'active' : '' ?>">
                                        <i class="fa fa-clock"></i> Más recientes
                                    </button>
                                    <button data-filter-type="ordenar" data-filter-value="price_asc"
                                            class="<?= $filters['ordenar'] == 'price_asc' ? 'active' : '' ?>">
                                        <i class="fa fa-arrow-up"></i> Precio: Menor a Mayor
                                    </button>
                                    <button data-filter-type="ordenar" data-filter-value="price_desc"
                                            class="<?= $filters['ordenar'] == 'price_desc' ? 'active' : '' ?>">
                                        <i class="fa fa-arrow-down"></i> Precio: Mayor a Menor
                                    </button>
                                    <button data-filter-type="ordenar" data-filter-value="name_asc"
                                            class="<?= $filters['ordenar'] == 'name_asc' ? 'active' : '' ?>">
                                        <i class="fa fa-sort-alpha-down"></i> Nombre: A-Z
                                    </button>
                                    <button data-filter-type="ordenar" data-filter-value="rating"
                                            class="<?= $filters['ordenar'] == 'rating' ? 'active' : '' ?>">
                                        <i class="fa fa-star"></i> Mejor valorados
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grid de productos -->
                    <div class="products-grid-modern" id="products-container">
                        <div class="row">
                            <?php if (count($productos) > 0): ?>
                                <?php foreach($productos as $producto): ?>
                                    <?php 
                                    $is_favorite = in_array($producto['id_producto'], $favoritos_ids);
                                    $in_cart = in_array($producto['id_producto'], $carrito_ids);
                                    renderProductCard($producto, $is_favorite, $usuario_logueado !== null, $in_cart); 
                                    ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="no-products-message" data-aos="fade-up">
                                        <i class="fa fa-search"></i>
                                        <h3>No se encontraron productos</h3>
                                        <p>Intenta ajustar los filtros o buscar algo diferente</p>
                                        <button class="btn-primary" onclick="limpiarFiltros()">
                                            <i class="fa fa-redo"></i> Limpiar filtros
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </main>
                
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    
    <!-- Core Scripts -->
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
    
    <!-- Header Handler - Actualización en tiempo real -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Shop Scripts -->
    <script src="public/assets/js/shop/shop-filters.js?v=2.0"></script>
    <script src="public/assets/js/shop/price-slider.js?v=2.0"></script>
    <script src="public/assets/js/shop/search-live.js?v=2.0"></script>
    
    <!-- Global Scripts -->
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/dark-mode.js"></script>
    <script src="public/assets/js/scroll-position-memory.js"></script>
    <script src="public/assets/js/image-color-extractor.js"></script>
    
    <!-- AOS Animations -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });
        
        // Sticky Header Scroll Effect
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header');
            let lastScroll = 0;
            
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                
                // Agregar clase cuando se hace scroll
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                lastScroll = currentScroll;
            });
        });
        
        // Sticky Sidebar Scroll Effect
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.modern-sidebar');
            const sidebarHeader = document.querySelector('.sidebar-header');
            
            if (sidebar && sidebarHeader) {
                sidebar.addEventListener('scroll', function() {
                    if (sidebar.scrollTop > 10) {
                        sidebarHeader.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
                    } else {
                        sidebarHeader.style.boxShadow = 'none';
                    }
                });
            }
        });
    </script>
    
    <!-- Real-time Updates System -->
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>
</html>
