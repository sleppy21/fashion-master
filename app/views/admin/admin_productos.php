<?php
/**
 * VISTA DE GESTIÓN DE PRODUCTOS - DISEÑO MODERNO
 * Sistema unificado con diseño actualizado
 */
?>

<div class="admin-module admin-products-module">
    <!-- Header del módulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de Productos</h2>
                <p class="module-description">Administra el catálogo de productos de la tienda</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="closeStockBubble(); window.showCreateProductModal();">
                <i class="fas fa-plus"></i>
                <span>Nuevo Producto</span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportProducts()">
                <i class="fas fa-download"></i>
                <span>Exportar Excel</span>
            </button>
            <button class="btn-modern btn-info" onclick="showStockReport()">
                <i class="fas fa-chart-bar"></i>
                <span>Reporte Stock</span>
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="module-filters">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-productos" class="search-input" 
                       placeholder="Buscar productos por nombre..." oninput="handleSearchInput()">
                <button class="search-clear" onclick="clearProductSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Categoría</label>
                <select id="filter-category" class="filter-select" onchange="filterProducts()">
                    <option value="">Todas las categorías</option>
                    <!-- Se cargan dinámicamente -->
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select" onchange="filterProducts()">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Stock</label>
                <select id="filter-stock" class="filter-select" onchange="filterProducts()">
                    <option value="">Todo el stock</option>
                    <option value="agotado">Agotado</option>
                    <option value="bajo">Stock bajo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Fecha</label>
                <select id="filter-fecha" class="filter-select" onchange="filterProducts()">
                    <option value="">Todas las fechas</option>
                    <!-- Se cargan dinámicamente -->
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllProductFilters()">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Contenido del módulo -->
    <div class="module-content">
        <div class="data-table-container">
            <div class="table-controls">
                <div class="table-info">
                    <span class="results-count">
                        Mostrando  <span id="showing-end-products">0</span> 
                        de <span id="total-products">0</span> productos
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleView('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleView('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                    <div class="bulk-actions" style="display: none;">
                        <span class="selected-count">0</span> seleccionados
                        <select class="bulk-select" onchange="handleBulkProductAction(this.value); this.value='';">
                            <option value="">Acciones en lote</option>
                            <option value="activar">Activar seleccionados</option>
                            <option value="desactivar">Desactivar seleccionados</option>
                            <option value="eliminar">Eliminar seleccionados</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="data-table-wrapper scrollable-table">
                <table class="data-table products-table">
                    <thead class="table-header">
                        <tr>
                            <th class="sortable" data-sort="id">
                                <span>ID</span>
                            </th>
                            <th class="no-sort">Imagen</th>
                            <th class="sortable" data-sort="nombre">
                                <span>Producto</span>
                            </th>
                            <th class="sortable" data-sort="codigo">
                                <span>Código</span>
                            </th>
                            <th class="sortable" data-sort="categoria">
                                <span>Categoría</span>
                            </th>
                            <th class="sortable" data-sort="marca">
                                <span>Marca</span>
                            </th>
                            <th class="sortable" data-sort="precio">
                                <span>Precio</span>
                            </th>
                            <th class="sortable" data-sort="stock">
                                <span>Stock</span>
                            </th>
                            <th class="sortable" data-sort="estado">
                                <span>Estado</span>
                            </th>
                            <th class="sortable" data-sort="fecha">
                                <span>Fecha</span>
                            </th>
                            <th class="no-sort actions-column">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productos-table-body" class="table-body">
                        <tr class="loading-row">
                            <td colspan="11" class="loading-cell">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <span>Cargando productos...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        Página <span id="current-page-products">1</span> de <span id="total-pages-products">1</span>
                    </span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="first-page-products" onclick="goToFirstPageProducts()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="pagination-btn" id="prev-page-products" onclick="previousPageProducts()">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <div class="pagination-numbers" id="pagination-numbers-products">
                        <!-- Números de página dinámicos -->
                    </div>
                    <button class="pagination-btn" id="next-page-products" onclick="nextPageProducts()">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button class="pagination-btn" id="last-page-products" onclick="goToLastPageProducts()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============ CONFIGURACIÓN ============

// Esperar a que AppConfig esté disponible y luego inicializar CONFIG
function initializeConfig() {
    if (typeof AppConfig !== 'undefined') {
        window.CONFIG = {
            apiUrl: AppConfig.getApiUrl('ProductController.php')
        };
    } else {
        // Fallback si config.js no está cargado
        window.CONFIG = {
            apiUrl: '/fashion-master/app/controllers/ProductController.php'
        };
    }
}

// Inicializar inmediatamente o esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeConfig);
} else {
    initializeConfig();
}

// Variables globales
let isLoading = false;
let productos = [];

// Variables de paginación
let currentPage = 1;
let totalPages = 1;

// Función para obtener la URL correcta de la imagen del producto
function getProductImageUrl(producto, forceCacheBust = false) {
    // Priorizar url_imagen_producto, luego imagen_producto
    let imageUrl = '';
    
    if (producto.url_imagen_producto) {
        // Verificar que no sea una URL de placeholder
        if (producto.url_imagen_producto.includes('placeholder') || 
            producto.url_imagen_producto.includes('via.placeholder')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
        } else {
            imageUrl = producto.url_imagen_producto;
        }
    } else if (producto.imagen_producto) {
        // Si es un nombre de archivo local, construir la ruta completa
        if (!producto.imagen_producto.startsWith('http')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('products/' + producto.imagen_producto) : '/fashion-master/public/assets/img/products/' + producto.imagen_producto;
        } else {
            imageUrl = producto.imagen_producto;
        }
    } else {
        imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
    }
    
    // Agregar cache-busting solo si se solicita explícitamente
    if (forceCacheBust) {
        const cacheBuster = '?v=' + Date.now();
        return imageUrl + cacheBuster;
    }
    
    return imageUrl;
}

// Función auxiliar para mostrar loading en búsqueda
function showSearchLoading() {
    const tbody = document.getElementById('productos-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="11" class="loading-cell">
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <span>Buscando productos...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Función principal para cargar productos con efectos visuales (DEFINICIÓN TEMPRANA)
async function loadProducts(forceCacheBust = false, preserveState = null) {

    // También crear un alias para compatibilidad
    window.loadProductos = loadProducts;
    window.loadProducts = loadProducts; // Asegurar que esté disponible globalmente
    
    isLoading = true;
    
    try {
        // Mostrar loading mejorado
        showSearchLoading();
        
        // Usar estado preservado si está disponible
        if (preserveState) {
            currentPage = preserveState.page || currentPage;
            
            // Restaurar filtros si están disponibles
            if (preserveState.searchTerm && typeof $ !== 'undefined') {
                $('#search-productos').val(preserveState.searchTerm);
            }
            
        }
        
        // Construir URL con parámetros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        // Agregar filtros si existen
        if (typeof $ !== 'undefined') {
            const search = $('#search-productos').val();
            if (search) params.append('search', search);
            
            const category = $('#filter-category').val();
            if (category) params.append('category', category);
            
            const status = $('#filter-status').val();
            if (status !== '') params.append('status', status);
            
            const stock = $('#filter-stock').val();
            if (stock) params.append('stock_filter', stock);
            
            const fecha = $('#filter-fecha').val();
            if (fecha) params.append('fecha', fecha);
        } else {
            // Fallback vanilla JS
            const searchInput = document.getElementById('search-productos');
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            
            const categorySelect = document.getElementById('filter-category');
            if (categorySelect && categorySelect.value) {
                params.append('category', categorySelect.value);
            }
            
            const statusSelect = document.getElementById('filter-status');
            if (statusSelect && statusSelect.value !== '') {
                params.append('status', statusSelect.value);
            }
            
            const stockSelect = document.getElementById('filter-stock');
            if (stockSelect && stockSelect.value) {
                params.append('stock_filter', stockSelect.value);
            }
            
            const fechaSelect = document.getElementById('filter-fecha');
            if (fechaSelect && fechaSelect.value) {
                params.append('fecha', fechaSelect.value);
            }
        }
        
        const finalUrl = `${CONFIG.apiUrl}?${params}`;
        
        const response = await fetch(finalUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            cache: 'no-cache'
        });    
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        
        // Obtener texto crudo
        const responseText = await response.text();
        
        // Parsear JSON de forma segura
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido del servidor');
        }
        
        productos = data.data || [];
        
        displayProducts(productos, forceCacheBust, preserveState);
        updateStats(data.pagination);
        updatePaginationInfo(data.pagination);
        
        // Cargar fechas únicas en el filtro
        loadProductDates(productos);
        
        // Actualizar contador de resultados
        if (data.pagination) {
            updateResultsCounter(productos.length, data.pagination.total_items);
        }
        
        // Destacar producto recién actualizado/creado si está especificado
        // PRESERVAR ESTADO - sin destacado visual para evitar bugs
        if (preserveState) {
            // Restaurar posición de scroll sin animaciones que causen problemas
            if (preserveState.scrollPosition && typeof restoreScrollPosition === 'function') {
                restoreScrollPosition(preserveState.scrollPosition);
            }
        }
        
    } catch (error) {
        const tbody = document.getElementById('productos-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="loading-cell">
                        <div class="loading-content error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Error: ${error.message}</span>
                            <button onclick="loadProducts()" class="btn-modern btn-primary">
                                <i class="fas fa-redo"></i> Reintentar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    } finally {
        isLoading = false;
        
        // Loading overlay eliminado
    }
}

// Asegurar que las funciones estén disponibles globalmente inmediatamente
window.loadProducts = loadProducts;
window.loadProductos = loadProducts;

// Función para cargar categorías en el filtro
async function loadCategories() {
    try {
        const url = `${CONFIG.apiUrl}?action=get_categories`;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            cache: 'no-cache'
        });
                
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        
        // Obtener texto crudo primero
        const responseText = await response.text();
        
        // Parsear JSON de forma segura
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('❌ Error al parsear JSON de categorías:', jsonError);
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        
        if (data.success && data.data) {
            const categorySelect = document.getElementById('filter-category');
            if (categorySelect) {
                // Limpiar opciones existentes excepto "Todas las categorías"
                categorySelect.innerHTML = '<option value="">Todas las categorías</option>';
                
                // Agregar categorías
                data.data.forEach(categoria => {
                    const option = document.createElement('option');
                    option.value = categoria.id_categoria;
                    option.textContent = categoria.nombre_categoria;
                    categorySelect.appendChild(option);
                });
                
            }
        }
    } catch (error) {
        console.error('❌ Error cargando categorías:', error);
    }
}

window.loadCategories = loadCategories;

// Función para cargar fechas únicas de productos en el filtro
function loadProductDates(products) {
    try {
        const fechaSelect = document.getElementById('filter-fecha');
        if (!fechaSelect || !products || products.length === 0) return;
        
        // Extraer fechas únicas (formato YYYY-MM-DD)
        const fechasSet = new Set();
        products.forEach(producto => {
            if (producto.fecha_creacion_producto) {
                // Extraer solo la parte de la fecha (YYYY-MM-DD)
                const fecha = producto.fecha_creacion_producto.split(' ')[0];
                fechasSet.add(fecha);
            }
        });
        
        // Convertir a array y ordenar de más reciente a más antigua
        const fechasUnicas = Array.from(fechasSet).sort((a, b) => b.localeCompare(a));
        
        // Guardar opción seleccionada actual
        const valorActual = fechaSelect.value;
        
        // Limpiar y agregar opción predeterminada
        fechaSelect.innerHTML = '<option value="">Todas las fechas</option>';
        
        // Agregar opciones de fechas
        fechasUnicas.forEach(fecha => {
            const option = document.createElement('option');
            option.value = fecha;
            // Formatear fecha para mostrar (DD/MM/YYYY)
            const [year, month, day] = fecha.split('-');
            option.textContent = `${day}/${month}/${year}`;
            fechaSelect.appendChild(option);
        });
        
        // Restaurar selección si existía
        if (valorActual && fechasUnicas.includes(valorActual)) {
            fechaSelect.value = valorActual;
        }
    } catch (error) {
        console.error('❌ Error cargando fechas:', error);
    }
}

