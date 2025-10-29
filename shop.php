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
    
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- Shop Modern CSS -->
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=3.0">
    <link rel="stylesheet" href="public/assets/css/shop/shop-filters-modern.css?v=2.0">
    <link rel="stylesheet" href="public/assets/css/shop/fix-grid.css?v=2.0">
    <link rel="stylesheet" href="public/assets/css/shop/empty-state.css?v=2.0">
    
    <!-- Shop Mobile Optimization - DESDE CERO - 2 productos por fila -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-mobile-clean.css?v=2.0">
    <link rel="stylesheet" href="public/assets/css/shop/mobile-grid-fix.css?v=2.0">
    
    <!-- noUiSlider CSS - Desactivado (filtro de precio removido) -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css"> -->
    
    <!-- Modals -->
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css">
    <link rel="stylesheet" href="public/assets/css/modals-dark-mode.css">
    
    <!-- Dark Mode -->
    <link rel="stylesheet" href="public/assets/css/dark-mode.css?v=<?= time() ?>">
    
    <link rel="stylesheet" href="public/assets/css/badges-override.css?v=<?= time() ?>">
    
    <!-- ✅ FIX: Eliminar barra blanca al lado del scrollbar -->
    <link rel="stylesheet" href="public/assets/css/fix-white-bar.css?v=1.0" type="text/css">
    
    <!-- SIDEBAR NORMAL - SIN STICKY -->
    
    <!-- HEADER FIX - SOLO PARA HEADER -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-header-fix.css?v=<?= time() ?>">
    
    <!-- Config Script -->
    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        window.SHOP_FILTERS = <?= json_encode($filters) ?>;
        
        // Verificar y corregir protocolo si es necesario
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');}
    </script>
     <style>
        @media (max-width: 768px) {
            /* 🚫 OCULTAR título Catálogo y contador de productos */
            .topbar-left-mobile {
                display: none !important;
            }
            
            /* ✅ Buscador y Ordenar lado a lado */
            .modern-topbar {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 10px !important;
                padding: 12px 15px !important;
            }
            
            .topbar-right-mobile {
                display: flex !important;
                flex: 1 !important;
                gap: 10px !important;
                align-items: center !important;
            }
            
            /* Buscador ocupa espacio flexible */
            .search-box-modern {
                flex: 1 !important;
                margin: 0 !important;
            }
            
            .search-box-modern input {
                font-size: 13px !important;
                padding: 10px 35px 10px 12px !important;
            }
            
            /* Botón ordenar más compacto */
            .sort-dropdown {
                flex-shrink: 0 !important;
            }
            .btn-sort {
                padding: 10px 14px !important;
                font-size: 13px !important;
            }
            
            .btn-sort span {
                display: inline !important;
            }
        }
        
        @media (max-width: 400px) {
            .btn-sort span {
                display: none !important;
            }
            .btn-sort {
                padding: 10px 12px !important;
            }
        }
        
        /* 🔘 Botón flotante de filtros - Responsive */
        @media (max-width: 991px) {
            #btnMobileFilters {
                display: flex !important;
            }
        }
        
        @media (min-width: 992px) {
            #btnMobileFilters {
                display: none !important;
            }
        }
        
        /* Efecto hover en el botón - Shadow oscura */
        #btnMobileFilters:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4) !important;
        }
        
        #btnMobileFilters:active {
            transform: scale(0.95);
        }
        
        /* Dark mode - sombra más suave */
        body.dark-mode #btnMobileFilters {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5) !important;
        }
        body.dark-mode #btnMobileFilters:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.6) !important;
        }
        
        /* 🎨 Overlay y Wrapper de Filtros (EXACTO AL OFFCANVAS) */
        .filters-menu-overlay {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .filters-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .filters-menu-wrapper {
            position: fixed;
            left: -100%;
            top: 0;
            width: 90%;
            max-width: 360px;
            height: 100%;
            background: #ffffff;
            padding: 0;
            z-index: 9999;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
        }
        
        .filters-menu-wrapper.active {
            left: 0;
        }
        
        body.dark-mode .filters-menu-wrapper {
            background: #1e1e1e;
        }
        
        /* Sidebar dentro del wrapper - con padding */
        .filters-menu-wrapper .modern-sidebar {
            padding: 20px 15px !important;
            margin: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Botón cerrar filtros */
        .filters__close {
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 15px;
            background: #ffffff;
            z-index: 10;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            font-size: 32px;
            font-weight: 300;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .filters__close::before {
            content: '×';
            display: block;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .filters__close:hover::before {
            background: rgba(0, 0, 0, 0.1);
            transform: rotate(90deg);
        }
        
        body.dark-mode .filters__close {
            background: #1e1e1e;
            border-bottom-color: #333;
            color: #fff;
        }
        
        body.dark-mode .filters__close::before {
            background: rgba(255, 255, 255, 0.1);
        }
        
        body.dark-mode .filters__close:hover::before {
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Ocultar en desktop */
        @media (min-width: 992px) {
            .filters-menu-overlay,
            .filters-menu-wrapper {
                display: none !important;
            }
        }
        
        /* 📱 TARJETAS MÓVIL - Reducir espaciado interno (precios, texto, etc) */
        @media (max-width: 768px) {
            /* Contenido de texto: menos padding */
            .product-content-modern {
                padding: 8px 8px 10px 8px !important;
            }
            
            /* Título: menos margen inferior */
            .product-title-modern {
                margin-bottom: 4px !important;
            }
            
            /* Categoría: menos margen */
            .product-category-modern {
                margin-bottom: 4px !important;
            }
            
            /* Rating: menos margen */
            .product-rating-modern {
                margin-bottom: 6px !important;
            }
            
            /* Precios: menos margen y gap */
            .product-price-modern {
                margin-bottom: 6px !important;
                gap: 4px !important;
            }
            
            /* Stock badge: menos margen */
            .stock-badge {
                margin-bottom: 6px !important;
            }
            
            /* Botón agregar al carrito: menos padding vertical */
            .add-to-cart-btn-modern {
                padding: 9px 12px !important;
                margin-top: 6px !important;
            }
        }
    </style>
</head>

<body class="shop-page">
    <!-- Offcanvas Menu -->
    <?php include 'includes/offcanvas-menu.php'; ?>
    
    <!-- Header con modales -->
    <?php include 'includes/header-section.php'; ?>
    
    <!-- Breadcrumb -->
    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Botón de filtros móvil - Diseño del offcanvas -->
    <button class="btn-mobile-filters" id="btnMobileFilters" aria-label="Abrir filtros" style="position: fixed; bottom: 20px; right: 20px; z-index: 999; width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; font-size: 18px;">
        <i class="fa fa-filter"></i>
        <span class="filter-count" id="filterCount" style="position: absolute; top: -4px; right: -4px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; border: 2px solid white;">0</span>
    </button>
    
    <!-- Overlay y Wrapper de Filtros Móvil (patrón offcanvas) -->
    <div class="filters-menu-overlay"></div>
    <div class="filters-menu-wrapper">
        <div class="filters__close" title="Cerrar filtros"></div>
    </div>
    
    <!-- Main Shop Section -->
    <section class="shop-modern spad">
        <div class="container-fluid px-lg-5">
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
                                <small style="font-size: 11px; opacity: 0.7; margin-left: 4px;">(Múltiple)</small>
                            </h3>
                            <div class="filter-buttons">
                                <?php foreach($categorias as $cat): 
                                    $is_active = (is_array($filters['categoria']) && in_array($cat['id_categoria'], $filters['categoria']));
                                ?>
                                    <button class="filter-chip <?= $is_active ? 'active' : '' ?>" 
                                            data-filter-type="categoria" 
                                            data-filter-value="<?= htmlspecialchars($cat['id_categoria'], ENT_QUOTES) ?>"
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
                    <div class="shop-topbar modern-topbar" data-aos="fade-left">
                        <div class="topbar-left topbar-left-mobile">
                            <h1 class="shop-title">
                                <i class="fa fa-store"></i> Catálogo
                            </h1>
                            <span class="results-count" id="results-count">
                                <?= count($productos) ?> producto<?= count($productos) != 1 ? 's' : '' ?> encontrado<?= count($productos) != 1 ? 's' : '' ?>
                            </span>
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
                                    <div class="no-products-found" data-aos="fade-up">
                                        <div class="empty-state-icon">
                                            <i class="fa fa-shopping-bag"></i>
                                            <div class="icon-circle"></div>
                                        </div>
                                        <h2 class="empty-state-title">No se encontraron productos</h2>
                                        <p class="empty-state-description">
                                            Intenta ajustar los filtros o buscar algo diferente.<br>
                                            Explora nuestro catálogo completo para descubrir productos increíbles.
                                        </p>
                                        <div class="empty-state-actions">
                                            <button class="btn-clear-filters" onclick="limpiarFiltros()">
                                                <i class="fa fa-redo"></i>
                                                <span>Limpiar filtros</span>
                                            </button>
                                            <a href="shop.php" class="btn-view-all">
                                                <i class="fa fa-th"></i>
                                                <span>Ver todos los productos</span>
                                            </a>
                                        </div>
                                        <div class="empty-state-suggestions">
                                            <p class="suggestions-title">Sugerencias:</p>
                                            <ul class="suggestions-list">
                                                <li><i class="fa fa-check-circle"></i> Verifica la ortografía de tu búsqueda</li>
                                                <li><i class="fa fa-check-circle"></i> Usa términos más generales</li>
                                                <li><i class="fa fa-check-circle"></i> Prueba con menos filtros activos</li>
                                            </ul>
                                        </div>
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
    <script src="public/assets/js/bootstrap.min.js"></script>
    <!-- noUiSlider desactivado (filtro de precio removido) -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script> -->
    
    <!-- Header Handler - Actualización en tiempo real -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Shop Scripts -->
    <script src="public/assets/js/shop/shop-filters.js?v=2.0"></script>
    <script src="public/assets/js/shop/search-live.js?v=2.0"></script>
    
    <!-- Fix Grid Móvil - 2 columnas FORZADO -->
    <script>
    (function() {
        'use strict';
        
        function forceMobileGrid() {
            if (window.innerWidth > 767) return;
            
            
            const rows = document.querySelectorAll('.products-grid-modern .row');
            rows.forEach(row => {
                row.style.cssText = 'display: flex !important; flex-wrap: wrap !important; margin: 0 -8px !important;';
                
                const columns = row.querySelectorAll('div[class*="col"]');
                
                columns.forEach((col, index) => {
                    // Remover todas las clases de Bootstrap
                    col.className = col.className.replace(/col-\w+-\d+/g, '').trim();
                    
                    // Agregar solo col-6
                    if (!col.classList.contains('col-6')) {
                        col.classList.add('col-6');
                    }
                    
                    // Forzar estilos inline
                    col.style.cssText = 'flex: 0 0 50% !important; max-width: 50% !important; width: 50% !important; padding: 0 8px !important; margin-bottom: 16px !important; box-sizing: border-box !important;';
                    
                });
            });
            
        }
        
        // Ejecutar múltiples veces para asegurar
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', forceMobileGrid);
        } else {
            forceMobileGrid();
        }
        
        window.addEventListener('load', forceMobileGrid);
        setTimeout(forceMobileGrid, 500);
        setTimeout(forceMobileGrid, 1000);
        
        // Observar cambios en el DOM
        const observer = new MutationObserver(forceMobileGrid);
        const container = document.querySelector('#products-container');
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    })();
    </script>
    
    <!-- Fix botón filtros móvil - Patrón Offcanvas -->
    <script>
    (function() {
        'use strict';
        
        function init() {
            try {
                initMobileFiltersButton();
            } catch (error) {
                console.error('Error al inicializar los filtros móviles:', error);
            }
        }

        function forceCloseFilters() {
            const wrapper = document.querySelector('.filters-menu-wrapper');
            const overlay = document.querySelector('.filters-menu-overlay');
            if (wrapper) wrapper.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Funciones para abrir/cerrar filtros (igual que offcanvas)
        function openFilters() {
            const wrapper = document.querySelector('.filters-menu-wrapper');
            const overlay = document.querySelector('.filters-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeFilters() {
            const sidebar = document.querySelector('.modern-sidebar');
            const overlay = document.querySelector('.filters-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
        
        function initMobileFiltersButton() {
            
            const btnMobileFilters = document.getElementById('btnMobileFilters');
            const sidebar = document.querySelector('.modern-sidebar');
            
            if (!btnMobileFilters || !sidebar) {
                setTimeout(initMobileFiltersButton, 100);
                return;
            }
            
            
            // Mostrar botón solo en móvil
            if (window.innerWidth <= 991) {
                btnMobileFilters.style.display = 'flex';
            }
            
            // Crear overlay si no existe
            let overlay = document.querySelector('.filters-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'filters-overlay';
                document.body.appendChild(overlay);
            }
            
            // Preparar estilos del sidebar para móvil (igual que offcanvas)
            sidebar.classList.add('mobile-filters-sidebar');
            
            // Event: Click en botón
            btnMobileFilters.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openFilters();
            });
            
            // Event: Click en touchstart para móviles
            btnMobileFilters.addEventListener('touchstart', function(e) {
                e.preventDefault();
                openFilters();
            }, { passive: false });
            
            // Event: Click en overlay para cerrar
            overlay.addEventListener('click', closeFilters);
            
            // Event: Responsive
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991) {
                    btnMobileFilters.style.display = 'none';
                    forceCloseFilters();
                } else {
                    btnMobileFilters.style.display = 'flex';
                }
            });
            
        }
        
        // Iniciar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
        
        // También probar inmediatamente
        setTimeout(init, 100);
        setTimeout(init, 500);
    })();
    </script>
    
    <!-- Filtros Móviles - Patrón Offcanvas -->
    <script src="public/assets/js/shop-filters-mobile.js?v=<?= time() ?>"></script>
    
    <!-- Global Scripts -->
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/dark-mode.js"></script>
    <script src="public/assets/js/scroll-position-memory.js"></script>
    <script src="public/assets/js/image-color-extractor.js"></script>
    
    <!-- Fix Modal Scrollbar - PREVENIR BARRA LATERAL -->
    <script src="public/assets/js/fix-modal-scrollbar.js"></script>
    
    <!-- AOS Animations -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50,
            // Desactivar AOS en móviles y sidebar
            disable: function() {
                return window.innerWidth < 768 || (this.classList && this.classList.contains('modern-sidebar'));
            }
        });
        
        // Remover AOS del sidebar inmediatamente
        setTimeout(function() {
            const sidebar = document.querySelector('.modern-sidebar');
            if (sidebar) {
                sidebar.removeAttribute('data-aos');
                sidebar.classList.remove('aos-init', 'aos-animate');
                sidebar.style.transform = 'none';
                sidebar.style.animation = 'none';
            }
        }, 100);
        
        // Sticky Header Scroll Effect
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header');
            let lastScroll = 0;
            
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                lastScroll = currentScroll;
            });
        });
    </script>
    
    <!-- Core Scripts -->
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script>
        window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.3.1.min.js"><\/script>');
    </script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/offcanvas-menu.js"></script>
    
    <!-- Shop Scripts -->
    <script src="public/assets/js/shop/shop-filters.js"></script>
    <script src="public/assets/js/shop/search-live.js"></script>
    
    <!-- Additional Scripts -->
    </script>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
    
    <!-- Core Scripts - Carga secuencial -->
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script>
        // Verificar jQuery y cargar desde CDN si falla la carga local
        if (typeof jQuery === 'undefined') {
            document.write('<script src="https://code.jquery.com/jquery-3.3.1.min.js"><\/script>');
        }
        
        // Esperar a que jQuery esté listo antes de cargar otros scripts
        function loadScripts() {
            // Cargar Bootstrap
            var bootstrap = document.createElement('script');
            bootstrap.src = 'public/assets/js/bootstrap.min.js';
            bootstrap.onload = function() {
                // Después de Bootstrap, cargar offcanvas
                var offcanvas = document.createElement('script');
                offcanvas.src = 'public/assets/js/offcanvas-menu.js';
                document.body.appendChild(offcanvas);
                
                // Después cargar los demás scripts
                [
                    'public/assets/js/shop/shop-filters.js',
                    'public/assets/js/shop/search-live.js',
                    'public/assets/js/cart-favorites-handler.js',
                    'public/assets/js/dark-mode.js',
                    'public/assets/js/scroll-position-memory.js',
                    'public/assets/js/image-color-extractor.js',
                    'public/assets/js/fix-modal-scrollbar.js',
                    'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js',
                    'public/assets/js/real-time-updates.js'
                ].forEach(function(src) {
                    var script = document.createElement('script');
                    script.src = src;
                    script.async = false;  // Mantener orden de carga
                    document.body.appendChild(script);
                    
                    // Inicializar AOS cuando se cargue
                    if (src.includes('aos')) {
                        script.onload = function() {
                            if (typeof AOS !== 'undefined') {
                                AOS.init({
                                    duration: 600,
                                    easing: 'ease-out-cubic',
                                    once: true,
                                    offset: 50,
                                    disable: window.innerWidth < 768
                                });
                            }
                        };
                    }
                });
            };
            document.body.appendChild(bootstrap);
        }

        // Cargar scripts cuando jQuery esté listo
        if (window.jQuery) {
            jQuery(document).ready(loadScripts);
        } else {
            document.addEventListener('DOMContentLoaded', function checkJQuery() {
                if (window.jQuery) {
                    jQuery(document).ready(loadScripts);
                } else {
                    setTimeout(checkJQuery, 50);
                }
            });
        }
    </script>
</body>
</html>
