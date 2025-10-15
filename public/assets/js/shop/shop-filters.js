/**
 * SHOP FILTERS MODULE
 * Sistema modular de filtros para la tienda
 * @version 2.0 - Modernizado 2025
 */

(function() {
    'use strict';
    
    // Estado global de filtros
    const FiltersState = {
        categoria: null,
        genero: null,
        marca: null,
        precio_min: 0,
        precio_max: 10000,
        buscar: '',
        ordenar: 'newest'
    };
    
    /**
     * Inicializar filtros desde URL
     */
    function initFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        FiltersState.categoria = urlParams.get('c') || urlParams.get('categoria') || null;
        FiltersState.genero = urlParams.get('g') || urlParams.get('genero') || null;
        FiltersState.marca = urlParams.get('m') || urlParams.get('marca') || null;
        FiltersState.precio_min = parseFloat(urlParams.get('pmin') || urlParams.get('precio_min') || 0);
        FiltersState.precio_max = parseFloat(urlParams.get('pmax') || urlParams.get('precio_max') || 10000);
        FiltersState.buscar = urlParams.get('q') || urlParams.get('buscar') || '';
        FiltersState.ordenar = urlParams.get('sort') || 'newest';
        
        console.log('✅ Filtros inicializados desde URL:', FiltersState);
    }
    
    /**
     * Aplicar filtro específico
     * @param {string} tipo - Tipo de filtro (categoria, genero, marca, etc)
     * @param {any} valor - Valor del filtro
     */
    window.aplicarFiltro = function(tipo, valor) {
        console.log(`🔍 Aplicando filtro: ${tipo} = ${valor}`);
        
        // Actualizar estado
        FiltersState[tipo] = valor;
        
        // Actualizar UI
        actualizarBotonesActivos(tipo, valor);
        
        // Aplicar filtros con AJAX
        aplicarFiltrosAjax();
    };
    
    /**
     * Actualizar botones activos visualmente
     * @param {string} tipo - Tipo de filtro
     * @param {any} valor - Valor seleccionado
     */
    function actualizarBotonesActivos(tipo, valor) {
        const botones = document.querySelectorAll(`[data-filter-type="${tipo}"]`);
        
        botones.forEach(boton => {
            const botonValor = boton.getAttribute('data-filter-value');
            
            if (botonValor == valor) {
                boton.classList.add('active');
            } else {
                boton.classList.remove('active');
            }
        });
    }
    
    /**
     * Aplicar filtros con AJAX y actualizar productos
     */
    function aplicarFiltrosAjax() {
        console.log('🚀 Aplicando filtros con AJAX...');
        
        // Construir parámetros de URL
        const params = new URLSearchParams();
        
        if (FiltersState.categoria) params.append('c', FiltersState.categoria);
        if (FiltersState.genero) params.append('g', FiltersState.genero);
        if (FiltersState.marca) params.append('m', FiltersState.marca);
        if (FiltersState.precio_min > 0) params.append('pmin', FiltersState.precio_min);
        if (FiltersState.precio_max < 10000) params.append('pmax', FiltersState.precio_max);
        if (FiltersState.buscar) params.append('q', FiltersState.buscar);
        params.append('sort', FiltersState.ordenar);
        
        // Actualizar URL sin recargar página
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({}, '', newUrl);
        
        // Mostrar loading
        const productsContainer = document.getElementById('products-container');
        if (productsContainer) {
            productsContainer.innerHTML = `
                <div class="col-12">
                    <div class="loading-products">
                        <div class="spinner-modern"></div>
                        <p>Cargando productos...</p>
                    </div>
                </div>
            `;
        }
        
        // Hacer petición AJAX
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(`${baseUrl}/app/actions/get_products_filtered.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderProducts(data.products);
                    updateResultsCount(data.products.length);
                } else {
                    showNoResults();
                }
            })
            .catch(error => {
                console.error('❌ Error al filtrar productos:', error);
                showError();
            });
    }
    
    /**
     * Renderizar productos en el DOM
     * @param {Array} products - Array de productos
     */
    function renderProducts(products) {
        const productsContainer = document.getElementById('products-container');
        if (!productsContainer) return;
        
        if (products.length === 0) {
            showNoResults();
            return;
        }
        
        // Limpiar el contenedor y crear nueva row
        productsContainer.innerHTML = '<div class="row"></div>';
        const row = productsContainer.querySelector('.row');
        
        products.forEach((product, index) => {
            const productCard = createProductCard(product, index);
            row.appendChild(productCard);
        });
        
        // Re-inicializar event handlers de carrito y favoritos
        reinitializeProductHandlers();
        
        // Re-inicializar AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    /**
     * Reinicializar event handlers después de renderizar productos
     */
    function reinitializeProductHandlers() {
        // Imágenes clickeables
        document.querySelectorAll('.product-image-clickable').forEach(img => {
            img.addEventListener('click', function() {
                const url = this.getAttribute('data-product-url');
                if (url) window.location.href = url;
            });
        });
        
        // Botones de favoritos
        document.querySelectorAll('.add-to-favorites').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-id');
                if (window.toggleFavorite) {
                    window.toggleFavorite(productId);
                }
            });
        });
        
        // Botones de carrito
        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.getAttribute('data-disabled') === 'true') return;
                const productId = this.getAttribute('data-id');
                if (window.addToCart) {
                    window.addToCart(productId);
                }
            });
        });
    }
    
    /**
     * Crear elemento de tarjeta de producto
     * @param {Object} product - Datos del producto
     * @param {number} index - Índice para animación
     * @returns {HTMLElement}
     */
    function createProductCard(product, index) {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6 col-sm-6';
        
        // Calcular precio con descuento
        const precioOriginal = parseFloat(product.precio_producto);
        const descuento = parseFloat(product.descuento_porcentaje_producto || 0);
        const precioFinal = precioOriginal - (precioOriginal * descuento / 100);
        const tieneDescuento = descuento > 0;
        
        // Stock
        const stock = parseInt(product.stock_actual_producto || 0);
        const sinStock = stock <= 0;
        const stockBajo = stock > 0 && stock <= 5;
        
        // Favorito
        const esFavorito = product.es_favorito || false;
        
        // Rating
        const rating = parseFloat(product.calificacion_promedio || 0);
        const totalResenas = parseInt(product.total_resenas || 0);
        const fullStars = Math.floor(rating);
        const hasHalf = (rating - fullStars) >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalf ? 1 : 0);
        
        let starsHtml = '';
        for (let i = 0; i < fullStars; i++) {
            starsHtml += '<i class="fa fa-star star"></i>';
        }
        if (hasHalf) {
            starsHtml += '<i class="fa fa-star-half-o star"></i>';
        }
        for (let i = 0; i < emptyStars; i++) {
            starsHtml += '<i class="fa fa-star-o star empty"></i>';
        }
        
        // Determinar si es nuevo (últimos 30 días)
        const esNuevo = product.es_nuevo || false;
        
        const imagenUrl = product.url_imagen_producto || 'public/assets/img/shop/default-product.jpg';
        const productUrl = `product-details.php?id=${product.id_producto}`;
        
        col.innerHTML = `
            <div class="product-card-modern" data-product-id="${product.id_producto}" data-aos="fade-up">
                
                <!-- Imagen del producto -->
                <div class="product-image-wrapper">
                    <a href="${productUrl}">
                        <img src="${imagenUrl}" 
                             alt="${product.nombre_producto}"
                             loading="lazy"
                             class="product-image"
                             crossorigin="anonymous">
                    </a>
                    
                    <!-- Badges superiores -->
                    <div class="product-badges">
                        ${sinStock ? '<span class="badge-modern badge-out-of-stock">AGOTADO</span>' : ''}
                        ${!sinStock && tieneDescuento ? `<span class="badge-modern badge-sale">-${Math.round(descuento)}%</span>` : ''}
                        ${!sinStock && !tieneDescuento && esNuevo ? '<span class="badge-modern badge-new">NUEVO</span>' : ''}
                    </div>
                    
                    <!-- Hover con botones circulares -->
                    <ul class="product__hover">
                        <li>
                            <a href="${productUrl}" class="view-details-btn" title="Ver detalles">
                                <span class="icon_search"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" 
                               class="add-to-favorites ${esFavorito ? 'active' : ''}" 
                               data-id="${product.id_producto}"
                               title="${esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos'}">
                                <span class="icon_heart${esFavorito ? '' : '_alt'}"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" 
                               class="add-to-cart" 
                               data-id="${product.id_producto}"
                               ${sinStock ? 'style="opacity:0.5;cursor:not-allowed;" data-disabled="true"' : ''}
                               title="${sinStock ? 'Sin stock' : 'Agregar al carrito'}">
                                <span class="icon_bag_alt"></span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Información del producto -->
                <div class="product-info">
                    <!-- Categoría -->
                    <div class="product-category">
                        ${(product.nombre_categoria || 'GENERAL').toUpperCase()}
                    </div>
                    
                    <!-- Nombre del producto -->
                    <h3 class="product-name">
                        <a href="${productUrl}">
                            ${product.nombre_producto}
                        </a>
                    </h3>
                    
                    <!-- Rating -->
                    ${totalResenas > 0 ? `
                        <div class="product-rating">
                            <div class="stars">
                                ${starsHtml}
                            </div>
                            <span class="rating-count">(${totalResenas})</span>
                        </div>
                    ` : ''}
                    
                    <!-- Precio -->
                    <div class="product-price">
                        <span class="price-current">S/ ${precioFinal.toFixed(2)}</span>
                        ${tieneDescuento ? `
                            <span class="price-original">S/ ${precioOriginal.toFixed(2)}</span>
                            <span class="price-discount">-${Math.round(descuento)}%</span>
                        ` : ''}
                    </div>
                    
                    <!-- Warning de stock bajo -->
                    ${stockBajo && !sinStock ? `
                        <div class="stock-warning">
                            <i class="fa fa-exclamation-circle"></i>
                            ¡Solo quedan ${stock}!
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        return col;
    }
    
    /**
     * Mostrar mensaje de sin resultados
     */
    function showNoResults() {
        const productsContainer = document.getElementById('products-container');
        if (productsContainer) {
            productsContainer.innerHTML = `
                <div class="col-12">
                    <div class="no-results-message">
                        <i class="fa fa-search"></i>
                        <h3>No se encontraron productos</h3>
                        <p>Intenta ajustar los filtros o buscar algo diferente</p>
                        <button class="btn-primary" onclick="limpiarFiltros()">
                            <i class="fa fa-redo"></i> Limpiar filtros
                        </button>
                    </div>
                </div>
            `;
        }
    }
    
    /**
     * Mostrar mensaje de error
     */
    function showError() {
        const productsContainer = document.getElementById('products-container');
        if (productsContainer) {
            productsContainer.innerHTML = `
                <div class="col-12">
                    <div class="error-message">
                        <i class="fa fa-exclamation-triangle"></i>
                        <h3>Error al cargar productos</h3>
                        <p>Por favor, intenta de nuevo más tarde</p>
                    </div>
                </div>
            `;
        }
    }
    
    /**
     * Actualizar contador de resultados
     * @param {number} count - Número de productos
     */
    function updateResultsCount(count) {
        const counter = document.getElementById('results-count');
        if (counter) {
            counter.textContent = `${count} producto${count !== 1 ? 's' : ''} encontrado${count !== 1 ? 's' : ''}`;
        }
    }
    
    /**
     * Limpiar todos los filtros
     */
    window.limpiarFiltros = function() {
        console.log('🧹 Limpiando todos los filtros...');
        
        // Resetear estado
        FiltersState.categoria = null;
        FiltersState.genero = null;
        FiltersState.marca = null;
        FiltersState.precio_min = 0;
        FiltersState.precio_max = 10000;
        FiltersState.buscar = '';
        FiltersState.ordenar = 'newest';
        
        // Resetear UI
        document.querySelectorAll('.filter-chip.active, .filter-btn.active').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Resetear lista de filtros
        document.querySelectorAll('.filter-list-item.active').forEach(item => {
            item.classList.remove('active');
        });
        
        // Resetear botones de ordenamiento
        document.querySelectorAll('#sortMenu button').forEach(btn => {
            btn.classList.remove('active');
            // Activar "Más recientes" por defecto
            if (btn.dataset.filterValue === 'newest') {
                btn.classList.add('active');
            }
        });
        
        // Resetear inputs
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';
        
        // Limpiar búsqueda en vivo si existe
        if (window.LiveSearch && typeof window.LiveSearch.clear === 'function') {
            window.LiveSearch.clear();
        }
        
        // Resetear slider de precio si existe
        if (window.PriceSlider && typeof window.PriceSlider.reset === 'function') {
            window.PriceSlider.reset();
        }
        
        // Limpiar URL sin recargar
        const newUrl = window.location.pathname;
        window.history.replaceState({}, '', newUrl);
        
        // Aplicar filtros (mostrará todos los productos)
        applyFilters();
        
        console.log('✅ Filtros limpiados y productos recargados');
    };
    
    /**
     * Inicializar módulo
     */
    function init() {
        console.log('🎯 Inicializando módulo de filtros...');
        initFiltersFromURL();
        
        // Event delegation para filtros
        document.addEventListener('click', function(e) {
            const filterBtn = e.target.closest('[data-filter-type]');
            if (filterBtn) {
                e.preventDefault();
                const tipo = filterBtn.getAttribute('data-filter-type');
                const valor = filterBtn.getAttribute('data-filter-value');
                aplicarFiltro(tipo, valor);
            }
        });
        
        // Botón flotante de filtros móvil
        const btnMobileFilters = document.getElementById('btnMobileFilters');
        const sidebar = document.querySelector('.modern-sidebar');
        
        if (btnMobileFilters && sidebar) {
            btnMobileFilters.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle sidebar en móvil
                if (sidebar.classList.contains('show-mobile')) {
                    sidebar.classList.remove('show-mobile');
                    document.body.style.overflow = '';
                } else {
                    sidebar.classList.add('show-mobile');
                    document.body.style.overflow = 'hidden';
                }
            });
            
            // Cerrar al hacer click fuera
            document.addEventListener('click', function(e) {
                if (sidebar.classList.contains('show-mobile') && 
                    !sidebar.contains(e.target) && 
                    !btnMobileFilters.contains(e.target)) {
                    sidebar.classList.remove('show-mobile');
                    document.body.style.overflow = '';
                }
            });
        }
        
        // Botón de ordenar (dropdown)
        const btnSort = document.getElementById('btnSort');
        const sortMenu = document.getElementById('sortMenu');
        
        if (btnSort && sortMenu) {
            btnSort.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sortMenu.classList.toggle('show');
            });
            
            // Cerrar al hacer click fuera
            document.addEventListener('click', function(e) {
                if (!btnSort.contains(e.target) && !sortMenu.contains(e.target)) {
                    sortMenu.classList.remove('show');
                }
            });
            
            // Aplicar ordenamiento al hacer click en opción
            sortMenu.querySelectorAll('button[data-filter-type="ordenar"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const valor = this.getAttribute('data-filter-value');
                    aplicarFiltro('ordenar', valor);
                    sortMenu.classList.remove('show');
                });
            });
        }
        
        // Búsqueda en tiempo real (con debounce)
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    FiltersState.buscar = this.value.trim();
                    aplicarFiltrosAjax();
                }, 500); // Esperar 500ms después de que el usuario deje de escribir
            });
        }
        
        console.log('✅ Módulo de filtros iniciado correctamente');
    }
    
    // Iniciar cuando DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