// Función para mostrar productos en tabla
function displayProducts(products, forceCacheBust = false, preserveState = null) {
    // FORZAR vista grid en móvil
    const isMobile = window.innerWidth <= 768;
    const currentView = isMobile ? 'grid' : getCurrentView();
    
    if (currentView === 'grid') {
        // Si está en vista grid, actualizar grid
        displayProductsGrid(products);
        return;
    }
    
    // Si está en vista tabla, actualizar tabla
    const tbody = document.getElementById('productos-table-body');
    
    if (!products || products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-box-open"></i>
                        <span>No se encontraron productos</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map((producto, index) => {
        return `
        <tr oncontextmenu="return false;" ondblclick="editProduct(${producto.id_producto})" style="cursor: pointer;" data-product-id="${producto.id_producto}">
            <td>${producto.id_producto}</td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${getProductImageUrl(producto, forceCacheBust)}', '${(producto.nombre_producto || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${getProductImageUrl(producto, forceCacheBust)}" 
                         alt="Producto" 
                         onerror="this.src='${AppConfig ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg'}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${producto.nombre_producto}</strong>
                </div>
            </td>
            <td>
                <code>${producto.codigo || 'N/A'}</code>
            </td>
            <td>
                ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categoría'}
            </td>
            <td>
                ${producto.nombre_marca || producto.marca_nombre || 'Sin marca'}
            </td>
            <td>
                <div class="price-info">
                    <strong>${producto.precio_formato || '$' + producto.precio_producto}</strong>
                </div>
            </td>
            <td>
                <div class="stock-info">
                    <span class="stock-number ${getStockClass(producto)}">${producto.stock_actual_producto}</span>
                    <small class="stock-status">${producto.estado_stock}</small>
                </div>
            </td>
            <td>
                <span class="status-badge ${producto.estado === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${producto.estado === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${producto.fecha_creacion_formato || producto.fecha_creacion_producto}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${producto.id_producto}, '${(producto.nombre_producto || '').replace(/'/g, "\\'")}', ${producto.stock_actual_producto}, '${producto.estado}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// Función para obtener clase de stock
function getStockClass(producto) {
    const stock = parseInt(producto.stock_actual_producto) || 0;
    const stockMinimo = parseInt(producto.stock_minimo_producto); // Tomar valor directo de BD
    const stockMaximo = parseInt(producto.stock_maximo_producto); // Tomar valor directo de BD
    
    if (stock === 0) return 'stock-agotado';
    if (stockMinimo && stock <= stockMinimo) return 'stock-bajo';
    return 'stock-normal'; // Verde para stock > stock_minimo
}

// Función para actualizar estadísticas
function updateStats(pagination) {
    if (pagination) {
        const { current_page, total_pages, total_items, items_per_page } = pagination;
        const start = ((current_page - 1) * items_per_page) + 1;
        const end = Math.min(current_page * items_per_page, total_items);
        
        const showingStartEl = document.getElementById('showing-start-products');
        const showingEndEl = document.getElementById('showing-end-products');
        const totalProductsEl = document.getElementById('total-products');
        
        if (showingStartEl) showingStartEl.textContent = total_items > 0 ? start : 0;
        if (showingEndEl) showingEndEl.textContent = total_items > 0 ? end : 0;
        if (totalProductsEl) totalProductsEl.textContent = total_items;
    }
}

// Función para actualizar información de paginación
function updatePaginationInfo(pagination) {
    if (pagination) {
        currentPage = pagination.current_page || 1;
        totalPages = pagination.total_pages || 1;
        
        // Actualizar elementos de paginación si existen
        const currentPageEl = document.getElementById('current-page-products');
        const totalPagesEl = document.getElementById('total-pages-products');
        
        if (currentPageEl) currentPageEl.textContent = currentPage;
        if (totalPagesEl) totalPagesEl.textContent = totalPages;
        
        // Actualizar botones de paginación si existen
        const firstBtn = document.querySelector('[onclick="goToFirstPageProducts()"]');
        const prevBtn = document.querySelector('[onclick="previousPageProducts()"]');
        const nextBtn = document.querySelector('[onclick="nextPageProducts()"]');
        const lastBtn = document.querySelector('[onclick="goToLastPageProducts()"]');
        
        if (firstBtn) firstBtn.disabled = currentPage <= 1;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (lastBtn) lastBtn.disabled = currentPage >= totalPages;
    }
}

// Función de filtrado mejorada con jQuery
function filterProducts() {
    if (typeof $ === 'undefined') {
        return filterProductsVanilla();
    }
    
    const search = $('#search-productos').val() || '';
    const category = $('#filter-category').val() || '';
    const status = $('#filter-status').val() || '';
    const stock = $('#filter-stock').val() || '';
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset página actual
    currentPage = 1;
    
    // Recargar productos con filtros
    loadProducts();
}

// Función de filtrado con vanilla JS como fallback
function filterProductsVanilla() {
    const searchInput = document.getElementById('search-productos');
    const categorySelect = document.getElementById('filter-category');
    const statusSelect = document.getElementById('filter-status');
    const stockSelect = document.getElementById('filter-stock');
    
    const search = searchInput ? searchInput.value || '' : '';
    const category = categorySelect ? categorySelect.value || '' : '';
    const status = statusSelect ? statusSelect.value || '' : '';
    const stock = stockSelect ? stockSelect.value || '' : '';
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset página actual
    currentPage = 1;
    
    // Recargar productos con filtros
    loadProducts();
}

// Función para manejar búsqueda en tiempo real con jQuery
let searchTimeout;
function handleSearchInput() {
    clearTimeout(searchTimeout);
    
    // Mostrar indicador visual de búsqueda
    if (typeof $ !== 'undefined') {
        const searchIcon = $('.search-icon');
        if (searchIcon.length) {
            searchIcon.removeClass('fa-search').addClass('fa-spinner fa-spin');
        }
        
        searchTimeout = setTimeout(() => {
            // Restaurar icono
            if (searchIcon.length) {
                searchIcon.removeClass('fa-spinner fa-spin').addClass('fa-search');
            }
            filterProducts();
        }, 300); // Reducido para mejor responsividad
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-productos');
        const searchIcon = searchInput?.parentElement?.querySelector('.search-icon');
        
        if (searchIcon) {
            searchIcon.classList.remove('fa-search');
            searchIcon.classList.add('fa-spinner', 'fa-spin');
        }
        
        searchTimeout = setTimeout(() => {
            if (searchIcon) {
                searchIcon.classList.remove('fa-spinner', 'fa-spin');
                searchIcon.classList.add('fa-search');
            }
            filterProducts();
        }, 300);
    }
}

// Función para cambiar vista (tabla/grid)
function toggleView(viewType) {
    console.log('🔄 Cambiando vista a:', viewType);
    
    // CERRAR BURBUJA DE STOCK si está abierta (evita que quede con coordenadas incorrectas)
    closeStockBubble();
    
    // CERRAR MENÚS FLOTANTES si están abiertos
    if (productos_activeFloatingContainer) {
        closeFloatingActionsAnimated();
    }
    
    const tableContainer = document.querySelector('.data-table-wrapper');
    const gridContainer = document.querySelector('.products-grid');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // Si no existe el grid, crearlo
    if (!gridContainer) {
        createGridView();
    }
    
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === viewType) {
            btn.classList.add('active');
        }
    });
    
    if (viewType === 'grid') {
        tableContainer.style.display = 'none';
        document.querySelector('.products-grid').style.display = 'grid';
        // Recargar productos para asegurar datos actualizados
        loadProducts();
    } else {
        tableContainer.style.display = 'block';
        document.querySelector('.products-grid').style.display = 'none';
        // Recargar productos para asegurar datos actualizados
        loadProducts();
    }
}

// Función para crear vista grid
function createGridView() {
    const gridContainer = document.createElement('div');
    gridContainer.className = 'products-grid';
    gridContainer.style.display = 'none';
    
    // Insertar después de la tabla
    const tableWrapper = document.querySelector('.data-table-wrapper');
    tableWrapper.parentNode.insertBefore(gridContainer, tableWrapper.nextSibling);
}

// Función para mostrar productos en grid
function displayProductsGrid(products) {
    const gridContainer = document.querySelector('.products-grid');
    if (!gridContainer) return;
    
    if (!products || products.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <i class="fas fa-box-open"></i>
                <p>No se encontraron productos</p>
            </div>
        `;
        return;
    }
    
    // Detectar si es móvil
    const isMobile = window.innerWidth <= 768;
    
    gridContainer.innerHTML = products.map(producto => {
        const stock = parseInt(producto.stock_actual_producto) || 0;
        const stockMinimo = parseInt(producto.stock_minimo_producto); // Tomar valor directo de BD
        const stockMaximo = parseInt(producto.stock_maximo_producto); // Tomar valor directo de BD
        let stockClass = 'stock-normal'; // ⭐ Por defecto verde
        let stockText = 'Normal';
        
        if (stock === 0) {
            stockClass = 'stock-agotado';
            stockText = 'Agotado';
        } else if (stockMinimo && stock <= stockMinimo) {
            stockClass = 'stock-bajo';
            stockText = 'Bajo';
        } else {
            stockClass = 'stock-normal'; // ⭐ Verde explícito para stock > stock_minimo
            stockText = 'Normal';
        }
        
        // Generar HTML de imagen SIEMPRE usando la misma función que la tabla
        const imageUrl = getProductImageUrl(producto);
        const hasImage = imageUrl && !imageUrl.includes('default-product.jpg');
        
        const imageHTML = `
            <div class="product-card-image-mobile ${hasImage ? '' : 'no-image'}">
                ${hasImage 
                    ? `<img src="${imageUrl}" alt="${producto.nombre_producto || 'Producto'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">` 
                    : '<i class="fas fa-image"></i>'}
            </div>
        `;
        
        return `
            <div class="product-card" ondblclick="editProduct(${producto.id_producto})" style="cursor: pointer;" data-product-id="${producto.id_producto}">
                ${imageHTML}
                <div class="product-card-header">
                    <h3 class="product-card-title">${producto.nombre_producto || 'Sin nombre'}</h3>
                    <span class="product-card-status ${producto.estado === 'activo' ? 'active' : 'inactive'}">
                        ${producto.estado === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="product-card-body">
                    ${producto.codigo ? `<div class="product-card-sku">Código: ${producto.codigo}</div>` : ''}
                    <div class="product-card-category">
                        <i class="fas fa-tag"></i> ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categoría'}
                    </div>
                    
                    <div class="product-card-stock">
                        <span class="${stockClass}">
                            <i class="fas fa-box"></i> ${stock} unidades (${stockText})
                        </span>
                    </div>
                    
                    <div class="product-card-price">
                        <i class="fas fa-dollar-sign"></i>
                        $${parseFloat(producto.precio_producto || 0).toLocaleString('es-CO')}
                        ${producto.precio_descuento_producto ? `<span class="discount-price">$${parseFloat(producto.precio_descuento_producto).toLocaleString('es-CO')}</span>` : ''}
                    </div>
                </div>
                
                <div class="product-card-actions">
                    <button class="product-card-btn btn-view" onclick="event.stopPropagation(); window.location.href='product-details.php?id=${producto.id_producto}'" title="Ver producto" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-edit" onclick="event.stopPropagation(); editProduct(${producto.id_producto})" title="Editar producto" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn ${producto.estado === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeProductEstado(${producto.id_producto})" 
                            title="${producto.estado === 'activo' ? 'Desactivar' : 'Activar'} producto"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${producto.estado === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-stock" onclick="event.stopPropagation(); updateStock(${producto.id_producto}, ${producto.stock_actual_producto}, event)" title="Actualizar stock" style="background-color: #fd7e14 !important; color: white !important; border: none !important;">
                        <i class="fas fa-boxes" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-delete" onclick="event.stopPropagation(); deleteProduct(${producto.id_producto}, '${(producto.nombre_producto || 'Producto').replace(/'/g, "\\'")}')\" title="Eliminar producto" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // NO aplicar Masonry en móvil - usar grid normal
    // if (isMobile) {
    //     applyMasonryLayout();
    // }
}

// Función para aplicar Masonry layout (DESACTIVADA - causaba problemas de espacio vacío)
function applyMasonryLayout() {
    // Desactivada - se usa grid normal ahora
    return;
    
    const gridContainer = document.querySelector('.products-grid');
    if (!gridContainer || window.innerWidth > 768) return;
    
    // Esperar a que las imágenes se carguen
    const images = gridContainer.querySelectorAll('img');
    let loadedImages = 0;
    const totalImages = images.length;
    
    const positionCards = () => {
        const cards = gridContainer.querySelectorAll('.product-card');
        cards.forEach(card => {
            const height = card.offsetHeight;
            const rowSpan = Math.ceil((height + 10) / 8); // 10 es el gap, 8 es grid-auto-rows
            card.style.gridRowEnd = `span ${rowSpan}`;
        });
    };
    
    if (totalImages === 0) {
        // Si no hay imágenes, aplicar inmediatamente
        setTimeout(positionCards, 50);
    } else {
        // Esperar a que las imágenes se carguen
        images.forEach(img => {
            if (img.complete) {
                loadedImages++;
            } else {
                img.addEventListener('load', () => {
                    loadedImages++;
                    if (loadedImages === totalImages) {
                        positionCards();
                    }
                });
                img.addEventListener('error', () => {
                    loadedImages++;
                    if (loadedImages === totalImages) {
                        positionCards();
                    }
                });
            }
        });
        
        // Si todas ya están cargadas
        if (loadedImages === totalImages) {
            positionCards();
        }
    }
    
    // Reajustar en cambios de tamaño
    let resizeTimeout;
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(positionCards, 100);
        }
    });
}

// ============ FUNCIONES PRINCIPALES PRODUCTOS ============

// ===================================
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - VERSIÓN AVANZADA
// ===================================

// Variables globales para el sistema flotante
let productos_activeFloatingContainer = null;
let productos_activeProductId = null;
let productos_isAnimating = false;
let productos_animationTimeout = null;
let productos_floatingButtons = [];
let productos_centerButton = null;

// Función principal para mostrar botones flotantes
function showActionMenu(productId, productName, stock, estado, event) {
    // CERRAR BURBUJA DE STOCK SI ESTÁ ABIERTA
    const existingBubbles = document.querySelectorAll('.stock-update-bubble');
    existingBubbles.forEach(bubble => {
        if (bubble && bubble.parentNode) {
            // Animación de salida
            bubble.style.transform = 'scale(0)';
            bubble.style.opacity = '0';
            setTimeout(() => {
                if (bubble && bubble.parentNode) {
                    bubble.remove();
                }
            }, 400);
        }
    });
    
    // Eliminar overlay de la burbuja de stock
    const stockOverlays = document.querySelectorAll('.stock-bubble-overlay');
    stockOverlays.forEach(overlay => {
        if (overlay && overlay.parentNode) {
            overlay.remove();
        }
    });
    
    // Prevenir múltiples ejecuciones
    if (productos_isAnimating) return;
    
    // Si ya está abierto para el mismo producto, cerrarlo
    if (productos_activeFloatingContainer && productos_activeProductId === productId) {
        closeFloatingActionsAnimated();
        return;
    }
    
    // Cerrar cualquier menú anterior
    if (productos_activeFloatingContainer) {
        closeFloatingActionsAnimated();
    }
    
    // Obtener el botón que disparó el evento - MEJORADO
    let triggerButton = null;
    
    if (event && event.currentTarget) {
        triggerButton = event.currentTarget;
    } else if (event && event.target) {
        // Buscar el botón padre si el click fue en el icono
        triggerButton = event.target.closest('.btn-menu');
    } else {
        // Fallback robusto: buscar entre todos los .btn-menu y comparar atributo onclick
        const allMenuButtons = document.querySelectorAll('.btn-menu');
        for (const btn of allMenuButtons) {
            const onclickAttr = btn.getAttribute('onclick') || '';
            if (onclickAttr.includes(`showActionMenu(${productId}`)) {
                triggerButton = btn;
                break;
            }
        }
    }
    
    if (!triggerButton) {
        return;
    }
    
    productos_isAnimating = true;
    productos_activeProductId = productId;
    
    // Crear contenedor flotante con animaciones
    createAnimatedFloatingContainer(triggerButton, productId, productName, stock, estado);
}

// Crear el contenedor flotante con animaciones avanzadas
function createAnimatedFloatingContainer(triggerButton, productId, productName, stock, estado) {
    // Limpiar cualquier menú anterior
    if (productos_activeFloatingContainer) {
        closeFloatingActionsAnimated();
    }
    
    // Verificar que tenemos un trigger button válido
    if (!triggerButton) {
        productos_isAnimating = false;
        return;
    }
    
    // Crear contenedor principal con ID único
    productos_activeFloatingContainer = document.createElement('div');
    productos_activeFloatingContainer.id = 'animated-floating-menu-' + productId;
    productos_activeFloatingContainer.className = 'animated-floating-container';
    
    // Asegurar que el contenedor padre tenga position relative
    const tableContainer = document.querySelector('.data-table-wrapper') || 
                          document.querySelector('.module-content') || 
                          document.querySelector('.admin-module');
    
    if (tableContainer) {
        const computedStyle = window.getComputedStyle(tableContainer);
        if (computedStyle.position === 'static') {
            tableContainer.style.position = 'relative';
        }
    }
    
    // Estilos del contenedor principal
    productos_activeFloatingContainer.style.cssText = `
        position: absolute !important;
        z-index: 999999 !important;
        pointer-events: none !important;
        top: 0 !important;
        left: 0 !important;
        width: auto !important;
        height: auto !important;
        display: block !important;
    `;
    
    // Guardar referencia al trigger button
    productos_activeFloatingContainer.triggerButton = triggerButton;
    
    // Crear botón central con los tres puntitos
    createCenterButton();
    
    // Definir acciones con colores vibrantes
    // Definir acciones con colores vibrantes (usando closures para capturar event) - SIN LABELS
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', actionFn: () => window.location.href = 'product-details.php?id=' + productId },
        { icon: 'fa-edit', color: '#34a853', actionFn: () => editProduct(productId) },
        { icon: 'fa-boxes', color: '#ff9800', actionFn: () => updateStock(productId, stock, event) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', actionFn: () => changeProductEstado(productId) },
        { icon: 'fa-trash', color: '#f44336', actionFn: () => deleteProduct(productId, productName) }
    ];
    
    // Crear botones flotantes con animaciones
    productos_floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        createAnimatedButton(action, index, angle, radius);
    });
    
    // Agregar al contenedor de la tabla
    if (tableContainer) {
        tableContainer.appendChild(productos_activeFloatingContainer);
    } else {
        document.body.appendChild(productos_activeFloatingContainer);
    }
    
    // Actualizar posiciones iniciales
    updateAnimatedButtonPositions();
    
    productos_activeProductId = productId;
    
    // Event listeners con animaciones
    setupAnimatedEventListeners();
    
    // Iniciar animación de entrada
    startOpenAnimation();
}

// Crear botón central con tres puntitos (para cerrar)
function createCenterButton() {
    productos_centerButton = document.createElement('div');
    productos_centerButton.className = 'animated-center-button';
    productos_centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
    
    productos_centerButton.style.cssText = `
        position: absolute !important;
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        background: transparent !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 1000000 !important;
        font-size: 16px !important;
        box-shadow: none !important;
        pointer-events: auto !important;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        border: none !important;
        transform: scale(0) rotate(0deg) !important;
        opacity: 0 !important;
    `;
    
    // Efectos hover
    productos_centerButton.addEventListener('mouseenter', () => {
        productos_centerButton.style.transform = 'scale(1.15) rotate(180deg)';
        productos_centerButton.style.boxShadow = '0 6px 25px rgba(102, 126, 234, 0.3)';
        productos_centerButton.style.background = 'rgba(255, 255, 255, 0.1)';
    });
    
    productos_centerButton.addEventListener('mouseleave', () => {
        productos_centerButton.style.transform = 'scale(1) rotate(360deg)';
        productos_centerButton.style.boxShadow = 'none';
        productos_centerButton.style.background = 'transparent';
    });
    
    // Click para cerrar
    productos_centerButton.addEventListener('click', (e) => {
        e.stopPropagation();
        closeFloatingActionsAnimated();
    });
    
    productos_activeFloatingContainer.appendChild(productos_centerButton);
}

// Crear botón animado individual
function createAnimatedButton(action, index, angle, radius) {
    const button = document.createElement('div');
    button.innerHTML = `<i class="fas ${action.icon}"></i>`;
    button.dataset.angle = angle;
    button.dataset.radius = radius;
    button.dataset.index = index;
    button.className = 'animated-floating-button';
    
    // Gradientes personalizados para cada color
    const gradients = {
        '#1a73e8': 'linear-gradient(45deg, #1a73e8 0%, #4285f4 100%)',
        '#34a853': 'linear-gradient(45deg, #34a853 0%, #4caf50 100%)',
        '#ff9800': 'linear-gradient(45deg, #ff9800 0%, #ffc107 100%)',
        '#9c27b0': 'linear-gradient(45deg, #9c27b0 0%, #e91e63 100%)',
        '#f44336': 'linear-gradient(45deg, #f44336 0%, #ff5722 100%)'
    };
    
    button.style.cssText = `
        position: absolute !important;
        width: 55px !important;
        height: 55px !important;
        border-radius: 50% !important;
        background: ${gradients[action.color] || action.color} !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 1000001 !important;
        font-size: 20px !important;
        box-shadow: 0 6px 20px ${action.color}40 !important;
        pointer-events: auto !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        border: none !important;
        transform: scale(0) rotate(-180deg) !important;
        opacity: 0 !important;
        backdrop-filter: blur(10px) !important;
    `;
    
    // Efectos hover avanzados
    button.addEventListener('mouseenter', () => {
        button.style.transform = 'scale(1.2) rotate(15deg)';
        button.style.boxShadow = `0 10px 30px ${action.color}60`;
        button.style.zIndex = '1000003';
        
        // Crear ripple effect
        createRippleEffect(button, action.color);
        
        // Tooltip deshabilitado - solo iconos
        // showTooltip(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '1000001';
        
        // Ocultar tooltip
        hideTooltip();
    });
    
    // Click handler con animación
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Forzar cierre inmediato del menú
        forceCloseFloatingActions();
        
        // Animación de click del botón
        button.style.transform = 'scale(0.9) rotate(180deg)';
        setTimeout(() => {
            button.style.transform = 'scale(1.1) rotate(360deg)';
        }, 100);
        
        // Ejecutar acción después de un delay mínimo
        setTimeout(() => {
            try {
                action.actionFn();
            } catch (err) {
                console.error('Error ejecutando acción flotante:', err);
            }
        }, 200);
    });
    
    productos_activeFloatingContainer.appendChild(button);
    productos_floatingButtons.push(button);
}

// Crear efecto ripple
function createRippleEffect(button, color) {
    const ripple = document.createElement('div');
    ripple.style.cssText = `
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        width: 10px !important;
        height: 10px !important;
        background: ${color} !important;
        border-radius: 50% !important;
        transform: translate(-50%, -50%) scale(0) !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
        animation: rippleEffect 0.6s ease-out !important;
        z-index: -1 !important;
    `;
    
    // Agregar CSS de animación si no existe
    if (!document.querySelector('#ripple-animation-styles')) {
        const styles = document.createElement('style');
        styles.id = 'ripple-animation-styles';
        styles.textContent = `
            @keyframes rippleEffect {
                0% { transform: translate(-50%, -50%) scale(0); opacity: 0.6; }
                100% { transform: translate(-50%, -50%) scale(4); opacity: 0; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    button.appendChild(ripple);
    
    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.remove();
        }
    }, 600);
}

// Mostrar tooltip
function showTooltip(button, text) {
    // Remover tooltip anterior si existe
    hideTooltip();
    
    const tooltip = document.createElement('div');
    tooltip.id = 'floating-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute !important;
        background: rgba(0, 0, 0, 0.9) !important;
        color: white !important;
        padding: 8px 12px !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        white-space: nowrap !important;
        z-index: 10004 !important;
        pointer-events: none !important;
        opacity: 0 !important;
        transform: translateY(10px) !important;
        transition: all 0.2s ease !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    `;
    
    document.body.appendChild(tooltip);
    
    // Posicionar tooltip
    const buttonRect = button.getBoundingClientRect();
    tooltip.style.left = (buttonRect.left + buttonRect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (buttonRect.bottom + 10) + 'px';
    
    // Animar entrada
    setTimeout(() => {
        tooltip.style.opacity = '1';
        tooltip.style.transform = 'translateY(0)';
    }, 50);
}

// Ocultar tooltip
function hideTooltip() {
    const tooltip = document.getElementById('floating-tooltip');
    if (tooltip) {
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'translateY(10px)';
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.remove();
            }
        }, 200);
    }
}

// Función para actualizar posiciones de botones con animaciones
function updateAnimatedButtonPositions() {
    if (!productos_activeFloatingContainer) {
        return;
    }
    
    if (!productos_activeFloatingContainer.triggerButton) {
        return;
    }
    
    // Verificar que el trigger button aún existe en el DOM
    if (!document.contains(productos_activeFloatingContainer.triggerButton)) {
        closeFloatingActionsAnimated();
        return;
    }
    
    // Obtener el contenedor padre donde están los botones
    const container = productos_activeFloatingContainer.parentElement;
    if (!container) {
        return;
    }
    
    // Asegurar que el contenedor tenga position relative
    const containerStyle = window.getComputedStyle(container);
    if (containerStyle.position === 'static') {
        container.style.position = 'relative';
    }
    
    // Obtener posiciones relativas
    const containerRect = container.getBoundingClientRect();
    const triggerRect = productos_activeFloatingContainer.triggerButton.getBoundingClientRect();
    
    // Calcular posición relativa del trigger respecto al contenedor
    const centerX = triggerRect.left - containerRect.left + triggerRect.width / 2;
    const centerY = triggerRect.top - containerRect.top + triggerRect.height / 2;
    
    // Ajustar por scroll del contenedor si es necesario
    const scrollLeft = container.scrollLeft || 0;
    const scrollTop = container.scrollTop || 0;
    
    const finalCenterX = centerX + scrollLeft;
    const finalCenterY = centerY + scrollTop;
    
    // Actualizar posición del botón central
    if (productos_centerButton) {
        productos_centerButton.style.left = `${finalCenterX - 22.5}px`;
        productos_centerButton.style.top = `${finalCenterY - 22.5}px`;
    }
    
    // Actualizar posición de cada botón flotante
    productos_floatingButtons.forEach((button, index) => {
        const angle = parseFloat(button.dataset.angle);
        const radius = parseFloat(button.dataset.radius);
        
        if (isNaN(angle) || isNaN(radius)) {
            return;
        }
        
        const x = finalCenterX + Math.cos(angle) * radius;
        const y = finalCenterY + Math.sin(angle) * radius;
        
        button.style.left = `${x - 27.5}px`;
        button.style.top = `${y - 27.5}px`;
    });
}

// Iniciar animación de apertura
function startOpenAnimation() {
    // Animar botón central primero
    if (productos_centerButton) {
        setTimeout(() => {
            productos_centerButton.style.transform = 'scale(1) rotate(360deg)';
            productos_centerButton.style.opacity = '1';
        }, 100);
    }
    
    // Animar botones flotantes con delay escalonado
    productos_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(1) rotate(0deg)';
            button.style.opacity = '1';
        }, 200 + (index * 100)); // 100ms de delay entre cada botón
    });
    
    // Finalizar animación
    setTimeout(() => {
        productos_isAnimating = false;
    }, 200 + (productos_floatingButtons.length * 100) + 400);
}

// Event listeners animados
function setupAnimatedEventListeners() {
    // Cerrar al hacer click fuera con animación
    const handleClick = (e) => {
        if (productos_activeFloatingContainer && !productos_activeFloatingContainer.contains(e.target)) {
            closeFloatingActionsAnimated();
        }
    };
    
    // Actualizar posiciones en resize
    const handleResize = () => {
        if (productos_activeFloatingContainer) {
            setTimeout(() => {
                updateAnimatedButtonPositions();
            }, 100);
        }
    };
    
    // Manejar scroll del contenedor padre
    const handleScroll = () => {
        if (productos_activeFloatingContainer) {
            updateAnimatedButtonPositions();
        }
    };
    
    document.addEventListener('click', handleClick);
    window.addEventListener('resize', handleResize, { passive: true });
    
    // Agregar listener de scroll al contenedor padre
    const container = productos_activeFloatingContainer.parentElement;
    if (container) {
        container.addEventListener('scroll', handleScroll, { passive: true });
    }
    
    // Limpiar listeners cuando se cierre
    productos_activeFloatingContainer.cleanup = () => {
        document.removeEventListener('click', handleClick);
        window.removeEventListener('resize', handleResize);
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }
    };
}

// Cerrar menú flotante con animación avanzada
function closeFloatingActionsAnimated() {
    if (!productos_activeFloatingContainer || productos_isAnimating) return;
    
    productos_isAnimating = true;
    
    // Limpiar timeout anterior si existe
    if (productos_animationTimeout) {
        clearTimeout(productos_animationTimeout);
    }
    
    // Ocultar tooltip si existe
    hideTooltip();
    
    // Animar salida de botones flotantes (en orden inverso)
    productos_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(0) rotate(-180deg)';
            button.style.opacity = '0';
        }, index * 50);
    });
    
    // Animar salida del botón central
    if (productos_centerButton) {
        setTimeout(() => {
            productos_centerButton.style.transform = 'scale(0) rotate(-360deg)';
            productos_centerButton.style.opacity = '0';
        }, productos_floatingButtons.length * 50 + 100);
    }
    
    // Limpiar después de que termine la animación
    productos_animationTimeout = setTimeout(() => {
        if (productos_activeFloatingContainer) {
            if (productos_activeFloatingContainer.cleanup) {
                productos_activeFloatingContainer.cleanup();
            }
            
            productos_activeFloatingContainer.remove();
            productos_activeFloatingContainer = null;
            productos_centerButton = null;
            productos_floatingButtons = [];
            productos_activeProductId = null;
            productos_isAnimating = false;
        }
    }, productos_floatingButtons.length * 50 + 400);
}

// Mantener compatibilidad con función anterior
function closeFloatingActions() {
    closeFloatingActionsAnimated();
}

// Función para forzar el cierre con retraso del menú flotante
function forceCloseFloatingActions() {
    // Agregar un retraso antes del cierre forzado
    setTimeout(() => {
        // Limpiar cualquier timeout pendiente
        if (productos_animationTimeout) {
            clearTimeout(productos_animationTimeout);
            productos_animationTimeout = null;
        }
        
        // Ocultar tooltip inmediatamente
        hideTooltip();
        
        // Si hay un contenedor activo, eliminarlo inmediatamente
        if (productos_activeFloatingContainer) {
            try {
                // Limpiar eventos si existen
                if (productos_activeFloatingContainer.cleanup) {
                    productos_activeFloatingContainer.cleanup();
                }
                
                // Remover del DOM inmediatamente
                productos_activeFloatingContainer.remove();
            } catch (e) {
            }
            
            // Resetear variables globales
            productos_activeFloatingContainer = null;
            productos_centerButton = null;
            productos_floatingButtons = [];
            productos_activeProductId = null;
            productos_isAnimating = false;
        }
        
        // Asegurarse de que no queden elementos flotantes huérfanos
        const orphanedContainers = document.querySelectorAll('.animated-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error eliminando contenedor huérfano:', e);
            }
        });
    }, 320); // Retraso de 150ms antes del cierre forzado
}

// ============ SISTEMA DE MODALES ============



// Función para exportar productos
async function exportProducts() {
    
    try {
        // // showNotification('Preparando exportación...', 'info');
        
        const response = await fetch(`${CONFIG.apiUrl}?action=export`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `productos_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            // // showNotification('Productos exportados exitosamente', 'success');
        } else {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
    } catch (error) {
        
        // Generar CSV del lado del cliente como fallback
        if (productos && productos.length > 0) {
            generateClientSideCSV();
        } else {
            // // showNotification('No hay productos para exportar', 'warning');
        }
    }
}

// Función para generar CSV del lado del cliente
function generateClientSideCSV() {
    const headers = ['ID', 'Nombre', 'Código', 'Categoría', 'Stock', 'Precio', 'Estado'];
    let csvContent = headers.join(',') + '\n';
    
    productos.forEach(producto => {
        const row = [
            producto.id_producto || '',
            `"${(producto.nombre_producto || '').replace(/"/g, '""')}"`,
            producto.codigo || '',
            `"${(producto.categoria_nombre || producto.nombre_categoria || '').replace(/"/g, '""')}"`,
            producto.stock_actual_producto != null ? producto.stock_actual_producto : 0,
            producto.precio_producto != null ? producto.precio_producto : 0,
            (producto.activo == 1 || producto.status_producto == 1) ? 'Activo' : 'Inactivo'
        ];
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = `productos_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // showNotification('Productos exportados exitosamente', 'success');
}

// Función para mostrar reporte de stock
function showStockReport() {
    // Implementar modal de reporte de stock
    // showNotification('Reporte de stock - Funcionalidad en desarrollo', 'info');
}

// Función para limpiar búsqueda con animación
function clearProductSearch() {
    if (typeof $ !== 'undefined') {
        const searchInput = $('#search-productos');
        searchInput.val('').focus();
        
        // Animación visual
        const searchContainer = searchInput.parent();
        searchContainer.addClass('search-cleared');
        
        setTimeout(() => {
            searchContainer.removeClass('search-cleared');
        }, 300);
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-productos');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    }
    
    filterProducts();
}

// Función para limpiar todos los filtros con efectos visuales
function clearAllProductFilters() {
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-productos').val('');
        $('#filter-category').val('');
        $('#filter-status').val('');
        $('#filter-stock').val('');
        $('#filter-fecha').val('');
        
        // Efecto visual de limpieza
        $('.module-filters').addClass('filters-clearing');
        
        setTimeout(() => {
            $('.module-filters').removeClass('filters-clearing');
        }, 400);
    } else {
        // Fallback vanilla JS
        const elements = [
            'search-productos',
            'filter-category',
            'filter-status',
            'filter-stock',
            'filter-fecha'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
    }
    
    // Mostrar notificación
    // showNotification('Filtros limpiados', 'info');
    
    filterProducts();
}

// Función para acciones en lote
async function handleBulkProductAction(action) {
    const selectedProducts = getSelectedProducts();
    
    if (selectedProducts.length === 0) {
        // // showNotification('Por favor selecciona al menos un producto', 'warning');
        return;
    }    
    const confirmMessage = `¿Estás seguro de ${action} ${selectedProducts.length} producto(s)?`;
    if (!confirm(confirmMessage)) return;
    
    try {
        let endpoint = '';
        let method = 'POST';
        
        switch (action) {
            case 'activar':
                endpoint = '?action=bulk-activate';
                break;
            case 'desactivar':
                endpoint = '?action=bulk-deactivate';
                break;
            case 'eliminar':
                endpoint = '?action=bulk-delete';
                method = 'DELETE';
                break;
            default:
                throw new Error('Acción no válida');
        }
        
        const response = await fetch(`${CONFIG.apiUrl}${endpoint}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedProducts })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // showNotification(`${action} completado para ${selectedProducts.length} producto(s)`, 'success');
            loadProducts(); // Recargar lista
            clearProductSelection();
        } else {
            throw new Error(result.message || 'Error en operación en lote');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para toggle select all
function toggleSelectAllProducts(checkbox) {
    
    const productCheckboxes = document.querySelectorAll('input[name="product_select"]');
    productCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    updateBulkActionButtons();
}

// Función para ver producto (wrapper que llama al parent)
function viewProduct(id) {
    console.log('👁️ viewProduct() llamado con ID:', id);
    
    // CERRAR BURBUJA DE STOCK si está abierta
    closeStockBubble();
    
    // Verificar si el ID es válido
    if (!id || id === 'undefined' || id === 'null') {
        console.error('❌ ID inválido para ver:', id);
        if (typeof showNotification === 'function') {
            // showNotification('Error: ID de producto inválido', 'error');
        }
        return;
    }
    
    // Debug: Verificar disponibilidad de funciones
    console.log('🔍 Buscando showViewProductModal en:', {
        'window': typeof window.showViewProductModal,
        'parent': typeof parent?.showViewProductModal,
        'top': typeof top?.showViewProductModal
    });
    
    // Como NO estamos en iframe, parent === window
    // Buscar directamente en window
    if (typeof window.showViewProductModal === 'function') {
        console.log('✅ Llamando a window.showViewProductModal');
        window.showViewProductModal(id);
    } else if (typeof window.viewProduct !== viewProduct && typeof window.viewProduct === 'function') {
        // Evitar recursión infinita
        console.log('✅ Llamando a window.viewProduct (función diferente)');
        window.viewProduct(id);
    } else {
        console.error('❌ showViewProductModal NO disponible. Funciones disponibles:', Object.keys(window).filter(k => k.includes('Product')));
        console.warn('⚠️ Usando fallback: abrir en nueva ventana');
        // Fallback: abrir en nueva ventana
        const url = AppConfig ? AppConfig.getViewUrl(`admin/product_modal.php?action=view&id=${id}`) : `/fashion-master/app/views/admin/product_modal.php?action=view&id=${id}`;
        window.open(url, 'ProductView', 'width=900,height=700');
    }
}

// ===== FUNCIÓN GLOBAL PARA CERRAR BURBUJA DE STOCK =====
function closeStockBubble() {
    const existingBubbles = document.querySelectorAll('.stock-update-bubble');
    const existingOverlays = document.querySelectorAll('.stock-bubble-overlay');
    
    existingBubbles.forEach(bubble => {
        // Limpiar listeners si existen
        if (bubble.updatePositionListener) {
            window.removeEventListener('scroll', bubble.updatePositionListener, true);
            window.removeEventListener('resize', bubble.updatePositionListener);
        }
        
        // Animación de salida
        bubble.style.transform = 'scale(0)';
        bubble.style.opacity = '0';
        
        setTimeout(() => {
            if (bubble && bubble.parentNode) {
                bubble.remove();
            }
        }, 400);
    });
    
    existingOverlays.forEach(overlay => {
        setTimeout(() => {
            if (overlay && overlay.parentNode) {
                overlay.remove();
            }
        }, 400);
    });
    
    console.log('🗑️ Burbujas de stock cerradas');
}

// Función para editar producto
async function editProduct(id) {
    console.log('🔧 editProduct() llamado con ID:', id);
    
    // CERRAR BURBUJA DE STOCK si está abierta
    closeStockBubble();
    
    // Verificar si el ID es válido
    if (!id || id === 'undefined' || id === 'null') {
        console.error('❌ ID inválido para editar:', id);
        if (typeof showNotification === 'function') {
            // showNotification('Error: ID de producto inválido', 'error');
        }
        return;
    }
    
    // Debug: Verificar disponibilidad de funciones
    console.log('🔍 Buscando showEditProductModal en:', {
        'window': typeof window.showEditProductModal,
        'parent': typeof parent?.showEditProductModal,
        'top': typeof top?.showEditProductModal
    });
    
    // Como NO estamos en iframe, parent === window
    // Buscar directamente en window
    if (typeof window.showEditProductModal === 'function') {
        console.log('✅ Llamando a window.showEditProductModal');
        window.showEditProductModal(id);
    } else if (typeof window.editProduct !== editProduct && typeof window.editProduct === 'function') {
        // Evitar recursión infinita
        console.log('✅ Llamando a window.editProduct (función diferente)');
        window.editProduct(id);
    } else {
        console.error('❌ showEditProductModal NO disponible. Funciones disponibles:', Object.keys(window).filter(k => k.includes('Product')));
        console.warn('⚠️ Usando fallback: abrir en nueva ventana');
        // Fallback: abrir en nueva ventana
        const url = AppConfig ? AppConfig.getViewUrl(`admin/product_modal.php?action=edit&id=${id}`) : `/fashion-master/app/views/admin/product_modal.php?action=edit&id=${id}`;
        window.open(url, 'ProductEdit', 'width=900,height=700');
    }
}

// Función para actualizar stock - MEJORADA CON BURBUJA SIN BOTONES
function updateStock(id, currentStock, event) {
    // VERIFICAR SI YA EXISTE UNA BURBUJA ABIERTA PARA ESTE PRODUCTO (TOGGLE)
    const existingBubble = document.querySelector(`.stock-update-bubble[data-product-id="${id}"]`);
    if (existingBubble) {
        console.log('🔄 Burbuja ya existe para este producto, cerrando (TOGGLE)...');
        closeStockBubble();
        return; // SALIR - No abrir de nuevo
    }
    
    // CERRAR MENÚ FLOTANTE SI ESTÁ ABIERTO (sin bloquear futuros menús)
    if (productos_activeFloatingContainer) {
        // Cerrar con animación
        closeFloatingActionsAnimated();
    }
    
    // Forzar eliminación de cualquier menú flotante residual
    const allFloatingMenus = document.querySelectorAll('.animated-floating-container');
    allFloatingMenus.forEach(menu => {
        if (menu && menu.parentNode) {
            menu.remove();
        }
    });
    
    // Resetear variables globales del menú flotante
    productos_activeFloatingContainer = null;
    productos_activeProductId = null;
    productos_isAnimating = false;
    if (productos_animationTimeout) {
        clearTimeout(productos_animationTimeout);
        productos_animationTimeout = null;
    }
    
    // Eliminar cualquier burbuja existente (de otros productos)
    closeStockBubble();
    
    // Crear overlay SIN bloquear scroll - solo para detectar clicks
    const overlay = document.createElement('div');
    overlay.className = 'stock-bubble-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000000;
        background: transparent;
        pointer-events: none;
    `;
    
    // Crear burbuja de stock - PEQUEÑA (50x50px) estilo botones flotantes, expandible hasta 3 dígitos
    const stockBubble = document.createElement('div');
    stockBubble.className = 'stock-update-bubble';
    stockBubble.setAttribute('data-product-id', id); // Agregar ID del producto para identificar
    stockBubble.innerHTML = `
        <input type="number" 
               id="stockInput" 
               value="${currentStock}" 
               min="0" 
               max="999"
               class="stock-input-circle"
               placeholder="0"
               autocomplete="off"
               maxlength="3"
               style="border: none !important; outline: none !important; box-shadow: none !important; text-decoration: none !important; -webkit-appearance: none !important; border-bottom: none !important;">
    `;
    
    // Encontrar el botón que disparó la acción (puede ser btn-menu de tabla o btn-stock de grid)
    // Primero intentar obtenerlo del evento
    let triggerButton = null;
    let isGridView = false;
    
    if (event) {
        // Intentar desde currentTarget
        triggerButton = event.currentTarget;
        
        // Verificar si es un botón de la vista grid
        if (triggerButton && triggerButton.classList.contains('product-card-btn')) {
            isGridView = true;
            console.log('✅ Detectado: Vista Grid desde botón');
        }
        // Si es un botón flotante, ignorar y buscar el botón real
        else if (triggerButton && triggerButton.classList.contains('animated-floating-button')) {
            triggerButton = null; // Resetear para buscar el botón correcto
            console.log('⚠️ Evento desde botón flotante, buscando botón real...');
        }
        // Si es el btn-menu de la tabla
        else if (triggerButton && triggerButton.classList.contains('btn-menu')) {
            isGridView = false;
            console.log('✅ Detectado: Vista Tabla desde btn-menu');
        }
    }
    
    // Si aún no tenemos el botón, buscarlo en el DOM por el ID del producto
    if (!triggerButton) {
        console.log('🔍 Buscando botón en DOM para producto ID:', id);
        
        // Determinar qué vista está visible actualmente
        const tableContainer = document.querySelector('.data-table-wrapper');
        const gridContainer = document.querySelector('.products-grid');
        const isTableVisible = tableContainer && tableContainer.style.display !== 'none';
        const isGridVisible = gridContainer && gridContainer.style.display !== 'none';
        
        console.log('📊 Vistas visibles - Tabla:', isTableVisible, 'Grid:', isGridVisible);
        
        // Buscar en la vista VISIBLE primero
        if (isGridVisible) {
            // Buscar en vista grid (visible)
            const productCard = document.querySelector(`.product-card[data-product-id="${id}"]`);
            if (productCard) {
                triggerButton = productCard.querySelector('.btn-stock');
                if (triggerButton) {
                    isGridView = true;
                    console.log('✅ Encontrado en Grid:', triggerButton);
                }
            }
        }
        
        if (!triggerButton && isTableVisible) {
            // Buscar en la tabla (visible)
            const productRow = document.querySelector(`tr[data-product-id="${id}"]`);
            if (productRow) {
                triggerButton = productRow.querySelector('.btn-menu');
                if (triggerButton) {
                    isGridView = false;
                    console.log('✅ Encontrado en Tabla:', triggerButton);
                }
            }
        }
    }
    
    // Último recurso: buscar por atributo onclick en la tabla
    if (!triggerButton) {
        triggerButton = document.querySelector(`[onclick*="showActionMenu(${id}"]`);
        if (triggerButton) {
            isGridView = false;
            console.log('✅ Encontrado por onclick:', triggerButton);
        }
    }
    
    if (!triggerButton) {
        console.error('❌ No se encontró el botón para el producto', id);
        return;
    }
    
    // VALIDAR QUE EL BOTÓN ESTÉ VISIBLE (no en una vista oculta)
    const rect = triggerButton.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) {
        console.error('❌ El botón encontrado está oculto (width/height = 0)');
        console.error('   Botón:', triggerButton);
        console.error('   Rect:', rect);
        closeStockBubble(); // Cerrar cualquier burbuja residual
        return;
    }
    
    console.log('✅ Botón final encontrado:', triggerButton, 'Vista Grid:', isGridView);
    
    // USAR POSICIÓN FIXED (viewport) como los botones flotantes
    const triggerRect = triggerButton.getBoundingClientRect();
    
    // Calcular centro del botón en coordenadas del viewport
    const centerX = triggerRect.left + (triggerRect.width / 2);
    const centerY = triggerRect.top + (triggerRect.height / 2);
    
    // Posición según la vista
    const bubbleSize = 40;
    const radius = 65;
    let angle;
    
    if (isGridView) {
        // En vista grid: arriba del botón (ángulo 270° = -π/2)
        angle = -Math.PI / 2; // 270° = arriba
    } else {
        // En vista tabla: a la izquierda del botón (ángulo 180° = π)
        angle = Math.PI; // 180° = izquierda
    }
    
    // Calcular posición con POSITION FIXED (coordenadas del viewport)
    const bubbleX = centerX + (Math.cos(angle) * radius) - (bubbleSize / 2);
    const bubbleY = centerY + (Math.sin(angle) * radius) - (bubbleSize / 2);
    
    // DEBUG: Mostrar valores calculados
    console.log('📍 Cálculo con POSITION FIXED:', {
        'Trigger (botón viewport)': { 
            top: triggerRect.top.toFixed(2), 
            left: triggerRect.left.toFixed(2),
            width: triggerRect.width,
            height: triggerRect.height
        },
        'Centro (viewport)': { 
            centerX: centerX.toFixed(2), 
            centerY: centerY.toFixed(2) 
        },
        'Fórmula': {
            'cos(π) * 65': (Math.cos(angle) * radius).toFixed(2),
            'sin(π) * 65': (Math.sin(angle) * radius).toFixed(2),
            'bubbleSize/2': (bubbleSize / 2)
        },
        '🎯 POSICIÓN FINAL (fixed)': { 
            bubbleX: bubbleX.toFixed(2), 
            bubbleY: bubbleY.toFixed(2) 
        }
    });
    
    // Aplicar estilos - POSICIÓN FIXED (viewport) como botones flotantes - Se expande según dígitos
    stockBubble.style.cssText = `
        position: fixed !important;
        left: ${bubbleX}px !important;
        top: ${bubbleY}px !important;
        min-width: ${bubbleSize}px !important;
        width: ${bubbleSize}px !important;
        height: ${bubbleSize}px !important;
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%) !important;
        border: 2px solid rgba(16, 185, 129, 0.3);
        border-radius: 20px !important;
        padding: 0 6px !important;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4), 
                    0 4px 12px rgba(5, 150, 105, 0.3),
                    0 0 0 1px rgba(255, 255, 255, 0.1),
                    inset 0 1px 2px rgba(255, 255, 255, 0.15);
        z-index: 1000002 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transform: scale(0) !important;
        opacity: 0 !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        pointer-events: auto !important;
        backdrop-filter: blur(10px);
    `;
    
    // Guardar referencia al botón para recalcular posición en scroll/resize
    stockBubble.triggerButton = triggerButton;
    stockBubble.isGridView = isGridView;
    
    // Estilos para el input - SIN SUBRAYADO y con expansión ovalada
    const style = document.createElement('style');
    style.id = 'stock-bubble-styles';
    style.textContent = `
        .stock-update-bubble {
            white-space: nowrap;
        }
        
        .stock-input-circle {
            background: transparent !important;
            border: 0 !important;
            border-width: 0 !important;
            border-style: none !important;
            border-color: transparent !important;
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            outline: 0 !important;
            outline-width: 0 !important;
            outline-style: none !important;
            outline-color: transparent !important;
            outline-offset: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 18px !important;
            font-weight: 900 !important;
            text-align: center !important;
            width: 100% !important;
            height: 100% !important;
            color: #ffffff !important;
            transition: none !important;
            font-family: 'Segoe UI', 'Arial', sans-serif !important;
            letter-spacing: -0.5px !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            -webkit-appearance: none !important;
            -moz-appearance: textfield !important;
            appearance: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            text-decoration: none !important;
            text-decoration-line: none !important;
            text-decoration-style: none !important;
            text-decoration-color: transparent !important;
            border-image: none !important;
            background-image: none !important;
            background-clip: padding-box !important;
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff !important;
        }
        
        .stock-input-circle:focus,
        .stock-input-circle:active,
        .stock-input-circle:hover,
        .stock-input-circle:visited,
        .stock-input-circle:focus-visible,
        .stock-input-circle:focus-within {
            outline: 0 !important;
            outline-width: 0 !important;
            outline-style: none !important;
            outline-color: transparent !important;
            outline-offset: 0 !important;
            border: 0 !important;
            border-width: 0 !important;
            border-style: none !important;
            border-color: transparent !important;
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            background: transparent !important;
            text-decoration: none !important;
            text-decoration-line: none !important;
            text-decoration-style: none !important;
            text-decoration-color: transparent !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.8),
                         0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }
        
        .stock-input-circle::-webkit-outer-spin-button,
        .stock-input-circle::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
            display: none !important;
        }
        
        .stock-input-circle[type=number] {
            -moz-appearance: textfield !important;
        }
        
        .stock-input-circle::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 18px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Forzar eliminación de cualquier estilo de Chrome/Edge */
        input[type=number].stock-input-circle::-webkit-textfield-decoration-container {
            border: none !important;
            outline: none !important;
        }
        
        @keyframes shake {
            0%, 100% { transform: scale(1) translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: scale(1) translateX(-5px); }
            20%, 40%, 60%, 80% { transform: scale(1) translateX(5px); }
        }
    `;
    
    // Eliminar style anterior si existe
    const oldStyle = document.getElementById('stock-bubble-styles');
    if (oldStyle) oldStyle.remove();
    document.head.appendChild(style);
    
    // Agregar overlay al body (sin bloquear scroll)
    document.body.appendChild(overlay);
    
    // Agregar burbuja al BODY (position fixed)
    document.body.appendChild(stockBubble);
    
    // Actualizar posición en scroll/resize (con position fixed)
    const updateBubblePosition = () => {
        if (!stockBubble || !stockBubble.triggerButton) return;
        
        const triggerRect = stockBubble.triggerButton.getBoundingClientRect();
        
        const centerX = triggerRect.left + triggerRect.width / 2;
        const centerY = triggerRect.top + triggerRect.height / 2;
        
        const bubbleSize = 40;
        const radius = 65;
        
        // Usar el ángulo guardado según la vista
        const angle = stockBubble.isGridView ? (-Math.PI / 2) : Math.PI;
        
        const bubbleX = centerX + Math.cos(angle) * radius - bubbleSize / 2;
        const bubbleY = centerY + Math.sin(angle) * radius - bubbleSize / 2;
        
        if (stockBubble && stockBubble.style) {
            stockBubble.style.left = bubbleX + 'px';
            stockBubble.style.top = bubbleY + 'px';
        }
    };
    
    // Listener para scroll/resize
    window.addEventListener('scroll', updateBubblePosition, true);
    window.addEventListener('resize', updateBubblePosition);
    stockBubble.updatePositionListener = updateBubblePosition;
    
    // Activar animación de entrada con reflow
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (stockBubble && stockBubble.style) {
                stockBubble.style.transform = 'scale(1)';
                stockBubble.style.opacity = '1';
            }
        });
    });
    
    // Focus en el input
    setTimeout(() => {
        const input = stockBubble?.querySelector('#stockInput');
        if (input) {
            input.focus();
            input.select();
            
            // Ajustar ancho de la burbuja según el número de dígitos (expansión ovalada)
            const adjustBubbleWidth = () => {
                const value = input.value.toString();
                const numDigits = value.length || 1;
                
                // Ancho base 40px, +12px por cada dígito extra
                let newWidth = 40;
                if (numDigits === 2) {
                    newWidth = 52; // Más ovalado para 2 dígitos
                } else if (numDigits >= 3) {
                    newWidth = 64; // Más ovalado para 3 dígitos
                }
                
                stockBubble.style.width = newWidth + 'px';
                
                // Recalcular posición para centrar la burbuja expandida
                const triggerRect = triggerButton.getBoundingClientRect();
                const centerX = triggerRect.left + (triggerRect.width / 2);
                const centerY = triggerRect.top + (triggerRect.height / 2);
                const radius = 65;
                const angle = isGridView ? (-Math.PI / 2) : Math.PI;
                
                const bubbleX = centerX + (Math.cos(angle) * radius) - (newWidth / 2);
                const bubbleY = centerY + (Math.sin(angle) * radius) - (40 / 2);
                
                stockBubble.style.left = bubbleX + 'px';
                stockBubble.style.top = bubbleY + 'px';
            };
            
            // Limitar a 3 dígitos
            input.addEventListener('input', function(e) {
                // Eliminar cualquier carácter no numérico
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limitar a 3 dígitos (máximo 999)
                if (this.value.length > 3) {
                    this.value = this.value.slice(0, 3);
                }
                
                // Validar que no exceda 999
                if (parseInt(this.value) > 999) {
                    this.value = '999';
                }
                
                // Ajustar ancho de la burbuja
                adjustBubbleWidth();
            });
            
            // Ajustar ancho inicial
            adjustBubbleWidth();
        }
    }, 450);
    
    // Función para guardar
    function saveStock() {
        if (!stockBubble) {
            console.error('❌ stockBubble no existe');
            return;
        }
        
        const input = stockBubble.querySelector('#stockInput');
        if (!input) {
            console.error('❌ input no existe');
            return;
        }
        
        const newStock = parseInt(input.value);
        
        if (isNaN(newStock) || newStock < 0 || newStock > 999) {
            // Animación de error - shake sin afectar el scale
            const originalTransform = stockBubble.style.transform;
            stockBubble.style.animation = 'shake 0.5s ease-in-out';
            input.style.color = '#fee2e2';
            input.style.textShadow = '0 0 10px rgba(239, 68, 68, 0.8)';
            
            setTimeout(() => {
                if (stockBubble) {
                    stockBubble.style.animation = '';
                    stockBubble.style.transform = originalTransform;
                }
                if (input) {
                    input.style.color = '';
                    input.style.textShadow = '';
                }
            }, 500);
            return;
        }
        
        // Animación de salida
        stockBubble.style.transform = 'scale(0)';
        stockBubble.style.opacity = '0';
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        // Llamada AJAX para actualizar stock
        const formData = new FormData();
        formData.append('action', 'update_stock');
        formData.append('id', id);
        formData.append('stock', newStock);
        
        fetch(`${CONFIG.apiUrl}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar notificación de éxito
                if (typeof showNotification === 'function') {
                    // showNotification(`✅ Stock actualizado a ${newStock} unidades`, 'success');
                }
                
                // Usar actualización suave si está disponible
                if (window.smoothTableUpdater && data.product) {
                    console.log('🎯 Usando actualización suave para cambiar stock del producto:', id);
                    window.smoothTableUpdater.updateSingleProduct(data.product);
                } else {
                    console.log('⚠️ SmoothTableUpdater no disponible o producto no retornado - usando recarga tradicional');
                    // Actualizar lista inmediatamente
                    loadProducts(true);
                }
                
                // Cerrar burbuja y overlay
                setTimeout(() => {
                    if (overlay && overlay.parentNode) overlay.remove();
                    if (stockBubble && stockBubble.parentNode) stockBubble.remove();
                }, 400);
            } else {
                if (typeof showNotification === 'function') {
                    // showNotification('❌ Error al actualizar stock', 'error');
                }
                if (overlay && overlay.parentNode) overlay.remove();
                if (stockBubble && stockBubble.parentNode) stockBubble.remove();
            }
        })
        .catch(error => {
            if (typeof showNotification === 'function') {
                // showNotification('❌ Error de conexión', 'error');
            }
            if (overlay && overlay.parentNode) overlay.remove();
            if (stockBubble && stockBubble.parentNode) stockBubble.remove();
        });
    }
    
    // Variable para guardar el handler del click outside
    let clickOutsideHandler = null;
    
    // Función para cerrar sin guardar
    function closeBubble() {
        if (!stockBubble) return;
        
        // Limpiar listeners
        if (stockBubble.updatePositionListener) {
            window.removeEventListener('scroll', stockBubble.updatePositionListener, true);
            window.removeEventListener('resize', stockBubble.updatePositionListener);
        }
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        stockBubble.style.transform = 'scale(0)';
        stockBubble.style.opacity = '0';
        setTimeout(() => {
            if (overlay && overlay.parentNode) overlay.remove();
            if (stockBubble && stockBubble.parentNode) stockBubble.remove();
        }, 400);
    }
    
    // Eventos del input
    const input = stockBubble.querySelector('#stockInput');
    
    if (!input) {
        console.error('❌ No se encontró el input de stock');
        return;
    }
    
    // Guardar con Enter
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveStock();
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            closeBubble();
        }
    });
    
    // Cerrar al hacer click en el overlay (fuera de la burbuja)
    overlay.addEventListener('click', function(e) {
        // Solo si el click es directamente en el overlay, no en sus hijos
        if (e.target === overlay) {
            saveStock(); // Guardar al hacer click fuera
        }
    });
    
    // MANTENER pointer-events: none en overlay para permitir scroll
    // El click se detectará solo cuando hagamos click en el área del overlay
    
    // Prevenir que clicks en la burbuja cierren el overlay
    stockBubble.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Cerrar al hacer click fuera (usando evento en document)
    clickOutsideHandler = function(e) {
        // Si el click no es en la burbuja ni en el overlay, guardar
        if (!stockBubble.contains(e.target) && e.target !== stockBubble) {
            saveStock();
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
    };
    
    // Agregar listener después de un pequeño delay para evitar que se cierre inmediatamente
    setTimeout(() => {
        document.addEventListener('click', clickOutsideHandler);
    }, 100);
}

