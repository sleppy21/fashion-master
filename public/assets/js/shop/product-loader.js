/**
 * PRODUCT LOADER - Cargador Centralizado de Productos
 * @version 3.0
 * 
 * RESPONSABILIDAD ÚNICA: Cargar productos desde el backend
 * NO maneja UI de filtros, solo carga y renderiza productos
 */

(function() {
    'use strict';
    
    // =============================================
    // ESTADO GLOBAL DE FILTROS (ÚNICA FUENTE DE VERDAD)
    // =============================================
    window.ProductFilters = {
        categorias: [],
        genero: null,
        marca: null,
        precio_min: 0,
        precio_max: 10000,
        buscar: '',
        ordenar: 'newest'
    };
    
    // =============================================
    // FUNCIÓN PRINCIPAL: CARGAR PRODUCTOS
    // =============================================
    window.loadProducts = function() {
        console.log('🔄 Cargando productos con filtros:', window.ProductFilters);
        
        const productsContainer = document.getElementById('products-container');
        if (!productsContainer) {
            console.warn('⚠️ Contenedor de productos no encontrado');
            return;
        }
        
        // Construir parámetros de URL
        const params = new URLSearchParams();
        
        if (window.ProductFilters.categorias && window.ProductFilters.categorias.length > 0) {
            window.ProductFilters.categorias.forEach(cat => {
                params.append('c[]', cat);
            });
        }
        
        if (window.ProductFilters.genero) params.append('g', window.ProductFilters.genero);
        if (window.ProductFilters.marca) params.append('m', window.ProductFilters.marca);
        if (window.ProductFilters.precio_min > 0) params.append('pmin', window.ProductFilters.precio_min);
        if (window.ProductFilters.precio_max < 10000) params.append('pmax', window.ProductFilters.precio_max);
        if (window.ProductFilters.buscar) params.append('q', window.ProductFilters.buscar);
        params.append('sort', window.ProductFilters.ordenar);
        
        // Actualizar URL del navegador
        const newUrl = params.toString() 
            ? `${window.location.pathname}?${params.toString()}`
            : window.location.pathname;
        window.history.pushState({}, '', newUrl);
        
        // Mostrar loading
        productsContainer.innerHTML = `
            <div class="col-12">
                <div class="loading-products">
                    <div class="modern-loader">
                        <div class="modern-loader-ring"></div>
                        <div class="modern-loader-ring"></div>
                        <div class="modern-loader-ring"></div>
                        <i class="modern-loader-icon fa fa-shopping-bag"></i>
                    </div>
                    <p class="loading-text">
                        Cargando productos<span class="loading-dots"><span></span><span></span><span></span></span>
                    </p>
                </div>
            </div>
        `;
        
        // Hacer petición AJAX
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(`${baseUrl}/app/actions/get_products_filtered.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    productsContainer.innerHTML = data.html;
                    
                    // Disparar eventos INMEDIATAMENTE (síncronos)
                    document.dispatchEvent(new CustomEvent('productsLoaded', {
                        detail: { count: data.count || 0 }
                    }));
                    
                    // Disparar evento para Masonry INMEDIATAMENTE
                    document.dispatchEvent(new CustomEvent('productsUpdated'));
                    
                    console.log('✅ Productos cargados:', data.count || 0);
                    
                    // Re-inicializar touch handler en móvil (sin delay)
                    if (typeof window.reinitProductTouchHandler === 'function') {
                        window.reinitProductTouchHandler();
                    }
                    
                } else if (data.success && data.count === 0) {
                    showNoResults();
                } else {
                    showNoResults();
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar productos:', error);
                showError();
            });
    };
    
    // =============================================
    // FUNCIONES AUXILIARES
    // =============================================
    
    function showNoResults() {
        const productsContainer = document.getElementById('products-container');
        if (productsContainer) {
            productsContainer.innerHTML = `
                <div class="col-12">
                    <div class="no-products-found">
                        <div class="no-products-animation">
                            <div class="empty-box">
                                <div class="box-lid"></div>
                                <div class="box-body">
                                    <i class="fa fa-search"></i>
                                </div>
                                <div class="box-shadow"></div>
                            </div>
                        </div>
                        <h3 class="no-products-title">No se encontraron productos</h3>
                        <p class="no-products-subtitle">Intenta ajustar los filtros o realiza una nueva búsqueda</p>
                        <button class="btn-clear-filters" onclick="clearAllFilters()">
                            <i class="fa fa-refresh"></i> Limpiar filtros
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    function showError() {
        const productsContainer = document.getElementById('products-container');
        if (productsContainer) {
            productsContainer.innerHTML = `
                <div class="col-12">
                    <div class="error-products">
                        <div class="error-animation">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="error-title">Error al cargar productos</h3>
                        <p class="error-subtitle">Por favor, intenta nuevamente</p>
                        <button class="btn-retry" onclick="window.loadProducts()">
                            <i class="fa fa-redo"></i> Reintentar
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    // Función para limpiar todos los filtros
    window.clearAllFilters = function() {
        window.ProductFilters = {
            categorias: [],
            genero: null,
            marca: null,
            precio_min: 0,
            precio_max: 10000,
            buscar: '',
            ordenar: 'newest'
        };
        window.loadProducts();
    };
    
    // =============================================
    // INICIALIZAR FILTROS DESDE URL
    // =============================================
    
    function initFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Categorías (pueden ser múltiples)
        const cats = urlParams.getAll('c[]');
        if (cats.length > 0) {
            window.ProductFilters.categorias = cats;
        } else {
            const singleCat = urlParams.get('c');
            if (singleCat) {
                window.ProductFilters.categorias = [singleCat];
            }
        }
        
        // Género
        const genero = urlParams.get('g');
        if (genero) window.ProductFilters.genero = genero;
        
        // Marca
        const marca = urlParams.get('m');
        if (marca) window.ProductFilters.marca = parseInt(marca);
        
        // Precio
        const pmin = urlParams.get('pmin');
        const pmax = urlParams.get('pmax');
        if (pmin) window.ProductFilters.precio_min = parseFloat(pmin);
        if (pmax) window.ProductFilters.precio_max = parseFloat(pmax);
        
        // Búsqueda
        const buscar = urlParams.get('q');
        if (buscar) window.ProductFilters.buscar = buscar;
        
        // Ordenamiento
        const sort = urlParams.get('sort');
        if (sort) window.ProductFilters.ordenar = sort;
        
        console.log('📍 Filtros inicializados desde URL:', window.ProductFilters);
    }
    
    // =============================================
    // AUTO-INICIO
    // =============================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initFiltersFromURL();
            console.log('✅ ProductLoader inicializado');
            // 🔥 CARGAR PRODUCTOS AUTOMÁTICAMENTE AL INICIO
            window.loadProducts();
        });
    } else {
        initFiltersFromURL();
        console.log('✅ ProductLoader inicializado');
        // 🔥 CARGAR PRODUCTOS AUTOMÁTICAMENTE AL INICIO
        window.loadProducts();
    }
    
})();
