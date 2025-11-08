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

    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- Shop Styles - Unificados v3.0 -->
    <link rel="stylesheet" href="public/assets/css/shop/shop.css?v=3.0">
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=3.0">
    <link rel="stylesheet" href="public/assets/css/shop/shop-responsive.css?v=3.1">
    <!-- <link rel="stylesheet" href="public/assets/css/layouts/shop.css?v=<?php echo time(); ?>"> -->


    <!-- Config Script -->
    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        window.SHOP_FILTERS = <?= json_encode($filters) ?>;
        
        // Verificar y corregir protocolo si es necesario
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>
     
</head>

<body class="shop-page">
    <!-- Header con modales (ya incluye offcanvas-menu.php) -->
    <?php include 'includes/header-section.php'; ?>
    
    <!-- Botón de filtros móvil - Diseño del offcanvas -->
    <button class="btn-mobile-filters" id="btnMobileFilters" aria-label="Abrir filtros">
        <i class="fa fa-filter"></i>
    </button>
    
    <!-- Overlay y Wrapper de Filtros Móvil (patrón offcanvas) -->
    <div class="filters-menu-overlay"></div>
    <div class="filters-menu-wrapper">
        <!-- Indicador de swipe (igual que offcanvas menu) -->
        <div class="swipe-indicator">
            <div style="width: 4px; height: 40px; background: rgba(0,0,0,0.2); border-radius: 2px; margin: 0 auto;"></div>
        </div>
        
        <!-- Header del sidebar móvil -->
        <div class="mobile-filters-header">
            <h2><i class="fa fa-sliders"></i> Filtros</h2>
            <button class="btn-clear-icon" onclick="limpiarFiltros()" title="Limpiar filtros" aria-label="Limpiar filtros">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <!-- Contenido de filtros (se clonará aquí via JS) -->
        <div id="mobile-filters-content"></div>
    </div>
    
    <!-- Main Shop Section -->
    <section class="shop-modern spad">
        <div class="container-fluid px-lg-5" style="max-width: 1600px;">
            <div class="row">
                
                <!-- ========== SIDEBAR - FILTROS ========== -->
                <aside class="col-lg-3 col-md-4 col-12">
                    <div class="shop-sidebar modern-sidebar" id="shopFilters">
                        
                        <!-- Header -->
                        <div class="sidebar-header">
                            <h2><i class="fa fa-sliders"></i> Filtros</h2>
                            <button class="btn-clear" onclick="limpiarFiltros()" title="Limpiar todos los filtros">
                                <i class="fa fa-times-circle"></i>
                                <span class="btn-text">Limpiar</span>
                            </button>
                        </div>
                        
                        <!-- Filtro: Categorías -->
                        <div class="filter-section filter-section-first">
                            <h3 class="filter-title">
                                <i class="fa fa-th-large"></i> Categorías
                            </h3>
                            <div class="filter-buttons">
                                <?php foreach($categorias as $cat): 
                                    $is_active = (is_array($filters['categoria']) && in_array($cat['id_categoria'], $filters['categoria']));
                                ?>
                                    <button class="filter-chip <?= $is_active ? 'active' : '' ?>" 
                                            data-filter-type="categoria" 
                                            data-filter-value="<?= $cat['id_categoria'] ?>"
                                            data-multi-select="true">
                                        <?php if (!empty($cat['url_imagen_categoria'])): ?>
                                            <img src="<?= $cat['url_imagen_categoria'] ?>" 
                                                 alt="<?= htmlspecialchars($cat['nombre_categoria']) ?>"
                                                 class="brand-logo">
                                        <?php endif; ?>
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
                                // VALORES CORREGIDOS: Usar M, F, Unisex como en BD
                                $generos = [
                                    'M' => ['icon' => 'mars', 'label' => 'Hombre'],
                                    'F' => ['icon' => 'venus', 'label' => 'Mujer'],
                                    'Unisex' => ['icon' => 'genderless', 'label' => 'Unisex']
                                ];
                                
                                foreach($generos as $value => $genero): 
                                    $is_active = ($filters['genero'] == $value);
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
                        
                    </div>
                </aside>
                
                <!-- ========== PRODUCTOS GRID ========== -->
                <main class="col-lg-9 col-md-8 col-12 ps-lg-4">
                    
                    <!-- Barra superior -->
                    <div class="shop-topbar modern-topbar">
                        <div class="topbar-left topbar-left-mobile">
                            <h1 class="shop-title">
                                <i class="fa fa-store"></i> Catálogo
                            </h1>
                        </div>
                        <div class="topbar-right topbar-right-mobile">
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
                            <div class="sort-dropdown hide-on-mobile">
                                <button class="btn-sort" id="btnSort">
                                    <i class="fa fa-sort-amount-down"></i>
                                    <span>Ordenar</span>
                                </button>
                                <div class="sort-menu" id="sortMenu">
                                    <button data-filter-type="ordenar" data-filter-value="newest" 
                                        class="<?= (!isset($filters['ordenar']) || $filters['ordenar'] == 'newest') ? 'active' : '' ?>">
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
                                                
                    <!-- Navbar de categorías fuera del topbar -->
                    <?php include 'includes/categories-navbar.php'; ?>
                    <link rel="stylesheet" href="public/assets/css/components/categories-navbar.css?v=1.0">
                    
                    <!-- Grid de productos -->
                    <div class="products-grid-modern" id="products-container"></div>
                    
                </main>
                
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    
    <!-- Core Scripts -->
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

    <!-- Shop Scripts - ARQUITECTURA V3.0 OPTIMIZADA -->
    
    <!-- 0. Image Adapter: PRIMERO - Ajusta altura de tarjetas (GLOBAL) -->
    <script src="public/assets/js/components/product-image-adapter.js?v=1.0"></script>
    
    <!-- 0.5. Masonry Layout: ANTES del product loader para estar listo -->
    <script src="public/assets/js/shop/masonry-layout.js?v=1.3"></script>
    
    <!-- 1. Product Loader: Carga productos (ÚNICO responsable) -->
    <script src="public/assets/js/shop/product-loader.js?v=3.1"></script>
    
    <!-- 2. Categories Navbar: Maneja navbar, actualiza estado -->
    <script src="public/assets/js/header-globals/categories-navbar.js?v=3.0"></script>
    
    <!-- 3. Shop Filters: Maneja sidebar, actualiza estado -->
    <script src="public/assets/js/shop/shop-filters.js?v=3.0"></script>
    <script src="public/assets/js/shop/shop-filters-mobile.js?v=3.0"></script>

    
    <!-- 4. Otros scripts auxiliares -->
    <script src="public/assets/js/shop/search-live.js?v=2.0"></script>
    
    <!-- 5. Touch handler para móviles (GLOBAL) -->
    <script src="public/assets/js/components/product-touch-handler.js?v=1.0"></script>
    
    
    <!-- Fix Sidebar Visibility -->
    <script>
        (function() {
            'use strict';
            
            // ✅ FIX: Asegurar visibilidad del sidebar original solo en desktop
            function ensureSidebarVisibility() {
                // Solo aplicar en desktop (>= 992px)
                if (window.innerWidth < 992) {
                    return; // En móvil, el sidebar se maneja con el clon
                }
                
                const sidebar = document.querySelector('#shopFilters'); // Solo el original
                const sidebarParent = document.querySelector('.col-lg-3');
                
                if (sidebar) {
                    sidebar.style.cssText = `
                        opacity: 1 !important;
                        visibility: visible !important;
                        display: block !important;
                        position: sticky !important;
                        top: 100px !important;
                        transform: none !important;
                    `;
                    
                    if (sidebarParent) {
                        sidebarParent.style.cssText = `
                            display: block !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                        `;
                    }
                }
            }
            
            // Ejecutar SOLO cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', ensureSidebarVisibility);
            } else {
                ensureSidebarVisibility();
            }
            
            // Ejecutar SOLO en resize con throttle (máximo 1 vez cada 300ms)
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(ensureSidebarVisibility, 300);
            }, { passive: true });
        })();
    </script>
    
    <!-- FIX: Limpiar offcanvas-active al cargar la página -->
    <script>
        // Ejecutar inmediatamente Y después del DOM load
        function cleanOffcanvasState() {
            document.body.classList.remove('offcanvas-active');
            
            // Cerrar todos los overlays y wrappers
            document.querySelectorAll('.offcanvas-menu-overlay, .filters-menu-overlay, .offcanvas-menu-wrapper, .filters-menu-wrapper').forEach(el => {
                el.classList.remove('active');
            });
            
            // Forzar pointer-events en el contenido principal
            const shopModern = document.querySelector('.shop-modern');
            const breadcrumb = document.querySelector('.breadcrumb-option');
            const productsGrid = document.querySelector('.products-grid-modern');
            
            if (shopModern) shopModern.style.pointerEvents = 'auto';
            if (breadcrumb) breadcrumb.style.pointerEvents = 'auto';
            if (productsGrid) productsGrid.style.pointerEvents = 'auto';
        }
        
        // Ejecutar inmediatamente
        cleanOffcanvasState();
        
        // Ejecutar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', cleanOffcanvasState);
        } else {
            cleanOffcanvasState();
        }
        
        // Ejecutar después de un pequeño delay para asegurar
        setTimeout(cleanOffcanvasState, 100);
    </script>
</body>


</html>