// Función para toggle status
async function toggleProductStatus(id, currentStatus) {
    
    const newStatus = !currentStatus;
    const action = newStatus ? 'activar' : 'desactivar';
    
    if (!confirm(`¿Estás seguro de ${action} este producto?`)) return;
    
    try {
        const response = await fetch(`${CONFIG.apiUrl}?action=toggle_status&id=${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // showNotification(`Producto ${action} exitosamente`, 'success');
            loadProducts(); // Recargar lista
        } else {
            throw new Error(result.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para cambiar estado del producto (activo/inactivo)
async function changeProductEstado(id) {
    try {
        // Obtener estado actual del producto
        const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            console.error('Error al obtener datos del producto');
            return;
        }
        
        const currentEstado = result.product.estado;
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        console.log(`Cambiando estado de ${currentEstado} a ${newEstado} para producto ${id}`);
        
        // Cambiar estado directamente sin confirmación
        const updateResponse = await fetch(`${CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}&estado=${newEstado}`
        });
        
        const updateResult = await updateResponse.json();
        
        if (updateResponse.ok && updateResult.success) {
            console.log('Estado cambiado exitosamente');
            
            // Usar actualización suave si está disponible
            if (window.smoothTableUpdater && updateResult.product) {
                console.log('🎯 Usando actualización suave para cambiar estado del producto:', id);
                window.smoothTableUpdater.updateSingleProduct(updateResult.product);
            } else {
                console.log('⚠️ SmoothTableUpdater no disponible o producto no retornado - usando recarga tradicional');
                // Recargar lista sin notificaciones
                loadProducts();
            }
        } else {
            console.error('Error al cambiar estado:', updateResult.error);
        }
        
    } catch (error) {
        console.error('Error en changeProductEstado:', error.message);
    }
}


// ============ FUNCIONES DE PAGINACIÓN ============

function goToFirstPageProducts() {
    if (currentPage > 1) {
        currentPage = 1;
        loadProducts();
    }
}

function previousPageProducts() {
    if (currentPage > 1) {
        currentPage--;
        loadProducts();
    }
}

function nextPageProducts() {
    if (currentPage < totalPages) {
        currentPage++;
        loadProducts();
    }
}

function goToLastPageProducts() {
    if (currentPage < totalPages) {
        currentPage = totalPages;
        loadProducts();
    }
}

// ============ FUNCIONES AUXILIARES ============

// Función para obtener productos seleccionados
function getSelectedProducts() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Función para limpiar selección de productos
function clearProductSelection() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    const selectAll = document.querySelector('input[type="checkbox"][onchange*="toggleSelectAllProducts"]');
    if (selectAll) selectAll.checked = false;
    
    updateBulkActionButtons();
}

// Función para actualizar botones de acciones en lote
function updateBulkActionButtons() {
    const selected = getSelectedProducts();
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        if (selected.length > 0) {
            bulkActions.style.display = 'flex';
            bulkActions.querySelector('.selected-count').textContent = selected.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// ============ NOTIFICACIONES DESACTIVADAS ============
// Todas las funciones de notificación han sido desactivadas por solicitud del usuario

// ============ INICIALIZACIÓN ============

// Función para actualizar contador de resultados
function updateResultsCounter(showing, total) {
    const showingStartEl = document.getElementById('showing-start-products');
    const showingEndEl = document.getElementById('showing-end-products');
    const totalProductsEl = document.getElementById('total-products');
    
    if (showingStartEl) showingStartEl.textContent = showing > 0 ? 1 : 0;
    if (showingEndEl) showingEndEl.textContent = showing;
    if (totalProductsEl) totalProductsEl.textContent = total;
}

// Función de inicialización principal
function initializeProductsModule() {
    
    // Asegurar que CONFIG esté inicializado
    if (typeof CONFIG === 'undefined' || !CONFIG.apiUrl) {
        initializeConfig();
    }

    
    // Verificar que los elementos necesarios existen
    const tbody = document.getElementById('productos-table-body');
    
    // Detectar si es móvil y preparar vista grid ANTES de cargar
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        console.log('📱 Dispositivo móvil detectado, preparando vista grid');
        
        // 1. Ocultar tabla INMEDIATAMENTE (antes que nada)
        const tableContainer = document.querySelector('.data-table-wrapper');
        if (tableContainer) {
            tableContainer.style.display = 'none !important';
            tableContainer.style.visibility = 'hidden';
        }
        
        // 2. Crear y mostrar grid container ANTES de cargar datos
        let gridContainer = document.querySelector('.products-grid');
        if (!gridContainer) {
            createGridView();
            gridContainer = document.querySelector('.products-grid');
        }
        
        // 3. Configurar grid para que esté visible desde el inicio
        if (gridContainer) {
            gridContainer.style.display = 'grid';
            gridContainer.style.visibility = 'visible';
            gridContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; color: #94a3b8;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div class="spinner" style="border: 3px solid #e2e8f0; border-top-color: #667eea; width: 40px; height: 40px;"></div>
                        <span style="font-size: 14px;">Cargando productos...</span>
                    </div>
                </div>
            `;
        }
        
        // 4. Cambiar botones activos
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'grid') {
                btn.classList.add('active');
            }
        });
    }
    
    // Cargar categorías y productos
    loadCategories();
    loadProducts();
    
    // Inicializar funciones de UI que antes estaban en DOMContentLoaded/load
    if (typeof initializeTableScroll === 'function') {
        initializeTableScroll();
    }
    if (typeof initializeDragScroll === 'function') {
        initializeDragScroll();
    }
    if (typeof setupDeleteModalBackdropClose === 'function') {
        setupDeleteModalBackdropClose();
    }
    
    // Función de debugging para verificar funciones disponibles
    window.debugProductsFunctions = function() {
        const functions = [
            'loadProducts', 'loadCategories', 'filterProducts', 'handleSearchInput', 
            'toggleView', 'showActionMenu', 'editProduct', 'viewProduct', 'deleteProduct',
            'toggleProductStatus', 'updateStock', 'exportProducts'
        ];
        
        const parentFunctions = ['showEditProductModal', 'showViewProductModal', 'showCreateProductModal'];
        parentFunctions.forEach(func => {

        });
    };
}

