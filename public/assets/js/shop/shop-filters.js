/**
 * SHOP FILTERS MODULE
 * Sistema modular de filtros para la tienda
 * @version 2.0 - Modernizado 2025
 */

(function() {
    'use strict';
    
    // Estado global de filtros
    const FiltersState = {
        categorias: [], // CAMBIADO: ahora es array para múltiples categorías
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
        
        // Manejar múltiples categorías (c[]=1&c[]=2 o c=1,2,3)
        const categoriasParam = urlParams.getAll('c[]');
        if (categoriasParam.length > 0) {
            FiltersState.categorias = categoriasParam;
        } else {
            const categoriasString = urlParams.get('c') || urlParams.get('categoria');
            FiltersState.categorias = categoriasString ? categoriasString.split(',') : [];
        }
        
        FiltersState.genero = urlParams.get('g') || urlParams.get('genero') || null;
        FiltersState.marca = urlParams.get('m') || urlParams.get('marca') || null;
        FiltersState.precio_min = parseFloat(urlParams.get('pmin') || urlParams.get('precio_min') || 0);
        FiltersState.precio_max = parseFloat(urlParams.get('pmax') || urlParams.get('precio_max') || 10000);
        FiltersState.buscar = urlParams.get('q') || urlParams.get('buscar') || '';
        FiltersState.ordenar = urlParams.get('sort') || 'newest';
        
    }
    
    /**
     * Aplicar filtro específico
     * @param {string} tipo - Tipo de filtro (categoria, genero, marca, etc)
     * @param {any} valor - Valor del filtro
     */
    window.aplicarFiltro = function(tipo, valor) {
        
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
        
        // Construir parámetros de URL
        const params = new URLSearchParams();
        
        // Agregar categorías (múltiples)
        if (FiltersState.categorias && FiltersState.categorias.length > 0) {
            FiltersState.categorias.forEach(cat => {
                params.append('c[]', cat);
            });
        }
        
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
            // Solo agregar si el card se creó correctamente (no null)
            if (productCard) {
                row.appendChild(productCard);
            }
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
     * ✅ FIX: NO agregar event listeners directos para evitar duplicación
     * Los event listeners se manejan con EVENT DELEGATION en real-time-updates.js
     */
    function reinitializeProductHandlers() {
        // ✅ NO agregar listeners aquí - ya están manejados con delegation
        // Event delegation en real-time-updates.js maneja:
        // - Clicks en favoritos (.add-to-favorites)
        // - Clicks en carrito (.add-to-cart)
        // - Clicks en botones del modal
        
        // SOLO re-inicializar AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }
    
    /**
     * Crear elemento de tarjeta de producto
     * @param {Object} product - Datos del producto
     * @param {number} index - Índice para animación
     * @returns {HTMLElement}
     */
    function createProductCard(product, index) {
        // VALIDACIÓN: Asegurar que el producto tenga ID
        if (!product || !product.id_producto) {
            return null;
        }
        
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
                               data-id="${product.id_producto || ''}"
                               ${!product.id_producto ? 'style="display:none;"' : ''}
                               title="${esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos'}">
                                <span class="icon_heart${esFavorito ? '' : '_alt'}"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" 
                               class="add-to-cart" 
                               data-id="${product.id_producto || ''}"
                               ${!product.id_producto ? 'data-disabled="true" style="opacity:0.5;cursor:not-allowed;"' : ''}
                               ${sinStock ? 'style="opacity:0.5;cursor:not-allowed;" data-disabled="true"' : ''}
                               title="${!product.id_producto ? 'Error: ID no disponible' : (sinStock ? 'Sin stock' : 'Agregar al carrito')}">
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
            `;
            
            // Re-inicializar AOS si existe
            if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
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
        
        // Resetear estado - Categorías vacías significa "TODAS"
        FiltersState.categorias = [];
        FiltersState.genero = null;
        FiltersState.marca = null;
        FiltersState.precio_min = 0;
        FiltersState.precio_max = 10000;
        FiltersState.buscar = '';
        FiltersState.ordenar = 'newest';
        
        // Resetear UI - checkboxes y chips de categorías
        document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
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
        
        // Aplicar filtros (array vacío de categorías = TODAS las categorías)
        aplicarFiltrosAjax();
        
    };
    
    /**
     * Inicializar módulo
     */
    function init() {
        initFiltersFromURL();
        
        // Event delegation para filtros de botones
        document.addEventListener('click', function(e) {
            const filterBtn = e.target.closest('[data-filter-type]');
            if (filterBtn && !filterBtn.classList.contains('filter-checkbox')) {
                e.preventDefault();
                const tipo = filterBtn.getAttribute('data-filter-type');
                const valor = filterBtn.getAttribute('data-filter-value');
                const multiSelect = filterBtn.getAttribute('data-multi-select') === 'true';
                
                // Si es multi-selección (categorías)
                if (multiSelect && tipo === 'categoria') {
                    if (filterBtn.classList.contains('active')) {
                        filterBtn.classList.remove('active');
                        FiltersState.categorias = FiltersState.categorias.filter(cat => cat != valor);
                    } else {
                        filterBtn.classList.add('active');
                        if (!FiltersState.categorias.includes(valor)) {
                            FiltersState.categorias.push(valor);
                        }
                    }
                    aplicarFiltrosAjax();
                } else {
                    // Selección única (género, marca, etc.)
                    aplicarFiltro(tipo, valor);
                }
            }
        });
        
        // Event listener para checkboxes de categorías (múltiple selección)
        document.querySelectorAll('.filter-checkbox[data-filter-type="categoria"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const categoriaId = this.getAttribute('data-filter-value');
                
                if (this.checked) {
                    // Agregar categoría al array
                    if (!FiltersState.categorias.includes(categoriaId)) {
                        FiltersState.categorias.push(categoriaId);
                    }
                } else {
                    // Quitar categoría del array
                    FiltersState.categorias = FiltersState.categorias.filter(id => id !== categoriaId);
                }
                
                
                // Aplicar filtros
                aplicarFiltrosAjax();
            });
        });
        
        // Botón flotante de filtros móvil
        const btnMobileFilters = document.getElementById('btnMobileFilters');
        const sidebar = document.querySelector('.modern-sidebar');
        
        if (btnMobileFilters && sidebar) {
            btnMobileFilters.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle sidebar en móvil - SIN AFECTAR SCROLLBAR
                if (sidebar.classList.contains('show-mobile')) {
                    sidebar.classList.remove('show-mobile');
                    // NO modificar overflow del body
                } else {
                    sidebar.classList.add('show-mobile');
                    // NO modificar overflow del body
                }
            });
            
            // Cerrar al hacer click fuera
            document.addEventListener('click', function(e) {
                if (sidebar.classList.contains('show-mobile') && 
                    !sidebar.contains(e.target) && 
                    !btnMobileFilters.contains(e.target)) {
                    sidebar.classList.remove('show-mobile');
                    // NO modificar overflow del body
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
        
    }
    
    // Iniciar cuando DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