// ✅ EXPONER LA FUNCIÓN DE INICIALIZACIÓN GLOBALMENTE
window.initializeProductsModule = initializeProductsModule;

// ✅ EJECUTAR INICIALIZACIÓN INMEDIATAMENTE (dentro del eval())
// Esto asegura que se ejecute en el momento correcto, cuando el DOM ya está listo
initializeProductsModule();

// NOTA: Al ejecutar dentro del eval(), la función se ejecuta en el momento exacto
// cuando todo el código está definido y el contenedor ya tiene el HTML insertado

// Asegurar que las funciones estén disponibles globalmente de inmediato
window.loadProducts = loadProducts;
window.loadProductos = loadProducts;
window.loadCategories = loadCategories;
window.filterProducts = filterProducts;
window.handleSearchInput = handleSearchInput;
window.toggleView = toggleView;
window.showActionMenu = showActionMenu;
window.clearProductSearch = clearProductSearch;
window.clearAllProductFilters = clearAllProductFilters;
window.exportProducts = exportProducts;
window.showStockReport = showStockReport;
window.editProduct = editProduct;
window.viewProduct = viewProduct;
window.deleteProduct = deleteProduct;
window.toggleProductStatus = toggleProductStatus;
window.changeProductEstado = changeProductEstado;
window.updateStock = updateStock;
window.closeStockBubble = closeStockBubble; // Exponer función para cerrar burbuja
window.showDeleteConfirmation = showDeleteConfirmation;
window.closeDeleteConfirmation = closeDeleteConfirmation;
window.setupDeleteModalBackdropClose = setupDeleteModalBackdropClose;
window.confirmDelete = confirmDelete;
window.handleBulkProductAction = handleBulkProductAction;
window.createGridView = createGridView;
window.displayProductsGrid = displayProductsGrid;
window.closeFloatingActions = closeFloatingActions;
window.closeFloatingActionsAnimated = closeFloatingActionsAnimated;
window.createAnimatedFloatingContainer = createAnimatedFloatingContainer;
window.updateAnimatedButtonPositions = updateAnimatedButtonPositions;
window.forceCloseFloatingActions = forceCloseFloatingActions;
window.showImageFullSize = showImageFullSize;

// ============ FUNCIONES DE ESTADO PARA PRESERVACIÓN ============

// Función para obtener la vista actual
window.getCurrentView = function() {
    const gridViewBtn = document.querySelector('[onclick="toggleView(\'grid\')"]');
    const tableViewBtn = document.querySelector('[onclick="toggleView(\'table\')"]');
    
    if (gridViewBtn && gridViewBtn.classList.contains('active')) {
        return 'grid';
    } else if (tableViewBtn && tableViewBtn.classList.contains('active')) {
        return 'table';
    }
    
    // Verificar por el contenido visible
    const gridContainer = document.querySelector('.products-grid');
    const tableContainer = document.querySelector('.products-table');
    
    if (gridContainer && gridContainer.style.display !== 'none') {
        return 'grid';
    } else if (tableContainer && tableContainer.style.display !== 'none') {
        return 'table';
    }
    
    return 'table'; // Default
};

// Función para obtener el término de búsqueda actual
window.getSearchTerm = function() {
    const searchInput = document.getElementById('search-productos');
    return searchInput ? searchInput.value.trim() : '';
};

// Función para obtener los filtros actuales
window.getCurrentFilters = function() {
    const filters = {};
    
    if (typeof $ !== 'undefined') {
        const category = $('#filter-category').val();
        const status = $('#filter-status').val();
        
        if (category) filters.category = category;
        if (status !== '') filters.status = status;
    }
    
    return filters;
};

// Función para preservar scroll position
window.preserveScrollPosition = function() {
    const mainContent = document.querySelector('.tab-content') || document.body;
    return {
        top: mainContent.scrollTop,
        left: mainContent.scrollLeft
    };
};

// Función para restaurar scroll position
window.restoreScrollPosition = function(position) {
    if (!position) return;
    
    const mainContent = document.querySelector('.tab-content') || document.body;
    setTimeout(() => {
        mainContent.scrollTo({
            top: position.top,
            left: position.left,
            behavior: 'auto'
        });
    }, 100);
};

// Hacer currentPage accesible globalmente para preservación de estado
window.currentPage = currentPage;

// ============ FUNCIONES DE DESTACADO Y ANIMACIONES ============

// Función de destacado eliminada para evitar problemas visuales


// Sistema de loading overlay y actualización forzada eliminados

// ============ FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Función para mostrar burbuja de confirmación de eliminación
function showDeleteConfirmation(productId, productName) {
    console.log('🗑️ showDeleteConfirmation llamada:', productId, productName);
    
    // Verificar si ya existe un modal
    const existingOverlay = document.querySelector('.delete-confirmation-overlay');
    if (existingOverlay) {
        console.log('❌ Modal ya existe, eliminándolo primero');
        existingOverlay.remove();
    }
    
    // Crear overlay con estilos profesionales
    const overlay = document.createElement('div');
    overlay.className = 'delete-confirmation-overlay';
    console.log('✅ Overlay creado');
    
    overlay.innerHTML = `
        <div class="delete-confirmation-modal">
            <div class="delete-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            </div>
            <div class="delete-modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>Para eliminar el producto <strong>"${productName}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
                <input type="text" id="deleteConfirmInput" class="confirmation-input" placeholder="Escribe 'eliminar' para confirmar" autocomplete="off">
                <div id="deleteError" class="delete-error">
                    Por favor escribe exactamente "eliminar" para confirmar
                </div>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-cancel-delete" onclick="closeDeleteConfirmation()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirm-delete" onclick="confirmDelete(${productId}, '${productName.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i> Eliminar Producto
                </button>
            </div>
        </div>
    `;
    
    // Agregar estilos profesionales para el modal de delete
    overlay.style.cssText = `
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.5) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 1000002 !important;
        opacity: 1 !important;
        visibility: visible !important;
    `;
    
    const modal = overlay.querySelector('.delete-confirmation-modal');
    modal.style.cssText = `
        border-radius: 12px;
        padding: 0;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        animation: modalSlideIn 0.3s ease-out;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;
    
    // Estilos para elementos internos
    const style = document.createElement('style');
    style.textContent = `
        @keyframes modalSlideIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .delete-modal-header {
            background: #1e293b;
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .delete-confirmation-modal h3 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .delete-modal-body {
            padding: 2rem;
            text-align: center;
        }
        .warning-icon {
            font-size: 3rem;
            color: #dc2626;
            margin-bottom: 1rem;
            display: block;
            background: rgba(220, 38, 38, 0.1);
            padding: 1rem;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(220, 38, 38, 0.2);
        }
        .delete-confirmation-modal p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
        }
        .confirmation-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            text-align: center;
            font-weight: 500;
            background-color: #ffffff;
            transition: all 0.3s ease;
            color: #151c32ff;
            box-sizing: border-box;
        }
        .confirmation-input:focus {
            outline: none;
            border-color: #1e293b;
            box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.15);
        }
        .delete-error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            font-weight: 500;
            display: none;
            padding: 0.75rem;
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 6px;
        }
        .delete-modal-footer {
            padding: 1.5rem 2rem;
            background: #1e293b;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }
        .btn-confirm-delete {
            background: #dc2626;
            color: white;
            border: 2px solid #dc2626;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }
        .btn-confirm-delete:hover {
            background: #b91c1c;
            border-color: #b91c1c;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
        }
        .btn-cancel-delete {
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }
        .btn-cancel-delete:hover {
            background: #1e293b;
            border-color: #1e293b;
            color: white;
            box-shadow: 0 4px 12px rgba(30, 41, 59, 0.2);
        }
    `;
    document.head.appendChild(style);
    
    console.log('📝 Estilos agregados');
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    console.log('🎯 Modal agregado al DOM');
    
    // Forzar reflow para que las animaciones funcionen
    overlay.offsetHeight;
    
    // Agregar clase 'show' para activar animaciones CSS
    requestAnimationFrame(() => {
        overlay.classList.add('show');
        
        // También agregar .show al modal interno
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('show');
        }
        
        console.log('✨ Clase "show" agregada - animación iniciada');
    });
    
    // Focus en el input después de la animación
    setTimeout(() => {
        const input = document.getElementById('deleteConfirmInput');
        if (input) {
            input.focus();
            console.log('⌨️ Focus en input');
        }
    }, 350);
    
    // Permitir confirmar con Enter
    const input = document.getElementById('deleteConfirmInput');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmDelete(productId, productName);
            }
        });
    }
    
    // Permitir cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeDeleteConfirmation();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Cerrar al hacer click en el overlay (fondo oscuro)
    overlay.addEventListener('click', function(e) {
        // Solo cerrar si se hace click directamente en el overlay, no en el modal
        if (e.target === overlay) {
            console.log('👆 Click en overlay detectado - cerrando modal');
            closeDeleteConfirmation();
        }
    });
    
    // Prevenir que clicks dentro del modal cierren el overlay
    const deleteModal = overlay.querySelector('.delete-confirmation-modal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// Función para cerrar la confirmación con animación
function closeDeleteConfirmation() {
    console.log('🔴 Cerrando modal de eliminación con animación');
    const overlay = document.querySelector('.delete-confirmation-overlay');
    if (overlay) {
        // Agregar clases de salida para animación
        overlay.classList.remove('show');
        overlay.classList.add('hide');
        
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('hide');
        }
        
        // Remover del DOM después de que termine la animación
        setTimeout(() => {
            overlay.remove();
            console.log('✅ Modal eliminado del DOM');
        }, 250); // Duración de la animación fadeOut actualizada
    }
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropClose() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmation();
        }
    });
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropClose() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmation();
        }
    });
}

// Función para confirmar eliminación
function confirmDelete(productId, productName) {
    const input = document.getElementById('deleteConfirmInput');
    const errorDiv = document.getElementById('deleteError');
    
    if (input.value.toLowerCase().trim() !== 'eliminar') {
        errorDiv.style.display = 'block';
        input.style.borderColor = '#dc2626';
        input.focus();
        return;
    }
    
    // Proceder con eliminación
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', productId);
    
    fetch(`${CONFIG.apiUrl}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteConfirmation();
        
        if (data.success) {
            // Mostrar notificación de éxito
            if (typeof showNotification === 'function') {
                // showNotification(`Producto "${productName}" eliminado exitosamente`, 'success');
            }
            
            // Usar actualización suave si está disponible
            if (window.smoothTableUpdater) {
                console.log('🎯 Usando actualización suave para eliminar producto:', productId);
                window.smoothTableUpdater.removeProduct(productId);
            } else {
                console.log('⚠️ SmoothTableUpdater no disponible - usando recarga tradicional');
                // Actualizar lista inmediatamente sin reload
                loadProducts(true);
            }
        } else {
            if (typeof showNotification === 'function') {
                // showNotification('Error al eliminar producto: ' + (data.error || 'Error desconocido'), 'error');
            } else {
                // alert('Error al eliminar producto: ' + (data.error || 'Error desconocido'));
            }
        }
    })
    .catch(error => {
        closeDeleteConfirmation();
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexión al eliminar producto', 'error');
        } else {
            // alert('Error de conexión al eliminar producto');
        }
    });
}

// Función para alternar estado del producto (activo/inactivo)
function toggleProductStatus(productId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', productId);
    formData.append('status', newStatus);
    
    fetch(`${CONFIG.apiUrl}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Usar actualización suave si está disponible
            if (window.smoothTableUpdater && data.product) {
                console.log('🎯 Usando actualización suave para cambiar estado del producto:', productId);
                window.smoothTableUpdater.updateSingleProduct(data.product);
            } else {
                console.log('⚠️ SmoothTableUpdater no disponible o producto no retornado - usando recarga tradicional');
                // Actualizar lista inmediatamente sin reload
                loadProducts(true);
            }
        } else {
            if (typeof showNotification === 'function') {
                // showNotification('Error al cambiar estado: ' + (data.error || 'Error desconocido'), 'error');
            } else {
                // alert('Error al cambiar estado: ' + (data.error || 'Error desconocido'));
            }
        }
    })
    .catch(error => {
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexión al cambiar estado', 'error');
        } else {
            // alert('Error de conexión al cambiar estado');
        }
    });
}

// Función wrapper para eliminar producto
function deleteProduct(productId, productName) {
    console.log('🚀 deleteProduct wrapper llamada:', productId, productName);
    showDeleteConfirmation(productId, productName || 'Producto');
}

// ============ FUNCIÓN PARA MOSTRAR IMAGEN EN TAMAÑO REAL ============

function showImageFullSize(imageUrl, productName) {
    console.log('🖼️ Mostrando imagen en tamaño real:', imageUrl);
    
    // Crear overlay con fondo transparente
    const overlay = document.createElement('div');
    overlay.className = 'image-fullsize-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.15);
        z-index: 1000005;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(3px);
        cursor: zoom-out;
    `;
    
    // Crear imagen directamente sin contenedor
    const img = document.createElement('img');
    img.src = imageUrl;
    img.alt = productName || 'Producto';
    img.style.cssText = `
        max-width: 95vw;
        max-height: 95vh;
        object-fit: contain;
        cursor: zoom-out;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    // Función para cerrar con animación
    const closeModal = () => {
        overlay.style.opacity = '0';
        img.style.opacity = '0';
        
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    };
    
    // Cerrar al hacer click en cualquier parte
    overlay.addEventListener('click', closeModal);
    
    // Cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Ensamblar elementos
    overlay.appendChild(img);
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    
    // Animar entrada
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        
        // Esperar un frame más para que la imagen cargue
        setTimeout(() => {
            img.style.opacity = '1';
        }, 50);
    });
    
    // Manejar error de carga de imagen
    img.onerror = () => {
        img.src = AppConfig ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
    };
}

// Hacer la función global
window.showImageFullSize = showImageFullSize;

// ============ FIN FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Sistema de limpieza automática para evitar menús huérfanos
setInterval(() => {
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    if (orphanedContainers.length > 1) {
        // Si hay más de un contenedor, algo está mal, limpiar todos
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error limpiando contenedor huérfano:', e);
            }
        });
        // Resetear variables globales
        productos_activeFloatingContainer = null;
        productos_centerButton = null;
        productos_floatingButtons = [];
        productos_activeProductId = null;
        productos_isAnimating = false;
    }
}, 2000); // Verificar cada 2 segundos

// Limpiar al cambiar de página o recargar
window.addEventListener('beforeunload', () => {
    forceCloseFloatingActions();
});

// ===== FUNCIONALIDAD DE SCROLL MEJORADO PARA LA TABLA =====
function initializeTableScroll() {
    const scrollableTable = document.querySelector('.scrollable-table');
    if (!scrollableTable) return;
    
    let scrollTimeout;
    
    // Detectar cuando se está haciendo scroll
    scrollableTable.addEventListener('scroll', function() {
        // Agregar clase durante el scroll
        this.classList.add('scrolling');
        
        // Remover clase después de que termine el scroll
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            this.classList.remove('scrolling');
        }, 150);
        
        // Detectar si tiene contenido que requiere scroll
        if (this.scrollHeight > this.clientHeight || this.scrollWidth > this.clientWidth) {
            this.classList.add('has-scroll');
        } else {
            this.classList.remove('has-scroll');
        }
    });
    
    // Verificar inicialmente si necesita scroll
    const checkScroll = () => {
        if (scrollableTable.scrollHeight > scrollableTable.clientHeight || 
            scrollableTable.scrollWidth > scrollableTable.clientWidth) {
            scrollableTable.classList.add('has-scroll');
        } else {
            scrollableTable.classList.remove('has-scroll');
        }
    };
    
    // Verificar cuando cambie el contenido
    const observer = new MutationObserver(checkScroll);
    observer.observe(scrollableTable, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['style']
    });
    
    // Verificar al redimensionar
    window.addEventListener('resize', checkScroll);
    
    // Verificar inicialmente
    setTimeout(checkScroll, 100);
}

// Inicializar el scroll mejorado cuando se carga la tabla
// ❌ DESACTIVADO: No usar DOMContentLoaded porque se ejecuta en eval() cada vez que se carga el módulo
// document.addEventListener('DOMContentLoaded', initializeTableScroll);
// En su lugar, initializeProductsModule() ya llama a esto directamente

// También inicializar cuando se actualiza la tabla
const originalDisplayProducts = displayProducts;
if (typeof displayProducts === 'function') {
    displayProducts = function(...args) {
        const result = originalDisplayProducts.apply(this, args);
        setTimeout(initializeTableScroll, 100);
        return result;
    };
}

// ===== SISTEMA DE DRAG-SCROLL PARA LA TABLA (COMO TOUCH) =====
function initializeDragScroll() {
    const scrollableTable = document.querySelector('.scrollable-table');
    if (!scrollableTable) return;
    
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let scrollLeft = 0;
    let scrollTop = 0;
    let velocityX = 0;
    let velocityY = 0;
    let lastX = 0;
    let lastY = 0;
    let lastTime = 0;
    
    // Iniciar drag
    scrollableTable.addEventListener('mousedown', function(e) {
        // No aplicar drag si se está clickeando en un botón, input o link
        if (e.target.closest('button, a, input, select, textarea, .btn-menu, .product-card-btn')) {
            return;
        }
        
        isDragging = true;
        startX = e.pageX - scrollableTable.offsetLeft;
        startY = e.pageY - scrollableTable.offsetTop;
        scrollLeft = scrollableTable.scrollLeft;
        scrollTop = scrollableTable.scrollTop;
        lastX = e.pageX;
        lastY = e.pageY;
        lastTime = Date.now();
        velocityX = 0;
        velocityY = 0;
        
        scrollableTable.classList.add('dragging');
        
        // Prevenir selección de texto completamente
        e.preventDefault();
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        document.body.style.mozUserSelect = 'none';
        document.body.style.msUserSelect = 'none';
    });
    
    // Mover durante drag
    scrollableTable.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        
        const currentTime = Date.now();
        const deltaTime = currentTime - lastTime;
        
        const x = e.pageX - scrollableTable.offsetLeft;
        const y = e.pageY - scrollableTable.offsetTop;
        
        const walkX = (x - startX);
        const walkY = (y - startY);
        
        // Calcular velocidad para momentum scrolling
        if (deltaTime > 0) {
            velocityX = (e.pageX - lastX) / deltaTime;
            velocityY = (e.pageY - lastY) / deltaTime;
        }
        
        scrollableTable.scrollLeft = scrollLeft - walkX;
        scrollableTable.scrollTop = scrollTop - walkY;
        
        lastX = e.pageX;
        lastY = e.pageY;
        lastTime = currentTime;
    });
    
    // Finalizar drag
    const endDrag = function(e) {
        if (!isDragging) return;
        
        isDragging = false;
        scrollableTable.classList.remove('dragging');
        
        // Restaurar selección de texto
        document.body.style.userSelect = '';
        document.body.style.webkitUserSelect = '';
        document.body.style.mozUserSelect = '';
        document.body.style.msUserSelect = '';
        
        // Aplicar momentum scrolling (inercia)
        const friction = 0.95;
        const minVelocity = 0.1;
        
        function momentum() {
            if (Math.abs(velocityX) < minVelocity && Math.abs(velocityY) < minVelocity) {
                return;
            }
            
            scrollableTable.scrollLeft -= velocityX * 10;
            scrollableTable.scrollTop -= velocityY * 10;
            
            velocityX *= friction;
            velocityY *= friction;
            
            requestAnimationFrame(momentum);
        }
        
        // Solo aplicar momentum si la velocidad es significativa
        if (Math.abs(velocityX) > minVelocity || Math.abs(velocityY) > minVelocity) {
            momentum();
        }
    };
    
    scrollableTable.addEventListener('mouseup', endDrag);
    scrollableTable.addEventListener('mouseleave', endDrag);
    
    // Prevenir click accidental después de drag
    scrollableTable.addEventListener('click', function(e) {
        if (Math.abs(velocityX) > 0.5 || Math.abs(velocityY) > 0.5) {
            e.stopPropagation();
            e.preventDefault();
        }
    }, true);
}

// Inicializar drag-scroll cuando carga el DOM
// ❌ DESACTIVADO: No usar DOMContentLoaded/load porque se acumulan event listeners
// document.addEventListener('DOMContentLoaded', function() {
//     initializeDragScroll();
// });

// window.addEventListener('load', function() {
//     initializeDragScroll();
// });
// En su lugar, initializeProductsModule() llama a initializeDragScroll() directamente

// ===== FUNCIÓN DE DESTRUCCIÓN DEL MÓDULO DE PRODUCTOS =====
window.destroyProductosModule = function() {
    console.log('🗑️ Destruyendo módulo de productos...');
    
    try {
        // 1. Limpiar variable de estado de carga
        if (typeof isLoading !== 'undefined') {
            isLoading = false;
        }
        
        // 2. Limpiar arrays de datos
        if (typeof productos !== 'undefined') {
            productos = [];
        }
        
        // 3. Resetear paginación
        if (typeof currentPage !== 'undefined') {
            currentPage = 1;
        }
        if (typeof totalPages !== 'undefined') {
            totalPages = 1;
        }
        
        // 4. Limpiar event listeners clonando elementos
        const searchInput = document.getElementById('search-productos');
        if (searchInput && searchInput.parentNode) {
            const newSearch = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearch, searchInput);
        }
        
        const filterCategory = document.getElementById('filter-category');
        if (filterCategory && filterCategory.parentNode) {
            const newFilter = filterCategory.cloneNode(true);
            filterCategory.parentNode.replaceChild(newFilter, filterCategory);
        }
        
        const filterStatus = document.getElementById('filter-status');
        if (filterStatus && filterStatus.parentNode) {
            const newFilterStatus = filterStatus.cloneNode(true);
            filterStatus.parentNode.replaceChild(newFilterStatus, filterStatus);
        }
        
        const filterStock = document.getElementById('filter-stock');
        if (filterStock && filterStock.parentNode) {
            const newFilterStock = filterStock.cloneNode(true);
            filterStock.parentNode.replaceChild(newFilterStock, filterStock);
        }
        
        // 5. Limpiar modales de productos
        const productModals = document.querySelectorAll('.product-view-modal, .product-modal, [id*="product-modal"]');
        productModals.forEach(modal => {
            modal.remove();
        });
        
        // 6. Limpiar burbujas flotantes de stock Y contenedores flotantes de productos SOLAMENTE
        const stockBubbles = document.querySelectorAll('.stock-update-bubble');
        stockBubbles.forEach(bubble => {
            bubble.remove();
        });
        
        // Limpiar SOLO los contenedores flotantes que pertenecen a productos
        if (productos_activeFloatingContainer && document.contains(productos_activeFloatingContainer)) {
            productos_activeFloatingContainer.remove();
        }
        
        // Resetear variables flotantes de productos
        productos_activeFloatingContainer = null;
        productos_centerButton = null;
        productos_floatingButtons = [];
        productos_activeProductId = null;
        productos_isAnimating = false;
        
        // 7. Limpiar confirmaciones de eliminación
        const deleteConfirmations = document.querySelectorAll('.delete-confirmation-overlay');
        deleteConfirmations.forEach(confirmation => {
            confirmation.remove();
        });
        
        // 8. Limpiar el tbody de la tabla
        const tbody = document.getElementById('productos-table-body');
        if (tbody) {
            tbody.innerHTML = '';
        }
        
        // 9. RESETEAR VISTA A TABLA (estado inicial)
        console.log('🔄 Reseteando vista a tabla (estado inicial)...');
        
        // Remover vista grid si existe
        const gridContainer = document.querySelector('.products-grid');
        if (gridContainer) {
            gridContainer.remove();
        }
        
        // Asegurar que la tabla esté visible
        const tableContainer = document.querySelector('.data-table-wrapper');
        if (tableContainer) {
            tableContainer.style.display = 'block';
        }
        
        // Resetear botones de vista
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'table') {
                btn.classList.add('active');
            }
        });
        
        // Limpiar localStorage de vista
        try {
            localStorage.removeItem('productos_view_mode');
        } catch (e) {}
        
        console.log('✅ Vista reseteada a tabla');
        
        // 10. Remover clases de body que puedan interferir
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('✅ Módulo de productos destruido correctamente');
        
    } catch (error) {
        console.error('❌ Error al destruir módulo de productos:', error);
    }
};

</script>

<style>
/* ===== PREVENIR DOBLE SCROLLBAR ===== */
.data-table-container:has(.scrollable-table) {
    overflow: visible !important;
}

.data-table-wrapper.scrollable-table {
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

/* ===== BARRA DE SCROLL PERSONALIZADA PARA LA TABLA ===== */
.scrollable-table {
    max-height: calc(100vh - 250px);
    min-height: 500px;
    overflow-y: auto;
    overflow-x: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    background: #1e293b;
    position: relative;
    cursor: default; /* Cursor normal ya que no hay drag horizontal */
}

.scrollable-table.dragging {
    cursor: default; /* Cursor normal */
    user-select: none; /* Evitar selección de texto */
}

/* Estilos personalizados para la barra de scroll - SIMPLIFICADO */
.scrollable-table::-webkit-scrollbar {
    width: 10px;
    height: 0; /* Sin scrollbar horizontal */
}

.scrollable-table::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 5px;
}

.scrollable-table::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #475569 0%, #334155 100%);
    border-radius: 5px;
    transition: all 0.3s ease;
}

.scrollable-table::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #64748b 0%, #475569 100%);
}

.scrollable-table::-webkit-scrollbar-thumb:active {
    background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
}

/* Esquina del scroll */
.scrollable-table::-webkit-scrollbar-corner {
    background: #0f172a;
}

/* Ocultar botones de flechas */
.scrollable-table::-webkit-scrollbar-button {
    display: none;
    width: 0;
    height: 0;
}

/* Mejorar la tabla dentro del contenedor con scroll */
.scrollable-table .data-table {
    margin: 0;
    border-radius: 0;
    box-shadow: none;
    border-collapse: separate;
    border-spacing: 0;
}

.scrollable-table .table-header th:first-child {
    border-top-left-radius: 8px;
}

.scrollable-table .table-header th:last-child {
    border-top-right-radius: 8px;
}

/* Sticky header dentro del scroll */
.scrollable-table .table-header {
    position: sticky;
    top: 0;
    z-index: 20;
    background: #1e293b;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Animación suave del scroll */
.scrollable-table {
    scroll-behavior: smooth;
}

/* ===== FORZAR PRIMER PLANO PARA ELEMENTOS FLOTANTES ===== */
.animated-floating-container,
.animated-floating-button,
.stock-update-bubble {
    z-index: 999999 !important;
    position: relative !important;
}

.delete-confirmation-overlay {
    z-index: 1000002 !important;
    position: fixed !important;
}

.animated-floating-container * {
    z-index: 999999 !important;
}

/* Asegurar que los tooltips también estén en primer plano */
.floating-tooltip {
    z-index: 1000000 !important;
}

/* Forzar primer plano en elementos específicos que pueden interferir */
.modal-content,
.modal-overlay,
#product-modal-overlay {
    z-index: 99999 !important;
}

/* Asegurar que las burbujas estén por encima de modales */
.animated-floating-container,
.stock-update-bubble {
    z-index: 1000001 !important;
}
</style>

